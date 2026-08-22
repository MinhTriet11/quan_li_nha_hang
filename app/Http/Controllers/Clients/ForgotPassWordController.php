<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPassWordController extends Controller
{
    public function resetPasswordLink(Request $request){
        try {
            $request->validate(
                [
                    'email' => 'required|email|exists:customers,email',
                ],
                [
                    'email.required' => 'Vui lòng nhập email',
                    'email.email' => 'Vui lòng nhập email hợp lệ',
                    'email.exists' => 'Email này không tồn tại trong hệ thống',
                ]
            );

            $status = Password::broker('customers')->sendResetLink($request->only('email'));


            if($status === Password::RESET_LINK_SENT){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Vui lòng kiểm tra email của bạn'
                ]);
            }
            return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể gửi email',
                    'error' => __($status)
                ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first('email'),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }
}
