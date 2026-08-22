<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Flasher\Prime\Notification\NotificationInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('client.pages.auth');
    }

    public function register(Request $request)
    {
        //check email exists
        $emailExist = Customer::where('email', $request->email)->first();

        if ($emailExist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email này đã được sử dụng'
            ]);
        }

        // Validate other fields
        $request->validate([
            'fullname' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'fullname.required' => 'Vui lòng nhập họ và tên',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Vui lòng nhập email hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        // Create new customer
        $customer = Customer::create([
            'name' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng ký thành công!'
        ]);

    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Vui lòng nhập email hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        //check login infomation
        if (Auth::guard('customer')->attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1])) {

            $customer = Auth::guard('customer')->user();
            $customer->update([
                'last_activity' => date("Y-m-d H:i:s")
            ]);

            $request->session()->regenerate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Đăng nhập thành công!',
                'redirect' => route('client.home')
            ]);
        }else{
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Đăng nhập thất bại!'
                ]
            );
        }
    }

    public function logout(Request $request){
        Auth::guard('customer')->logout();

        $request->session()->invalidate(); //xoa toan bo session
        $request->session()->regenerateToken(); //tao csrf token moi de cac form sau do su dung token moi

        toastr('Đăng xuất thành công', NotificationInterface::SUCCESS, 'Thành công');
        return redirect('/');

    }
}
