<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchIngredient;
use App\Models\ImportDetail;
use App\Models\ImportReceipts;
use App\Models\Ingredient;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = ImportReceipts::with(['branch', 'user', 'details.ingredient', 'details.supplier']);

        // Filter theo chi nhánh
        if ($request->has('branch') && $request->branch !== null && $request->branch !== '') {
            $query->where('id_br', $request->branch);
        }

        // Filter theo trạng thái
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search theo mã phiếu
        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $search = preg_replace('/[^0-9]/', '', $search);
            $query->where('id_im', 'like', "%{$search}%");
        }

        $receipts = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $branches = Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.pages.import_receipts', compact('receipts', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('status', 1)->orderBy('name')->get();
        $suppliers = Supplier::where('status', 1)->orderBy('name')->get();
        $ingredients = Ingredient::where('status', 1)->orderBy('name')->get();

        return view('admin.pages.import_receipt_create', compact('branches', 'suppliers', 'ingredients'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_br'              => 'required|exists:branches,id_br',
                'details'            => 'required|array|min:1',
                'details.*.id_ingre' => 'required|exists:ingredients,id_ingre',
                'details.*.id_su'    => 'required|exists:supplier,id_su',
                'details.*.quantity' => 'required|numeric|min:0.01',
                'details.*.price'    => 'required|numeric|min:0',
            ], [
                'id_br.required'              => 'Vui lòng chọn chi nhánh',
                'id_br.exists'                => 'Chi nhánh không tồn tại',
                'details.required'            => 'Vui lòng thêm ít nhất 1 dòng nguyên liệu',
                'details.min'                 => 'Vui lòng thêm ít nhất 1 dòng nguyên liệu',
                'details.*.id_ingre.required' => 'Vui lòng chọn nguyên liệu',
                'details.*.id_ingre.exists'   => 'Nguyên liệu không tồn tại',
                'details.*.id_su.required'    => 'Vui lòng chọn nhà cung cấp',
                'details.*.id_su.exists'      => 'Nhà cung cấp không tồn tại',
                'details.*.quantity.required' => 'Số lượng là bắt buộc',
                'details.*.quantity.min'      => 'Số lượng phải lớn hơn 0',
                'details.*.price.required'    => 'Đơn giá là bắt buộc',
                'details.*.price.min'         => 'Đơn giá phải lớn hơn hoặc bằng 0',
            ]);

            DB::transaction(function () use ($request) {
                $receipt = ImportReceipts::create([
                    'id_br'   => $request->id_br,
                    'id_user' => Auth::id() ?? null,
                    'status'  => 0, // chờ duyệt
                ]);

                foreach ($request->details as $detail) {
                    ImportDetail::create([
                        'id_im'    => $receipt->id_im,
                        'id_ingre' => $detail['id_ingre'],
                        'id_su'    => $detail['id_su'],
                        'quantity' => $detail['quantity'],
                        'price'    => $detail['price'],
                    ]);
                }
            });

            return response()->json([
                'success'  => true,
                'message'  => 'Tạo phiếu nhập kho thành công!',
                'redirect' => route('admin.import.index'),
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

    public function show(string $id)
    {
        $receipt = ImportReceipts::with(['branch', 'user', 'details.ingredient', 'details.supplier'])
            ->findOrFail($id);

        $branches = Branch::where('status', 1)->orderBy('name')->get();
        $suppliers = Supplier::where('status', 1)->orderBy('name')->get();
        $ingredients = Ingredient::where('status', 1)->orderBy('name')->get();

        return view('admin.pages.import_receipt_show', compact('receipt', 'branches', 'suppliers', 'ingredients'));
    } 

    public function addDetail(Request $request, string $id)
    {
        try {
            $receipt = ImportReceipts::findOrFail($id);

            if ($receipt->status == 1) {
                throw new \Exception('Phiếu nhập đã duyệt, không thể thêm dòng');
            }

            $request->validate([
                'id_ingre' => 'required|exists:ingredients,id_ingre',
                'id_su'    => 'required|exists:supplier,id_su',
                'quantity' => 'required|numeric|min:0.01',
                'price'    => 'required|numeric|min:0',
            ], [
                'id_ingre.required' => 'Vui lòng chọn nguyên liệu',
                'id_ingre.exists'   => 'Nguyên liệu không tồn tại',
                'id_su.required'    => 'Vui lòng chọn nhà cung cấp',
                'id_su.exists'      => 'Nhà cung cấp không tồn tại',
                'quantity.required' => 'Số lượng là bắt buộc',
                'quantity.min'      => 'Số lượng phải lớn hơn 0',
                'price.required'    => 'Đơn giá là bắt buộc',
                'price.min'         => 'Đơn giá phải lớn hơn hoặc bằng 0',
            ]);

            ImportDetail::create([
                'id_im'    => $receipt->id_im,
                'id_ingre' => $request->id_ingre,
                'id_su'    => $request->id_su,
                'quantity' => $request->quantity,
                'price'    => $request->price,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Thêm nguyên liệu thành công!',
                'redirect' => route('admin.import.show', $id),
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

    public function updateDetail(Request $request, string $id, string $detailId)
    {
        try {
            $receipt = ImportReceipts::findOrFail($id);

            if ($receipt->status == 1) {
                throw new \Exception('Phiếu nhập đã duyệt, không thể chỉnh sửa');
            }

            $request->validate([
                'quantity' => 'required|numeric|min:0.01',
            ], [
                'quantity.required' => 'Số lượng là bắt buộc',
                'quantity.min'      => 'Số lượng phải lớn hơn 0',
            ]);

            $detail = ImportDetail::where('id_im', $id)
                ->where('id_import_detail', $detailId)
                ->firstOrFail();

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
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDetail(string $id, string $detailId)
    {
        try {
            $receipt = ImportReceipts::with('details')->findOrFail($id);

            if ($receipt->status == 1) {
                throw new \Exception('Phiếu nhập đã duyệt, không thể xóa dòng');
            }

            if ($receipt->details->count() <= 1) {
                throw new \Exception('Phiếu nhập phải có ít nhất 1 dòng nguyên liệu');
            }

            $detail = ImportDetail::where('id_im', $id)
                ->where('id_import_detail', $detailId)
                ->firstOrFail();

            $detail->delete();

            
            toastr('Xóa nguyên liệu thành công.', 'success', 'Thành công');
            return redirect()->back();

        } catch (\Exception $e) {
            toastr($e->getMessage(), 'error', 'Lỗi');
            return redirect()->back();
        }
    }

    public function approve(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $receipt = ImportReceipts::with('details')->findOrFail($id);

                if ($receipt->status == 1) {
                    throw new \Exception('Phiếu nhập đã được duyệt trước đó');
                }

                // Cộng tồn kho theo từng (id_br, id_ingre)
                foreach ($receipt->details as $detail) {
                    $stock = BranchIngredient::where('id_br', $receipt->id_br)
                        ->where('id_ingre', $detail->id_ingre)
                        ->first();

                    if ($stock) {
                        $stock->quantity = $stock->quantity + $detail->quantity;
                        $stock->save();
                    } else {
                        BranchIngredient::create([
                            'id_br'    => $receipt->id_br,
                            'id_ingre' => $detail->id_ingre,
                            'quantity' => $detail->quantity,
                        ]);
                    }
                }

                $receipt->status = 1;
                $receipt->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Duyệt phiếu nhập kho thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $receipt = ImportReceipts::findOrFail($id);

            if ($receipt->status == 1) {
                toastr('Phiếu nhập đã được duyệt, không thể xóa', 'error', 'Lỗi');
                return redirect()->back();
            }

            DB::transaction(function () use ($receipt) {
                $receipt->details()->delete();
                $receipt->delete();
            });

            toastr('Xóa phiếu nhập thành công', 'success', 'Thành công');
            return redirect(route('admin.import.index'));
        } catch (\Exception $e) {
            toastr($e->getMessage(), 'error', 'Lỗi');
            return redirect()->back();
        }
    }

    public function reject(string $id)
    {
        try {
            $receipt = ImportReceipts::findOrFail($id);

            if ($receipt->status == 1) {
                throw new \Exception('Phiếu nhập đã được duyệt, không thể từ chối');
            }
            if ($receipt->status == 2) {
                throw new \Exception('Phiếu nhập đã bị từ chối trước đó');
            }

            DB::transaction(function () use ($receipt) {
                $receipt->status = 2;
                $receipt->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối phiếu nhập kho!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
