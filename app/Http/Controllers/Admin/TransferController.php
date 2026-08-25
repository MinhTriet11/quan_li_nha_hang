<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchIngredient;
use App\Models\Ingredient;
use App\Models\TransDetail;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    /**
     * Danh sách phiếu chuyển kho (lọc theo chi nhánh nguồn/đích, trạng thái, từ khóa).
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['fromBranch', 'toBranch', 'user', 'details.ingredient']);

        
        if ($request->filled('from_branch')) {
            $query->where('from_branch', $request->from_branch);
        }
        
        if ($request->filled('to_branch')) {
            $query->where('to_branch', $request->to_branch);
        }
        
        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }
        // Tìm theo mã phiếu
        if ($request->filled('search')) {
            $kw = $request->search;
            $search = preg_replace('/[^0-9]/', '', $kw);
            $query->where('id_tran', 'like', "%{$search}%");
        }

        $transfers = $query->orderBy('created_at')->paginate(10)->withQueryString();
        $branches = Branch::orderBy('name')->get();

        return view('admin.pages.transfer', compact('transfers', 'branches'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $ingredients = Ingredient::where('status', 1)->orderBy('name')->get();
        // Tồn kho theo từng (id_br, id_ingre) — dùng để hiển thị cho chi nhánh nguồn
        $stocks = BranchIngredient::with('ingredient')->get();

        return view('admin.pages.transfer_create', compact('branches', 'ingredients', 'stocks'));
    }

    public function getBranchTo($id_br){
        $rows = Branch::where('id_br', '!=' , $id_br)
                    ->where('status', 1)
                    ->orderBy('name')
                    ->get();

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'from_branch'        => 'required|exists:branches,id_br',
                'to_branch'          => 'required|exists:branches,id_br|different:from_branch',
                'details'            => 'required|array|min:1',
                'details.*.id_ingre' => 'required|exists:ingredients,id_ingre',
                'details.*.quantity' => 'required|numeric|min:0.01',
            ], [
                'from_branch.required'         => 'Vui lòng chọn chi nhánh nguồn',
                'to_branch.required'           => 'Vui lòng chọn chi nhánh đích',
                'to_branch.different'          => 'Chi nhánh đích phải khác chi nhánh nguồn',
                'details.required'             => 'Vui lòng thêm ít nhất 1 dòng nguyên liệu',
                'details.*.id_ingre.required'  => 'Vui lòng chọn nguyên liệu',
                'details.*.id_ingre.exists'    => 'Nguyên liệu không tồn tại',
                'details.*.quantity.required'  => 'Số lượng là bắt buộc',
                'details.*.quantity.min'       => 'Số lượng phải lớn hơn 0',
            ]);

            DB::transaction(function () use ($request) {
                $transfer = Transfer::create([
                    'from_branch' => (int) $request->from_branch,
                    'to_branch'   => (int) $request->to_branch,
                    'id_user'     => Auth::id() ?? null,
                    'status'      => 0, // chờ duyệt
                ]);

                foreach ($request->details as $detail) {
                    TransDetail::create([
                        'id_tran'  => $transfer->id_tran,
                        'id_ingre' => $detail['id_ingre'],
                        'quantity' => $detail['quantity'],
                    ]);
                }
            });

            return response()->json([
                'success'  => true,
                'message'  => 'Tạo phiếu chuyển kho thành công! Vui lòng chờ admin duyệt.',
                'redirect' => route('admin.transfer.index'),
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
     * Chi tiết phiếu chuyển kho.
     */
    public function show($id)
    {
        $transfer = Transfer::with(['fromBranch', 'toBranch', 'user', 'details.ingredient'])
            ->findOrFail($id);

        // Chuẩn bị dữ liệu cho form "thêm nguyên liệu" (chỉ dùng khi status=0).
        $ingredients = Ingredient::where('status', 1)->orderBy('name')->get();
        $stocks = BranchIngredient::where('id_br', $transfer->from_branch)->get()
            ->keyBy('id_ingre');

        

        return view('admin.pages.transfer_show', compact('transfer', 'ingredients', 'stocks'));
    }

    /**
     * Duyệt phiếu chuyển kho: trừ tồn kho chi nhánh nguồn, cộng tồn kho chi nhánh đích.
     */
    public function approve($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $transfer = Transfer::with('details')->findOrFail($id);

                if ((int) $transfer->status === 1) {
                    throw new \Exception('Phiếu chuyển kho đã được duyệt trước đó');
                }
                if ((int) $transfer->status === 2) {
                    throw new \Exception('Phiếu chuyển kho đã bị từ chối, không thể duyệt');
                }

                // Kiểm tra tồn kho chi nhánh nguồn trước khi trừ
                foreach ($transfer->details as $detail) {
                    $stock = BranchIngredient::where('id_br', $transfer->from_branch)
                        ->where('id_ingre', $detail->id_ingre)
                        ->first();
                    if (!$stock || (float) $stock->quantity < (float) $detail->quantity) {
                        $ingre = $detail->ingredient;
                        $name = $ingre->name ?? ('ID ' . $detail->id_ingre);
                        throw new \Exception("Nguyên liệu \"{$name}\" không đủ tồn kho ở chi nhánh nguồn để chuyển.");
                    }
                }

                // Trừ tồn kho nguồn, cộng tồn kho đích
                foreach ($transfer->details as $detail) {
                    // Trừ ở chi nhánh nguồn
                    $srcStock = BranchIngredient::where('id_br', $transfer->from_branch)
                        ->where('id_ingre', $detail->id_ingre)
                        ->first();
                    $srcStock->quantity = (float) $srcStock->quantity - (float) $detail->quantity;
                    $srcStock->save();

                    // Cộng ở chi nhánh đích (nếu chưa có dòng thì tạo mới)
                    $dstStock = BranchIngredient::where('id_br', $transfer->to_branch)
                        ->where('id_ingre', $detail->id_ingre)
                        ->first();
                    if ($dstStock) {
                        $dstStock->quantity = (float) $dstStock->quantity + (float) $detail->quantity;
                        $dstStock->save();
                    } else {
                        BranchIngredient::create([
                            'id_br'    => $transfer->to_branch,
                            'id_ingre' => $detail->id_ingre,
                            'quantity' => $detail->quantity,
                        ]);
                    }
                }

                $transfer->status = 1;
                $transfer->save();
            });


            toastr('Duyệt phiếu chuyển kho thành công! Đã cập nhật tồn kho giữa 2 chi nhánh.', 'success', 'Thành công');
            return redirect(route('admin.transfer.show', $id));
        } catch (\Exception $e) {
            return redirect()->route('admin.transfer.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Từ chối phiếu chuyển kho (chỉ thay đổi trạng thái, không động vào tồn kho).
     * Hỗ trợ cả AJAX (Accept: application/json) lẫn submit form thường.
     */
    public function reject(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $transfer = Transfer::findOrFail($id);

                if ((int) $transfer->status === 1) {
                    throw new \Exception('Phiếu đã duyệt, không thể từ chối.');
                }
                if ((int) $transfer->status === 2) {
                    throw new \Exception('Phiếu đã bị từ chối trước đó.');
                }

                $transfer->status = 2;
                $transfer->save();
            });

            // if ($request->wantsJson()) {
            //     return response()->json([
            //         'success' => true,
            //         'message' => 'Đã từ chối phiếu chuyển kho r.',
            //     ]);
            // }

            toastr('Đã từ chối phiếu chuyển kho.', 'success', 'Thành công');
            return redirect(route('admin.transfer.show', $id));
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error'   => true,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.transfer.show', $id)
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xóa phiếu chờ duyệt (phiếu đã duyệt không được xóa để giữ lịch sử).
     */
    public function destroy($id)
    {
        try {
            $transfer = Transfer::findOrFail($id);

            if ((int) $transfer->status === 1) {
                return redirect()->back()->with('error', 'Phiếu đã duyệt, không thể xóa.');
            }

            DB::transaction(function () use ($transfer) {
                $transfer->details()->delete();
                $transfer->delete();
            });

            toastr('Xóa phiếu thành công', 'success', 'Thành công');
            return redirect(route('admin.transfer.index'));
        } catch (\Exception $e) {
            toastr($e->getMessage(), 'error', 'Lỗi');
            return redirect()->back();
        }
    }

    /**
     * Thêm 1 dòng nguyên liệu vào phiếu chuyển (chỉ khi phiếu còn chờ duyệt).
     * Số lượng không được vượt quá tồn kho chi nhánh nguồn.
     */
    public function addDetail(Request $request, $id)
    {
        try {
            $transfer = Transfer::findOrFail($id);
            if ((int) $transfer->status !== 0) {
                throw new \Exception('Phiếu đã duyệt hoặc đã từ chối, không thể thêm dòng.');
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
            $exists = $transfer->details()->where('id_ingre', $request->id_ingre)->exists();
            if ($exists) {
                throw new \Exception('Nguyên liệu này đã có trong phiếu, hãy chỉnh số lượng ở dòng tương ứng.');
            }

            // Kiểm tra tồn kho chi nhánh nguồn.
            $stock = BranchIngredient::where('id_br', $transfer->from_branch)
                ->where('id_ingre', $request->id_ingre)
                ->first();
            $stockQty = $stock ? (float) $stock->quantity : 0.0;
            if ($stockQty <= 0) {
                throw new \Exception('Chi nhánh nguồn không có tồn kho của nguyên liệu này.');
            }
            if ((float) $request->quantity > $stockQty) {
                throw new \Exception("Số lượng chuyển ({$request->quantity}) vượt quá tồn kho chi nhánh nguồn ({$stockQty}).");
            }

            TransDetail::create([
                'id_tran'  => $transfer->id_tran,
                'id_ingre' => $request->id_ingre,
                'quantity' => $request->quantity,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Thêm nguyên liệu thành công!',
                'redirect' => route('admin.transfer.show', $id),
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
     * Cập nhật số lượng 1 dòng nguyên liệu của phiếu chuyển (chỉ khi chờ duyệt).
     */
    public function updateDetail(Request $request, $id, $detailId)
    {
        try {
            $transfer = Transfer::findOrFail($id);
            if ((int) $transfer->status !== 0) {
                throw new \Exception('Phiếu đã duyệt hoặc đã từ chối, không thể chỉnh sửa.');
            }

            $request->validate([
                'quantity' => 'required|numeric|min:0.01',
            ], [
                'quantity.required' => 'Số lượng là bắt buộc',
                'quantity.min'      => 'Số lượng phải lớn hơn 0',
            ]);

            $detail = TransDetail::where('id_tran', $id)
                ->where('id_tran_detail', $detailId)
                ->firstOrFail();

            $stock = BranchIngredient::where('id_br', $transfer->from_branch)
                ->where('id_ingre', $detail->id_ingre)
                ->first();
            $stockQty = $stock ? (float) $stock->quantity : 0.0;
            if ((float) $request->quantity > $stockQty) {
                throw new \Exception("Số lượng chuyển ({$request->quantity}) vượt quá tồn kho chi nhánh nguồn ({$stockQty}).");
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
     * Xóa 1 dòng nguyên liệu khỏi phiếu chuyển (giữ ít nhất 1 dòng, chỉ khi chờ duyệt).
     */
    public function deleteDetail($id, $detailId)
    {
        try {
            $transfer = Transfer::with('details')->findOrFail($id);
            if ((int) $transfer->status !== 0) {
                throw new \Exception('Phiếu đã duyệt hoặc đã từ chối, không thể xóa dòng.');
            }
            if ($transfer->details->count() <= 1) {
                throw new \Exception('Phiếu chuyển phải có ít nhất 1 dòng nguyên liệu.');
            }

            $detail = TransDetail::where('id_tran', $id)
                ->where('id_tran_detail', $detailId)
                ->firstOrFail();
            $detail->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa nguyên liệu thành công.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
