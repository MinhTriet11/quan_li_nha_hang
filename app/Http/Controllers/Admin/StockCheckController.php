<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchIngredient;
use App\Models\Ingredient;
use App\Models\StockCheckDetail;
use App\Models\StockCheckReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockCheckController extends Controller
{
    /**
     * Danh sách phiếu kiểm kê.
     */
    public function index(Request $request)
    {
        $query = StockCheckReceipt::with(['branch', 'user', 'details']);

        if ($request->filled('branch_id')) {
            $query->where('id_br', $request->branch_id);
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = preg_replace('/[^0-9]/', '', $request->search);
            $query->where('id_stock', 'like', "%{$search}%");
        }

        $receipts = $query->orderBy('created_at')->paginate(10)->withQueryString();
        $branches = Branch::orderBy('name')->get();

        return view('admin.pages.stock_check_index', compact('receipts', 'branches'));
    }

    /**
     * Form tạo phiếu kiểm kê. Nếu đã chọn branch_id (qua GET) sẽ render sẵn bảng nguyên liệu.
     */
    public function create(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $preselectedBranchId = $request->get('branch_id');
        $stocks = null;

        if ($preselectedBranchId) {
            $stocks = BranchIngredient::with('ingredient')
                ->where('id_br', $preselectedBranchId)
                ->get()
                ->map(function ($item) {
                    $item->actual_quantity = (float) $item->quantity;
                    return $item;
                });
        }

        return view('admin.pages.stock_check_create', compact(
            'branches',
            'preselectedBranchId',
            'stocks'
        ));
    }

    /**
     * Lưu phiếu kiểm kê (status = 0, chờ duyệt). Snapshot system_quantity + difference tại thời điểm lưu.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_br'                       => 'required|exists:branches,id_br',
                // 'note'                        => 'nullable|string|max:1000',
                'details'                     => 'required|array|min:1',
                'details.*.id_ingre'          => 'required|exists:ingredients,id_ingre',
                'details.*.actual_quantity'   => 'required|numeric|min:0',
            ], [
                'id_br.required'              => 'Vui lòng chọn chi nhánh',
                'id_br.exists'                => 'Chi nhánh không tồn tại',
                'details.required'            => 'Vui lòng thêm ít nhất 1 dòng nguyên liệu',
                'details.min'                 => 'Vui lòng thêm ít nhất 1 dòng nguyên liệu',
                'details.*.id_ingre.exists'   => 'Nguyên liệu không tồn tại',
                'details.*.actual_quantity.required' => 'Số lượng thực tế là bắt buộc',
                'details.*.actual_quantity.min'      => 'Số lượng thực tế phải >= 0',
            ]);

            DB::transaction(function () use ($request) {
                $receipt = StockCheckReceipt::create([
                    'id_br'   => $request->id_br,
                    'id_user' => Auth::id(),
                    'status'  => 0,
                ]);

                foreach ($request->details as $detail) {
                    $system = (float) BranchIngredient::where('id_br', $request->id_br)
                        ->where('id_ingre', $detail['id_ingre'])
                        ->value('quantity');

                    $actual = (float) $detail['actual_quantity'];

                    StockCheckDetail::create([
                        'id_stock'         => $receipt->id_stock,
                        'id_ingre'         => $detail['id_ingre'],
                        'system_quantity'  => $system,
                        'actual_quantity'  => $actual,
                    ]);
                }
            });

            return response()->json([
                'success'  => true,
                'message'  => 'Tạo phiếu kiểm kê thành công!',
                'redirect' => route('admin.stock_check.index'),
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
     * Xem chi tiết phiếu kiểm kê.
     */
    public function show(string $id)
    {
        $receipt = StockCheckReceipt::with([
            'branch',
            'user',
            'details.ingredient',
        ])->findOrFail($id);

        // Danh sách nguyên liệu của chi nhánh
        $ingredients = Ingredient::where('status', 1)->orderBy('name')->get();
        $stocks = BranchIngredient::where('id_br', $receipt->id_br)->get()->keyBy('id_ingre');

        return view('admin.pages.stock_check_show', compact('receipt', 'ingredients', 'stocks'));
    }

    /**
     * Duyệt phiếu: cập nhật trực tiếp branch_ingredient.quantity = actual_quantity (giống TransferController).
     */
    public function approve(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $receipt = StockCheckReceipt::with('details')->findOrFail($id);

                if ((int) $receipt->status === 1) {
                    throw new \Exception('Phiếu kiểm kê đã được duyệt trước đó');
                }
                if ((int) $receipt->status === 2) {
                    throw new \Exception('Phiếu kiểm kê đã bị từ chối, không thể duyệt');
                }

                foreach ($receipt->details as $detail) {
                    BranchIngredient::updateOrCreate(
                        [
                            'id_br'    => $receipt->id_br,
                            'id_ingre' => $detail->id_ingre,
                        ],
                        [
                            'quantity' => $detail->actual_quantity,
                        ]
                    );
                }

                $receipt->status = 1;
                $receipt->save();
            });

            toastr('Duyệt phiếu kiểm kê thành công! Tồn kho đã được cập nhật.', 'success', 'Thành công');
            return redirect(route('admin.stock_check.show', $id));
        } catch (\Exception $e) {
            toastr($e->getMessage(), 'error', 'Lỗi');
            return redirect()->back();
        }
    }

    /**
     * Từ chối phiếu kiểm kê (giống TransferController).
     */
    public function reject(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $receipt = StockCheckReceipt::findOrFail($id);

                if ((int) $receipt->status === 1) {
                    throw new \Exception('Phiếu đã được duyệt, không thể từ chối.');
                }
                if ((int) $receipt->status === 2) {
                    throw new \Exception('Phiếu đã bị t� chối trước đó.');
                }

                $receipt->status = 2;
                $receipt->save();
            });

            toastr('Đã từ chối phiếu kiểm kê.', 'success', 'Thành công');
            return redirect(route('admin.stock_check.show', $id));
        } catch (\Exception $e) {
            toastr('Có lỗi xảy ra: ' . $e->getMessage(), 'error', 'Lỗi');
            return redirect()->back();
        }
    }

    /**
     * Xóa phiếu (chỉ khi chưa duyệt) — giống TransferController.
     */
    public function destroy(string $id)
    {
        try {
            $receipt = StockCheckReceipt::findOrFail($id);

            if ((int) $receipt->status === 1) {
                toastr('Phiếu kiểm kê đã được duyệt, không thể xóa', 'error', 'Lỗi');
                return redirect()->back();
            }

            DB::transaction(function () use ($receipt) {
                $receipt->details()->delete();
                $receipt->delete();
            });

            toastr('Xóa phiếu kiểm kê thành công', 'success', 'Thành công');
            return redirect(route('admin.stock_check.index'));
        } catch (\Exception $e) {
            toastr($e->getMessage(), 'error', 'Lỗi');
            return redirect()->back();
        }
    }

    /**
     * Cập nhật số lượng 1 dòng nguyên liệu (chỉ khi phiếu còn ch� duyệt).
     */
    public function updateDetail(Request $request, string $id, string $detailId)
    {
        try {
            $receipt = StockCheckReceipt::findOrFail($id);
            if ((int) $receipt->status !== 0) {
                throw new \Exception('Phiếu đã duyệt hoặc đã từ chối, không thể chỉnh sửa.');
            }

            $request->validate([
                'actual_quantity' => 'required|numeric|min:0',
            ], [
                'actual_quantity.required' => 'Số lượng thực tế là bắt buộc',
                'actual_quantity.min'      => 'Số lượng thực tế phải >= 0',
            ]);

            $detail = StockCheckDetail::where('id_stock', $id)
                ->where('id_sto_detail', $detailId)
                ->firstOrFail();

            $actual = (float) $request->actual_quantity;
            $detail->actual_quantity = $actual;
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

}
