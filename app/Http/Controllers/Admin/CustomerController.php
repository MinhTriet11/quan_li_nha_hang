<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $customers = $query->orderBy('id_cus', 'desc')->paginate(10);

        return view('admin.pages.customers', compact('customers'));
    }

    public function create()
    {
        return view('admin.pages.customer_create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|max:255|unique:customers,email',
                'phone'    => 'nullable|string|max:20',
                'address'  => 'nullable|string|max:255',
                'password' => 'required|string|min:6|confirmed',
                'status'   => 'required|in:0,1',
            ], [
                'name.required'        => 'Tên khách hàng là bắt buộc',
                'email.required'       => 'Email là bắt buộc',
                'email.email'          => 'Email không đúng định dạng',
                'email.unique'         => 'Email đã tồn tại',
                'password.required'    => 'Mật khẩu là bắt buộc',
                'password.min'         => 'Mật khẩu tối thiểu 6 ký tự',
                'password.confirmed'   => 'Mật khẩu xác nhận không khớp',
                'status.required'      => 'Vui lòng chọn trạng thái',
            ]);

            $customer = Customer::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'address'  => $request->address,
                'password' => Hash::make($request->password),
                'status'   => $request->status,
            ]);

            if ($customer) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Tạo khách hàng thành công!',
                    'redirect' => route('admin.customer.create'),
                ]);
            }

            return response()->json([
                'error'   => false,
                'message' => 'Vui lòng thử lại sau',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => false,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        }
    }

    public function show($id)
    {
        $customer = Customer::with('Books')->findOrFail($id);
        return view('admin.pages.customer_show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.pages.customer_edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            $rules = [
                'name'    => 'required|string|max:255',
                'email'   => 'required|email|max:255|unique:customers,email,' . $id . ',id_cus',
                'phone'   => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'status'  => 'required|in:0,1',
            ];

            // Chỉ validate password khi admin nhập mới
            if ($request->filled('password')) {
                $rules['password'] = 'nullable|string|min:6|confirmed';
            }

            $request->validate($rules, [
                'name.required'   => 'Tên khách hàng là bắt buộc',
                'email.required'  => 'Email là bắt buộc',
                'email.email'     => 'Email không đúng định dạng',
                'email.unique'    => 'Email đã tồn tại',
                'password.min'    => 'Mật khẩu tối thiểu 6 ký tự',
                'password.confirmed' => 'Mật khẩu xác nhận không khớp',
                'status.required' => 'Vui lòng chọn trạng thái',
            ]);

            $customer->name    = $request->name;
            $customer->email   = $request->email;
            $customer->phone   = $request->phone;
            $customer->address = $request->address;
            $customer->status  = $request->status;

            if ($request->filled('password')) {
                $customer->password = Hash::make($request->password);
            }

            $customer->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật khách hàng thành công',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => false,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        }
    }

    // Toggle khóa/mở khách hàng (không xóa cứng)
    public function toggleStatus($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            // status = 1: mở khóa (đang hoạt động), status = 0: đã khóa
            $customer->status = $customer->status == 1 ? 0 : 1;
            $customer->save();

            return redirect()->route('admin.customer')
                ->with('success', $customer->status == 1 ? 'Đã mở khóa khách hàng' : 'Đã khóa khách hàng');
        } catch (\Exception $e) {
            return redirect()->route('admin.customer')->with('error', 'Có lỗi xảy ra');
        }
    }

    // Xóa khách hàng - chỉ cho phép khi chưa có lịch sử đặt bàn
    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            if ($customer->Books()->count() > 0) {
                return redirect()->route('admin.customer')
                    ->with('error', 'Không thể xóa khách hàng đã có lịch sử đặt bàn');
            }

            $customer->delete();

            return redirect()->route('admin.customer')
                ->with('success', 'Đã xóa khách hàng thành công');
        } catch (\Exception $e) {
            return redirect()->route('admin.customer')->with('error', 'Có lỗi xảy ra');
        }
    }
}
