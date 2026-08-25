<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchIngredient;
use App\Models\ExportDetail;
use App\Models\ExportReceipts;
use App\Models\Ingredient;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExportReceiptController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * Danh sách phiếu xuất kho (lọc theo chi nhánh, lý do, trạng thái, từ khóa).
     */
    public function index(Request $request)
    {
        $query = ExportReceipts::with(['branch', 'user', 'details.ingredient']);

        if ($request->filled('branch')) {
            $query->where('id_br', $request->branch);
        }
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }
        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $kw = $request->search;
            $search = preg_replace('/[^0-9]/', '', $kw);
            $query->where('id_ex', 'like', "%{$search}%");
        }

        $receipts = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $branches = Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.pages.export_receipts', compact('receipts', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.pages.export_receipt_create', compact('branches'));
    }

    public function getBranchIngredients($id_br)
    {
        $rows = BranchIngredient::with('ingredient')
            ->where('id_br', $id_br)
            ->orderBy('id_ingre')
            ->get()
            ->map(function ($r) {
                return [
                    'id_ingre' => $r->id_ingre,
                    'name'     => $r->ingredient->name ?? '',
                    'unit'     => $r->ingredient->unit ?? '',
                    'quantity' => (float) $r->quantity,
                ];
            })->values();

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
    }

    //Lưu phiếu xuất kho thủ công (chờ duyệt).
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_br' => 'required|exists:branches,id_br',
                'reason' => 'required|in:waste,broken,adjustment',
                'note'  => 'nullable|string|max:1000',
                'details' => 'required|array|min:1',
                'details.*.id_ingre' => 'required|exists:ingredients,id_ingre',
                'details.*.quantity' => 'required|numeric|min:0.01',
            ], [
                'id_br.required'   => 'Vui lòng chọn chi nhánh',
                'reason.required' => 'Vui lòng chọn lý do xuất',
                'reason.in' => 'Lý do xuất không hợp lệ',
                'details.required'  => 'Vui lòng thêm ít nhất 1 dòng nguyên liệu',
                'details.*.id_ingre.required' => 'Vui lòng chọn nguyên liệu',
                'details.*.id_ingre.exists' => 'Nguyên liệu không tồn tại',
                'details.*.quantity.required' => 'Số lượng là bắt buộc',
                'details.*.quantity.min'  => 'Số lượng phải lớn hơn 0',
            ]);


            if(!empty($request->details)){
                $details = collect($request->details)
                            ->groupBy('id_ingre')
                            ->map(function ($items) {
                                return $items->sum('quantity');
                            });
                foreach($details as $ingre => $quantity){
                    $branchIngre = BranchIngredient::with('ingredient')
                                    ->where('id_br', $request->id_br)
                                    ->where('id_ingre', $ingre)
                                    ->first();

                    if($branchIngre->quantity < $quantity){
                        return response()->json([
                                'error'  => true,
                                'message'  => 'Nguyên liệu '.$branchIngre->ingredient->name.' không đủ số lượng.',
                            ]);
                    }
                }
            }

            $receipt = $this->inventory->createManualExport(
                (int) $request->id_br,
                (string) $request->reason,
                $request->note,
                $request->details
            );

            return response()->json([
                'success'  => true,
                'message'  => 'Tạo phiếu xuất kho thành công! Vui lòng chờ admin duyệt.',
                'redirect' => route('admin.export.show', $receipt->id_ex),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Chi tiết phiếu xuất.
     */
    public function show($id)
    {
        $receipt = ExportReceipts::with(['branch', 'user', 'details.ingredient'])
            ->findOrFail($id);

        $ingredients = Ingredient::where('status', 1)->orderBy('name')->get();
        $stocks = BranchIngredient::where('id_br', $receipt->id_br)->get()
            ->keyBy('id_ingre');

        

        return view('admin.pages.export_receipt_show', compact('receipt', 'ingredients', 'stocks'));
    }

    /**
     * Admin duyệt phiếu xuất thủ công => trừ tồn kho.
     */
    public function approve($id)
    {
        try {
            $receipt = ExportReceipts::with('details')->findOrFail($id);
            $this->inventory->approveExport($receipt);

            toastr('Duyệt phiếu xuất kho thành công! Đã trừ tồn kho.', 'success', 'Thành công');
            return redirect()->route('admin.export.show', $id);
        } catch (\Exception $e) {
            return redirect()->route('admin.export.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Admin từ chối phiếu xuất thủ công.
     */
    public function reject($id)
    {

        try {
            $receipt = ExportReceipts::findOrFail($id);

            if ($receipt->status == 1) {
                throw new \Exception('Phiếu xuất đã được duyệt, không thể từ chối');
            }
            if ($receipt->status == 2) {
                throw new \Exception('Phiếu xuất đã bị từ chối trước đó');
            }

            DB::transaction(function () use ($receipt) {
                $receipt->status = 2;
                $receipt->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối phiếu xuất kho!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa phiếu chờ duyệt (phiếu đã duyệt không được xóa để giữ lịch sử).
     */
    public function destroy($id)
    {
        try {
            $receipt = ExportReceipts::findOrFail($id);

            if ((int) $receipt->status === 1) {
                toastr('Phiếu đã được duyệt, không thể xóa', 'error', 'Lỗi');
                return redirect()->back();;
            }

            DB::transaction(function () use ($receipt) {
                $receipt->details()->delete();
                $receipt->delete();
            });

            toastr('Xóa phiếu thành công', 'success', 'Thành công');
            return redirect(route('admin.export.index'));
        } catch (\Exception $e) {
            toastr($e->getMessage(), 'error', 'Lỗi');
            return redirect()->back();
        }
    }

    /**
     * Thêm dòng nguyên liệu vào phiếu xuất khi chưa duyệt.
     * Chỉ áp dụng cho phiếu thủ công (reason != sale) đang ở trạng thái chờ duyệt.
     */
    public function addDetail(Request $request, $id)
    {
        try {
            $receipt = ExportReceipts::findOrFail($id);

            // Không cho sửa phiếu đã duyệt / từ chối
            if ((int) $receipt->status !== 0) {
                throw new \Exception('Phiếu xuất đã duyệt hoặc đã từ chối, không thể thêm dòng');
            }
            // Phiếu tự động (sale) do hệ thống tạo, không cho thêm thủ công
            if ($receipt->reason === 'sale') {
                throw new \Exception('Phiếu tự động từ bán hàng không thể thêm dòng thủ công');
            }

            $request->validate([
                'id_ingre' => 'required|exists:ingredients,id_ingre',
                'quantity' => 'required|numeric|min:0.01',
            ], [
                'id_ingre.required' => 'Vui lòng chọn nguyên liệu',
                'id_ingre.exists'   => 'Nguyên liệu không tồn tại',
                'quantity.required' => 'Số lượng là bắt buộc',
                'quantity.min'      => 'Số lượng phải lớn hơn 0',
            ]);

            // Không cho trùng nguyên liệu với dòng đã có.
            $exists = $receipt->details()->where('id_ingre', $request->id_ingre)->exists();
            if ($exists) {
                throw new \Exception('Nguyên liệu này đã có trong phiếu, hãy chỉnh số lượng ở dòng tương ứng.');
            }

            // Kiểm tra tồn kho chi nhánh nguồn.
            $stock = BranchIngredient::where('id_br', $receipt->id_br)
                ->where('id_ingre', $request->id_ingre)
                ->first();
            $stockQty = $stock ? (float) $stock->quantity : 0.0;
            if ($stockQty <= 0) {
                throw new \Exception('Chi nhánh không có tồn kho của nguyên liệu này.');
            }
            if ((float) $request->quantity > $stockQty) {
                throw new \Exception("Số lượng chuyển ({$request->quantity}) vượt quá tồn kho chi nhánh ({$stockQty}).");
            }

            ExportDetail::create([
                'id_ex'    => $receipt->id_ex,
                'id_food'  => null,
                'id_ingre' => $request->id_ingre,
                'quantity' => $request->quantity,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Thêm nguyên liệu thành công!',
                'redirect' => route('admin.export.show', $id),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật số lượng của một dòng nguyên liệu khi phiếu chưa duyệt.
     */
    public function updateDetail(Request $request, $id, $detailId)
    {
        try {
            $receipt = ExportReceipts::findOrFail($id);

            if ((int) $receipt->status !== 0) {
                throw new \Exception('Phiếu xuất đã duyệt hoặc đã từ chối, không thể chỉnh sửa');
            }
            if ($receipt->reason === 'sale') {
                throw new \Exception('Phiếu tự động từ bán hàng không thể chỉnh sửa');
            }

            $request->validate([
                'quantity' => 'required|numeric|min:0.01',
            ], [
                'quantity.required' => 'Số lượng là bắt buộc',
                'quantity.min'      => 'Số lượng phải lớn hơn 0',
            ]);

            $detail = ExportDetail::where('id_ex', $id)
                ->where('id_export_detail', $detailId)
                ->firstOrFail();
            
            $stock = BranchIngredient::where('id_br', $receipt->id_br)
                ->where('id_ingre', $detail->id_ingre)
                ->first();
            $stockQty = $stock ? (float) $stock->quantity : 0.0;
            if ((float) $request->quantity > $stockQty) {
                throw new \Exception("Số lượng chuyển ({$request->quantity}) vượt quá tồn kho chi nhánh ({$stockQty}).");
            }

            $detail->quantity = $request->quantity;
            $detail->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật số lượng thành công!',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa một dòng nguyên liệu khi phiếu chưa duyệt (giữ ít nhất 1 dòng).
     */
    public function deleteDetail($id, $detailId)
    {
        try {
            $receipt = ExportReceipts::with('details')->findOrFail($id);

            if ((int) $receipt->status !== 0) {
                throw new \Exception('Phiếu xuất đã duyệt hoặc đã từ chối, không thể xóa dòng');
            }
            if ($receipt->reason === 'sale') {
                throw new \Exception('Phiếu tự động từ bán hàng không thể xóa dòng');
            }
            if ($receipt->details->count() <= 1) {
                throw new \Exception('Phiếu xuất phải có ít nhất 1 dòng nguyên liệu');
            }

            $detail = ExportDetail::where('id_ex', $id)
                ->where('id_export_detail', $detailId)
                ->firstOrFail();

            $detail->delete();

            toastr('Xóa nguyên liệu thành công.', 'success', 'Thành công');
            return redirect()->back();
        } catch (\Exception $e) {
            toastr($e->getMessage(), 'error', 'Lỗi');
            return redirect()->back();
        }
    }
}
