<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $suppliers = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.pages.suppliers', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.pages.supplier_create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'    => 'required|string|max:255|unique:supplier,name',
                'phone'   => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
                'email'   => 'nullable|email|max:255',
                'address' => 'required|string|max:255',
                'status'  => 'required|in:0,1',
            ], [
                'name.required'    => 'Tên nhà cung cấp là bắt buộc',
                'name.unique'      => 'Tên nhà cung cấp đã tồn tại',
                'phone.regex'      => 'Số điện thoại không hợp lệ',
                'email.email'      => 'Email không hợp lệ',
                'address.required' => 'Địa chỉ là bắt buộc',
                'status.required'  => 'Vui lòng chọn trạng thái',
                'status.in'        => 'Trạng thái không hợp lệ',
            ]);

            $supplier = Supplier::create([
                'name'    => $request->name,
                'phone'   => $request->phone ?? '',
                'email'   => $request->email ?? '',
                'address' => $request->address,
                'status'  => $request->status,
            ]);

            if ($supplier) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Thêm nhà cung cấp thành công!',
                    'redirect' => route('admin.supplier'),
                ]);
            }

            return response()->json([
                'error'   => true,
                'message' => 'Vui lòng thử lại sau',
            ], 500);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        }
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.pages.supplier_edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            $request->validate([
                'name'    => 'required|string|max:255|unique:supplier,name,' . $id . ',id_su',
                'phone'   => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
                'email'   => 'nullable|email|max:255',
                'address' => 'required|string|max:255',
                'status'  => 'required|in:0,1',
            ], [
                'name.required'    => 'Tên nhà cung cấp là bắt buộc',
                'name.unique'      => 'Tên nhà cung cấp đã tồn tại',
                'phone.regex'      => 'Số điện thoại không hợp lệ',
                'email.email'      => 'Email không hợp lệ',
                'address.required' => 'Địa chỉ là bắt buộc',
                'status.required'  => 'Vui lòng chọn trạng thái',
                'status.in'        => 'Trạng thái không hợp lệ',
            ]);

            $supplier->update([
                'name'    => $request->name,
                'phone'   => $request->phone ?? '',
                'email'   => $request->email ?? '',
                'address' => $request->address,
                'status'  => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật nhà cung cấp thành công',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            // Khong cho xoa neu dang co phieu nhap kho tham chieu
            if ($supplier->importReceipts()->exists()) {
                return redirect()->back()->with('error', 'Nhà cung cấp đang có phiếu nhập kho, không thể xóa');
            }

            $supplier->delete();
            return redirect()->back()->with('success', 'Xóa nhà cung cấp thành công');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra');
        }
    }
}