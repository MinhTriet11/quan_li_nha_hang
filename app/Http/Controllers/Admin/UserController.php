<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Danh sách các role code mà nhân viên BẮT BUỘC phải gán chi nhánh.
     * (manager, chef, staff, waiter, cashier, …)
     *
     * Lưu ý: phải khớp với `codeMap` ở view user_create / user_edit.
     */
    public const REQUIRED_BRANCH_ROLE_CODES = ['manager', 'chef', 'staff', 'waiter', 'cashier'];

    /**
     * Kiểm tra id_role có thuộc nhóm bắt buộc chi nhánh hay không.
     */
    protected function roleRequiresBranch($idRole): bool
    {
        $codeMap = [1 => 'admin', 2 => 'manager', 3 => 'staff', 4 => 'chef', 5 => 'warehouse', 6 => 'waiter'];
        $code = $codeMap[(int) $idRole] ?? '';
        return in_array($code, self::REQUIRED_BRANCH_ROLE_CODES, true);
    }

    public function index(Request $request)
    {
        $query = User::with(['branch', 'role'])
            ->withCount(['order', 'importReceipts', 'exportReceipts', 'stockCheckReceipts', 'transfers as transfers_count']);

        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('id_role') && $request->id_role !== null && $request->id_role !== '') {
            $query->where('id_role', $request->id_role);
        }

        $users = $query->orderBy('id_user', 'desc')->paginate(10);
        $roles = Role::all();

        // dd($roles);

        return view('admin.pages.users', compact('users', 'roles'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $roles = Role::all();
        return view('admin.pages.user_create', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        try {
            $requiresBranch = $this->roleRequiresBranch($request->id_role);

            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|max:255|unique:users,email',
                'phone'    => 'nullable|string|max:20',
                'address'  => 'nullable|string|max:255',
                'id_br'    => [$requiresBranch ? 'required' : 'nullable', 'exists:branches,id_br'],
                'id_role'  => 'required|exists:roles,id_role',
                'password' => 'required|string|min:6|confirmed',
                'status'   => 'required|in:0,1',
            ], [
                'name.required'      => 'Tên nhân viên là bắt buộc',
                'email.required'     => 'Email là bắt buộc',
                'email.email'        => 'Email không đúng định dạng',
                'email.unique'       => 'Email đã tồn tại',
                'password.required'  => 'Mật khẩu là bắt buộc',
                'password.min'       => 'Mật khẩu tối thiểu 6 ký tự',
                'password.confirmed' => 'Mật khẩu xác nhận không khớp',
                'id_br.required'     => 'Chức vụ này bắt buộc phải chọn chi nhánh',
                'id_br.exists'       => 'Chi nhánh không hợp lệ',
                'id_role.required'   => 'Vui lòng chọn chức vụ',
                'id_role.exists'     => 'Chức vụ không hợp lệ',
                'status.required'    => 'Vui lòng chọn trạng thái',
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'address'  => $request->address,
                'id_br'    => $request->id_br ?: null,
                'id_role'  => $request->id_role,
                'password' => Hash::make($request->password),
                'status'   => $request->status,
            ]);

            if ($user) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Tạo nhân viên thành công!',
                    'redirect' => route('admin.user.create'),
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
        $user = User::with(['branch', 'order'])->findOrFail($id);
        return view('admin.pages.user_show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $branches = Branch::orderBy('name')->get();
        $roles = Role::all();
        return view('admin.pages.user_edit', compact('user', 'branches', 'roles'));
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $requiresBranch = $this->roleRequiresBranch($request->id_role);

            $rules = [
                'name'    => 'required|string|max:255',
                'email'   => 'required|email|max:255|unique:users,email,' . $id . ',id_user',
                'phone'   => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'id_br'   => [$requiresBranch ? 'required' : 'nullable', 'exists:branches,id_br'],
                'id_role' => 'required|exists:roles,id_role',
                'status'  => 'required|in:0,1',
            ];

            if ($request->filled('password')) {
                $rules['password'] = 'nullable|string|min:6|confirmed';
            }

            $request->validate($rules, [
                'name.required'        => 'Tên nhân viên là bắt buộc',
                'email.required'       => 'Email là bắt buộc',
                'email.email'          => 'Email không đúng định dạng',
                'email.unique'         => 'Email đã tồn tại',
                'password.min'         => 'Mật khẩu tối thiểu 6 ký tự',
                'password.confirmed'   => 'Mật khẩu xác nhận không khớp',
                'id_br.required'       => 'Chức vụ này bắt buộc phải chọn chi nhánh',
                'id_br.exists'         => 'Chi nhánh không hợp lệ',
                'id_role.required'     => 'Vui lòng chọn chức vụ',
                'id_role.exists'       => 'Chức vụ không hợp lệ',
                'status.required'      => 'Vui lòng chọn trạng thái',
            ]);

            $user->name    = $request->name;
            $user->email   = $request->email;
            $user->phone   = $request->phone;
            $user->address = $request->address;
            $user->id_br   = $request->id_br ?: null;
            $user->id_role = $request->id_role;
            $user->status  = $request->status;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật nhân viên thành công',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => false,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        }
    }

    // Toggle khóa/mở khóa nhân viên
    // status = 1: đang hoạt động; status = 0: đã khóa
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->status = $user->status == 1 ? 0 : 1;
            $user->save();

            return redirect()->route('admin.user')
                ->with('success', $user->status == 1 ? 'Đã mở khóa nhân viên' : 'Đã khóa nhân viên');
        } catch (\Exception $e) {
            return redirect()->route('admin.user')->with('error', 'Có lỗi xảy ra');
        }
    }

    // Xóa nhân viên - chỉ cho phép khi chưa có bất kỳ lịch sử nào
    public function destroy($id)
    {
        try {
            $user = User::withCount(['order', 'importReceipts', 'exportReceipts', 'stockCheckReceipts', 'transfers as transfers_count'])->findOrFail($id);

            $totalHistory = $user->order_count
                + $user->import_receipts_count
                + $user->export_receipts_count
                + $user->stock_check_receipts_count
                + $user->transfers_count;

            if ($totalHistory > 0) {
                return redirect()->route('admin.user')
                    ->with('error', 'Không thể xóa nhân viên đã có lịch sử hoạt động');
            }

            $user->delete();

            return redirect()->route('admin.user')
                ->with('success', 'Đã xóa nhân viên thành công');
        } catch (\Exception $e) {
            return redirect()->route('admin.user')->with('error', 'Có lỗi xảy ra');
        }
    }
}
