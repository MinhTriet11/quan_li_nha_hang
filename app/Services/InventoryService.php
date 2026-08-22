<?php

namespace App\Services;

use App\Models\BranchIngredient;
use App\Models\ExportDetail;
use App\Models\ExportReceipts;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


/**
 * Xử lý nghiệp vụ xuất kho:
 *  - Tự động xuất kho khi món bắt đầu chế biến (OrderDetail.status 0 -> 1)
 *  theo công thức Recipe × số lượng OrderDetail.
 *  - Tạo phiếu xuất kho thủ công (hư / đổ / điều chỉnh) ở trạng thái chờ duyệt.
 *  - Duyệt phiếu thủ công (cập nhật trạng thái + trừ tồn kho).
 */
class InventoryService
{
    
    public function exportForOrderDetail($orderDetails, $order): ?ExportReceipts
    {
        if (!$order) {
            return null;
        }

        $details = collect($orderDetails)
            ->filter(fn ($d) => (int) $d->status === 1)
            ->values();

        if ($details->isEmpty()) {
            return null;
        }

        // Chống xuất trùng ở cấp đơn: nếu đơn này đã có phiếu sale thì bỏ qua.
        $alreadyExported = ExportReceipts::where('reason', 'sale')
            ->where('source_ordetail', $order->id_order)
            ->exists();
        if ($alreadyExported) {
            return null;
        }

        // Gom công thức × số lượng của tất cả món trong đơn.
        // Map: id_ingre => ['needQty' => float, 'foodIds' => array]
        $aggregated = [];
        foreach ($details as $detail) {
            $recipes = Recipe::where('id_food', $detail->id_food)->get();
            foreach ($recipes as $recipe) {
                $needQty = (float) $recipe->quantity * (int) $detail->quantity;
                if (!isset($aggregated[$recipe->id_ingre])) {
                    $aggregated[$recipe->id_ingre] = [
                        'needQty'  => 0.0,
                        'foodIds'  => [],
                    ];
                }
                $aggregated[$recipe->id_ingre]['needQty'] += $needQty;
                $aggregated[$recipe->id_ingre]['foodIds'][$detail->id_food] = true;
            }
        }

        if (empty($aggregated)) {
            return null;
        }

        return DB::transaction(function () use ($order, $details, $aggregated) {
            $receipt = ExportReceipts::create([
                'id_br'           => $order->id_br,
                'id_user'         => Auth::id() ?? $order->id_user,
                'note'            => 'Tự động xuất kho khi bếp bắt đầu chế biến đơn #' . $order->id_order
                                      . ' (món #' . $details->pluck('id_food')->unique()->implode(', #') . ')',
                'reason'          => 'sale',
                'source_ordetail' => $order->id_order, 
                'status'          => 1, 
            ]);

            foreach ($aggregated as $ingredientId => $row) {
                
                $foodId = array_key_first($row['foodIds']);

                ExportDetail::create([
                    'id_ex'    => $receipt->id_ex,
                    'id_food'  => $foodId,
                    'id_ingre' => $ingredientId,
                    'quantity' => $row['needQty'],
                ]);

                $this->decrementStock($order->id_br, (int) $ingredientId, $row['needQty']);
            }

            return $receipt;
        });
    }

    /**
     * Tạo phiếu xuất kho thủ công (lý do: waste / broken / adjustment).
     * Lưu ở trạng thái chờ duyệt (status = 0).
     */
    public function createManualExport(int $branchId, string $reason, ?string $note, array $details): ExportReceipts
    {
        return DB::transaction(function () use ($branchId, $reason, $note, $details) {
            $receipt = ExportReceipts::create([
                'id_br'   => $branchId,
                'id_user' => Auth::id(),
                'note'    => $note,
                'reason'  => $reason,
                'status'  => 0, // chờ duyệt
            ]);

            foreach ($details as $row) {
                ExportDetail::create([
                    'id_ex'    => $receipt->id_ex,
                    'id_food'  => null,
                    'id_ingre' => $row['id_ingre'],
                    'quantity' => $row['quantity'],
                ]);
            }

            return $receipt;
        });
    }

    /**
     * Admin duyệt phiếu xuất thủ công: trừ tồn kho, chuyển status = 1.
     * Có thể duyệt lại (idempotent cho cùng status=1).
     */
    public function approveExport(ExportReceipts $receipt): void
    {
        if ((int) $receipt->status === 1) {
            return; // đã duyệt rồi
        }
        if ((int) $receipt->status === 2) {
            throw new \RuntimeException('Phiếu xuất đã bị từ chối, không thể duyệt.');
        }

        DB::transaction(function () use ($receipt) {
            foreach ($receipt->details as $detail) {
                $this->decrementStock($receipt->id_br, $detail->id_ingre, (float) $detail->quantity);
            }
            $receipt->status = 1;
            $receipt->save();
        });
    }


    /**
     * Trừ tồn kho. Không tạo bản ghi âm — nếu chưa có tồn kho của nguyên liệu tại chi nhánh thì bỏ qua.
     */
    protected function decrementStock(int $branchId, int $ingredientId, float $qty): void
    {
        $stock = BranchIngredient::where('id_br', $branchId)
            ->where('id_ingre', $ingredientId)
            ->first();

        if ($stock) {
            $stock->quantity = (float) $stock->quantity - $qty;
            $stock->save();
        }
    }

    /**
     * Kiểm tra chi nhánh có đủ nguyên liệu để chế biến các OrderDetail không.
     * KHÔNG thay đổi tồn kho, chỉ đọc.
     * Trả về ['ok' => bool, 'shortages' => array of [id_ingre, name, unit, required, available, shortage]].
     */
    public function checkStockAvailability(int $branchId, $orderDetails): array
    {
        // Gom nhu cầu nguyên liệu theo id_ingre (cùng logic aggregation với exportForOrderDetail)
        $aggregated = [];
        foreach ($orderDetails as $detail) {
            $recipes = Recipe::where('id_food', $detail->id_food)->get();
            foreach ($recipes as $recipe) {
                $needQty = (float) $recipe->quantity * (int) $detail->quantity;
                $aggregated[$recipe->id_ingre] = ($aggregated[$recipe->id_ingre] ?? 0) + $needQty;
            }
        }

        // Món không có recipe => không cần check nguyên liệu
        if (empty($aggregated)) {
            return ['ok' => true, 'shortages' => []];
        }

        $ingredientIds = array_keys($aggregated);

        $stocks = BranchIngredient::where('id_br', $branchId)
            ->whereIn('id_ingre', $ingredientIds)
            ->get()
            ->keyBy('id_ingre');

        $ingredients = Ingredient::whereIn('id_ingre', $ingredientIds)
            ->get()
            ->keyBy('id_ingre');

        $shortages = [];
        foreach ($aggregated as $idIngre => $needQty) {
            $available = (float) ($stocks[$idIngre]->quantity ?? 0);
            if ($available < $needQty) {
                $shortages[] = [
                    'id_ingre'  => $idIngre,
                    'name'      => $ingredients[$idIngre]->name ?? "Nguyên liệu #{$idIngre}",
                    'unit'      => $ingredients[$idIngre]->unit ?? '',
                    'required'  => round($needQty, 3),
                    'available' => round($available, 3),
                    'shortage'  => round($needQty - $available, 3),
                ];
            }
        }

        return [
            'ok'        => empty($shortages),
            'shortages' => $shortages,
        ];
    }
}
