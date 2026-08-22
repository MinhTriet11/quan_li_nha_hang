<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginAdmin(){
        return view('admin.pages.auth_admin');
    }


    public function login(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Vui lòng nhập email hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        if(Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1])){
            $request->session()->regenerate();
            // return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công!');
            return response()->json([
                'status' => 'success',
                'message' => 'Đăng nhập thành công',
                'redirect' => route('admin.dashboard')
            ]);
        }

        return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng kiểm tra lại tài khoản và mật khẩu',
            ]);
    }

    public function logout(Request $request){
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login-form')->with('success', 'Đăng xuất thành công!');
    }
}
