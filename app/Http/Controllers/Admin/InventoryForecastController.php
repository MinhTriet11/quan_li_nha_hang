<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchIngredient;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Module dự báo nhập kho.
 *
 * Trả lời 3 câu hỏi:
 *  1. Dự báo:  Trong khoảng [H+1 → H+forecastDays] sẽ bán được bao nhiêu món?
 *  2. Yêu cầu: Lượng nguyên liệu cần dùng để chế biến lượng món trên?
 *  3. Đề xuất: So với tồn kho hiện tại + min_stock, cần nhập thêm bao nhiêu?
 *
 * Thuật toán: trung bình N ngày gần nhất (mặc định 14 ngày).
 * Tính độc lập cho từng chi nhánh.
 */
class InventoryForecastController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();

        // Mặc định: chi nhánh đầu tiên (nếu có) để dữ liệu không trống.
        $selectedBranchId = $request->get('branch_id')
            ?: optional($branches->first())->id_br;

        // Khoảng tính trung bình (lịch sử) và số ngày dự báo tương lai.
        $historyDays  = (int) $request->get('history_days', 14);
        $historyDays  = max(1, min($historyDays, 90));
        $forecastDays = (int) $request->get('forecast_days', 7);
        $forecastDays = max(1, min($forecastDays, 30));

        $today      = Carbon::today();
        $historyEnd = $today->copy()->subDay();                 // hôm qua
        $historyStart = $today->copy()->subDays($historyDays);  // cách $historyDays ngày
        $forecastStart = $today->copy()->addDay();
        $forecastEnd   = $today->copy()->addDays($forecastDays);

        // 1. Lấy order theo chi nhánh trong khoảng lịch sử
        $orders = Order::with('orderDetails')
            ->where('id_br', $selectedBranchId)
            ->whereBetween('order_time', [
                $historyStart->toDateTimeString(),
                $historyEnd->endOfDay()->toDateTimeString(),
            ])
            ->where('status', 3)
            ->get();

        // 2. Tổng số lượng từng món trong lịch sử
        $foodSold = [];
        foreach ($orders as $order) {
            foreach ($order->orderDetails as $d) {
                $key = $d->id_food;
                $foodSold[$key] = ($foodSold[$key] ?? 0) + (int) $d->quantity;
            }
        }

        // 3. Dự báo nhu cầu từng món (làm tròn 2 chữ số)
        //    forecast = (tổng SL lịch sử / số ngày lịch sử) * số ngày dự báo
        $forecasts = [];
        foreach ($foodSold as $idFood => $totalQty) {
            $avgPerDay   = $totalQty / $historyDays;
            $forecastQty = round($avgPerDay * $forecastDays, 2);
            $forecasts[$idFood] = [
                'history_qty'  => $totalQty,
                'avg_per_day'  => round($avgPerDay, 3),
                'forecast_qty' => $forecastQty,
            ];
        }

        // 4. Tính yêu cầu nguyên liệu từ công thức (recipe)
        //    Map id_ingre => required qty
        $recipeRows = \App\Models\Recipe::with('ingredient')
            ->whereIn('id_food', array_keys($forecasts))
            ->get();

        $ingredientRequired = [];
        foreach ($recipeRows as $row) {
            $idIngre = $row->id_ingre;
            $need    = ((float) $row->quantity) * $forecasts[$row->id_food]['forecast_qty'];
            $ingredientRequired[$idIngre] = ($ingredientRequired[$idIngre] ?? 0) + $need;
        }

        // 5. Tồn kho hiện tại của từng nguyên liệu (theo chi nhánh)
        $stocks = BranchIngredient::with('ingredient')
            ->where('id_br', $selectedBranchId)
            ->get()
            ->keyBy('id_ingre');

        // 6. Đề xuất nhập kho
        $suggestions = [];
        $allIngredients = Ingredient::orderBy('name')->get();
        foreach ($allIngredients as $ingre) {
            $idIngre = $ingre->id_ingre;
            $required = $ingredientRequired[$idIngre] ?? 0.0;
            if ($required <= 0) {
                continue; // Không có nhu cầu → không đề xuất
            }

            $currentQty = (float) ($stocks[$idIngre]->quantity ?? 0);
            $minStock   = (float) ($ingre->min_stock ?? 0);
            $safetyStock = max($minStock, 0); // mức an toàn = min_stock

            // Cần có tổng = required + an toàn
            $targetStock  = $required + $safetyStock;
            $needImport   = max(0, $targetStock - $currentQty);

            // Cảnh báo nếu không đủ tồn kho cho nhu cầu
            $shortage = max(0, $required - $currentQty);

            $suggestions[] = [
                'id_ingre'     => $idIngre,
                'name'         => $ingre->name,
                'unit'         => $ingre->unit ?? '',
                'required'     => round($required, 2),
                'current_qty'  => round($currentQty, 2),
                'min_stock'    => round($minStock, 2),
                'safety_stock' => round($safetyStock, 2),
                'target_stock' => round($targetStock, 2),
                'suggest_qty'  => round($needImport, 2),
                'shortage'     => round($shortage, 2),
                'is_out'       => $currentQty <= 0,
                'is_low'       => $currentQty > 0 && $currentQty <= $minStock,
            ];
        }

        // Sắp xếp: thiếu nhiều nhất lên đầu
        usort($suggestions, fn ($a, $b) => $b['shortage'] <=> $a['shortage']);

        // Tên chi nhánh đang chọn (hiển thị)
        $selectedBranch = $branches->firstWhere('id_br', (int) $selectedBranchId);

        return view('admin.pages.inventory_forecast', compact(
            'branches',
            'selectedBranchId',
            'selectedBranch',
            'historyDays',
            'forecastDays',
            'historyStart',
            'historyEnd',
            'forecastStart',
            'forecastEnd',
            'today',
            'forecasts',
            'ingredientRequired',
            'suggestions'
        ));
    }
}
