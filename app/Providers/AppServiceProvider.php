<?php

namespace App\Providers;

use App\Helpers\PermissionHelper;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Chia sẻ biến phân quyền cho toàn bộ view trong resources/views/admin
        // để sidebar/header có thể ẩn/hiện menu theo role của admin đang đăng nhập.
        View::composer('admin.*', function ($view) {
            $view->with('authAdmin', Auth::guard('admin')->user());
            $view->with('allowedModules', PermissionHelper::allowedModules());
            $view->with('canAccess', function (string $module): bool {
                return PermissionHelper::canAccess($module);
            });
        });
    }
}
