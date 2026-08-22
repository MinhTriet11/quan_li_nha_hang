<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RolePermission
 *
 * Middleware kiểm tra quyền truy cập route dựa trên role_code của admin đang đăng nhập.
 * Sử dụng bảng phân quyền trong App\Helpers\PermissionHelper.
 *
 * Cách dùng trong routes/web.php:
 *   Route::middleware(['auth:admin', 'role'])->group(...);
 *
 * Khi user không đủ quyền, trả về HTTP 403 + view admin.errors.403
 * (nếu là request AJAX/JSON thì trả về JSON 403).
 */
class RolePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            // Middleware 'auth:admin' đã chạy trước đó, nhưng vẫn phòng hờ
            return redirect()->route('admin.login-form');
        }

        $routeName = $request->route()?->getName();

        // Nếu route không đặt tên (một số route fallback) thì cho qua,
        // vì lúc đó không có cách đối chiếu. Bảo mật vẫn đảm bảo bởi auth:admin.
        if (!$routeName) {
            return $next($request);
        }

        // Logout luôn luôn được phép
        if ($routeName === 'admin.logout') {
            return $next($request);
        }

        if (PermissionHelper::canAccessRoute($routeName)) {
            return $next($request);
        }

        // Không đủ quyền
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error'   => true,
                'message' => 'Bạn không có quyền truy cập chức năng này.',
            ], 403);
        }

        return response()->view('admin.errors.403', [
            'message' => 'Bạn không có quyền truy cập chức năng này.',
        ], 403);
    }
}
