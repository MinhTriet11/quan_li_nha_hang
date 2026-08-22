<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $bookings = Books::with(['table', 'branch', 'tableType'])
            ->where('id_cus', $customer->id_cus)
            ->orderBy('time', 'desc')
            ->paginate(10)
            ->withQueryString();


        $bills = Bill::with([
            'order.table',
            'order.branch',
            'order.orderDetails.menuItem',
        ])
            ->whereHas('order', function ($q) use ($customer) {
                $q->where('id_cus', $customer->id_cus);
            })
            ->orderBy('pay_time', 'desc')
            ->get();

        return view('client.pages.profile', compact('customer', 'bookings', 'bills'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'fullname' => 'required',
            'email' => 'required|email',
            'phone' => 'required|regex:/^(0[35789])[0-9]{8}$/',
            'address' => 'required'
        ], [
            'fullname.required' => 'Vui lòng nhập tên',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Vui lòng nhập email hợp lệ',
            'address.required' => 'Vui lòng nhập địa chỉ',
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.regex' => 'Vui lòng nhập số điện thoại hợp lệ'
        ]);

        $customer = Auth::guard('customer')->user();

        $customer->name = $request->input('fullname');
        $customer->phone = $request->input('phone');
        $customer->address = $request->input('address');
        $customer->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thông tin thành công!',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate(
            [
                'old_password' => 'required|min:6',
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required'
            ],
            [
                'old_password.required' => 'Vui lòng nhập password',
                'old_password.min' => 'Mật khẩu phải trên 6 ký tự',
                'password.required' => 'Vui lòng nhập password',
                'password.min' => 'Mật khẩu phải trên 6 ký tự',
                'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            ]
        );

        $customer = Auth::guard('customer')->user();

        //check
        if (!Hash::check($request->old_password, $customer->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu hiện tại không chính xác',
            ], 422);
        } else {
            //update password
            $customer->update(['password' => Hash::make($request->password)]);

            return response()->json([
                'status' => 'success',
                'message' => 'Mật khẩu đã được đặt lại thành công',
            ]);
        }
    }


    //Hủy đặt bàn (chỉ cho phép khi status = 0 hoặc 1, tức pending/confirmed).
    public function cancelBooking(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();
        $booking = Books::where('id_book', $id)
            ->where('id_cus', $customer->id_cus)
            ->firstOrFail();

        if (!in_array((int) $booking->status, [0, 1])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể hủy đặt bàn ở trạng thái hiện tại',
            ], 422);
        }

        $booking->status = 3; // 3 = cancelled
        $booking->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Hủy đặt bàn thành công',
        ]);
    }
}
