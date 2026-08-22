<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Flasher\Prime\Notification\NotificationInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token){
        return view('client.pages.auth', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], 
        [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Vui lòng nhập email hợp lệ',
            'password.required' => 'Vui lòng nhập password',
            'password.min' => 'Mật khẩu phải trên 6 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'token.required' => 'Mã không hợp lệ hoặc đã hết hạn'
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if($status === Password::PASSWORD_RESET){
            return response()->json([
                'status' => 'success',
                'message' => 'Mật khẩu đã được đặt lại thành công',
                'redirect' => route('auth')
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Đường dẫn đặt lại mật khẩu không hợp lệ hoặc đã hết hạn'
        ], 400);
    }
}