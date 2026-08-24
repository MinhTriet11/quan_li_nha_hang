<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

/**
 * PermissionHelper
 *
 * Bảng phân quyền theo role_code cho khu vực admin.
 *
 * Mỗi nhóm chức năng ("module") gồm danh sách route name mà role được phép truy cập.
 * Middleware \App\Http\Middleware\RolePermission sẽ đối chiếu route hiện tại với
 * bảng này để quyết định cho phép hay từ chối (HTTP 403).
 *
 * View (sidebar) dùng các hằng MODULE_* để quyết định ẩn/hiện menu tương ứng.
 */
class PermissionHelper
{
    /* ---------- Danh sách module (nhóm chức năng) ---------- */

    public const MODULE_DASHBOARD   = 'dashboard';
    public const MODULE_BOOKING     = 'booking';
    public const MODULE_MENU        = 'menu';
    public const MODULE_CUSTOMER    = 'customer';
    public const MODULE_EMPLOYEE    = 'employee';
    public const MODULE_ORDER       = 'order';
    public const MODULE_INVENTORY   = 'inventory';
    public const MODULE_REPORT      = 'report';
    public const MODULE_KITCHEN     = 'kitchen';
    public const MODULE_BRANCH      = 'branch';

    /* ---------- Route name theo module ---------- */

    /** Dashboard – mọi role đều truy cập được. */
    public const ROUTES_DASHBOARD = [
        'admin.dashboard',
    ];

    /** Quản lý đặt bàn + quản lý bàn. */
    public const ROUTES_BOOKING = [
        'admin.book',
        'admin.book.availableTables',
        'admin.book.create',
        'admin.book.confirm',
        'admin.book.cancel',
        'admin.book.store',
        'admin.book.show',
        'admin.book.edit',
        'admin.book.update',
        'admin.book.destroy',
        'admin.table',
        'admin.table.create',
        'admin.table.store',
        'admin.table.show',
        'admin.table.edit',
        'admin.table.update',
        'admin.table.destroy',
        'admin.tableType',
        'admin.tableType.create',
        'admin.tableType.store',
        'admin.tableType.edit',
        'admin.tableType.update',
        'admin.tableType.destroy',
        'admin.branch',
        'admin.branch.create',
        'admin.branch.store',
        'admin.branch.show',
        'admin.branch.edit',
        'admin.branch.update',
        'admin.branch.destroy',
    ];

    /** Quản lý thực đơn (món ăn, danh mục, recipe). */
    public const ROUTES_MENU = [
        'admin.menuItem',
        'admin.menuItem.create',
        'admin.menuItem.store',
        'admin.menuItem.show',
        'admin.menuItem.edit',
        'admin.menuItem.update',
        'admin.menuItem.destroy',
        'admin.category',
        'admin.category.create',
        'admin.category.store',
        'admin.category.edit',
        'admin.category.update',
        'admin.category.destroy',
        'admin.recipe.store',
        'admin.recipe.destroy',
        'admin.recipe.updateItem',
    ];

    /** Quản lý khách hàng + hóa đơn. */
    public const ROUTES_CUSTOMER = [
        'admin.customer',
        'admin.customer.create',
        'admin.customer.store',
        'admin.customer.show',
        'admin.customer.edit',
        'admin.customer.update',
        'admin.customer.toggleStatus',
        'admin.customer.destroy',
        'admin.customer.lookup',
        'admin.bill.index',
        'admin.bill.show',
        'admin.bill.print',
    ];

    /** Quản lý nhân viên. */
    public const ROUTES_EMPLOYEE = [
        'admin.user',
        'admin.user.create',
        'admin.user.store',
        'admin.user.show',
        'admin.user.edit',
        'admin.user.update',
        'admin.user.toggleStatus',
        'admin.user.destroy',
    ];

    /** Gọi món (order – tạo đơn, quản lý theo bàn, thanh toán). */
    public const ROUTES_ORDER = [
        'admin.order.tables',
        'admin.order.management',
        'admin.order.menuItems',
        'admin.order.create',
        'admin.order.availableTables',
        'admin.order.show',
        'admin.order.updateStatus',
        'admin.order.cancel',
        'admin.order.complete',
        'admin.order.addItem',
        'admin.order.updateItem',
        'admin.order.transfer',
        'admin.order.merge',
        'admin.order.payment',
        'admin.order.markUnpaid',
        'admin.order.tableInfo',
    ];

    /** Kho nguyên liệu (nguyên liệu, NCC, nhập/xuất/chuyển/kiểm kê, tồn kho, dự báo). */
    public const ROUTES_INVENTORY = [
        'admin.ingredient',
        'admin.ingredient.create',
        'admin.ingredient.store',
        'admin.ingredient.edit',
        'admin.ingredient.update',
        'admin.ingredient.destroy',
        // lấy đơn vị (gọi từ form nguyên liệu)
        'admin.ingredient.get-unit',
        'admin.supplier',
        'admin.supplier.create',
        'admin.supplier.store',
        'admin.supplier.edit',
        'admin.supplier.update',
        'admin.supplier.destroy',
        'admin.inventory.stock',
        'admin.inventory.forecast',
        'admin.import.index',
        'admin.import.create',
        'admin.import.store',
        'admin.import.show',
        'admin.import.approve',
        'admin.import.reject',
        'admin.import.destroy',
        'admin.import.detail.store',
        'admin.import.detail.update',
        'admin.import.detail.destroy',
        'admin.export.index',
        'admin.export.create',
        'admin.export.branchIngredients',
        'admin.export.store',
        'admin.export.show',
        'admin.export.approve',
        'admin.export.reject',
        'admin.export.destroy',
        'admin.export.detail.store',
        'admin.export.detail.update',
        'admin.export.detail.destroy',
        'admin.transfer.index',
        'admin.transfer.create',
        'admin.transfer.getBranchTo',
        'admin.transfer.store',
        'admin.transfer.show',
        'admin.transfer.approve',
        'admin.transfer.reject',
        'admin.transfer.destroy',
        'admin.transfer.detail.store',
        'admin.transfer.detail.update',
        'admin.transfer.detail.destroy',
        'admin.stock_check.index',
        'admin.stock_check.create',
        'admin.stock_check.store',
        'admin.stock_check.show',
        'admin.stock_check.approve',
        'admin.stock_check.reject',
        'admin.stock_check.destroy',
        'admin.stock_check.detail.update',
    ];

    /** Báo cáo (tổng quan doanh thu). */
    public const ROUTES_REPORT = [
        'admin.report.index',
    ];

    /** Bếp – xem order, cập nhật trạng thái món. */
    public const ROUTES_KITCHEN = [
        'admin.kitchen',
        'admin.kitchen.updateItemStatus',
    ];

    /** Bếp – xem order, cập nhật trạng thái món. */
    public const ROUTES_BRANCH = [
        'admin.branch',
        'admin.branch.create',
        'admin.branch.store',
        'admin.branch.edit',
        'admin.branch.update',
        'admin.branch.destroy',
        'admin.branch.create',
        'admin.branch.show',
    ];

    /* ---------- Bảng phân quyền theo role_code ---------- */

    /**
     * Mỗi role là một mảng các module được phép truy cập.
     * 'admin' thấy tất cả; các role khác giới hạn theo chức năng.
     */
    public const ROLE_PERMISSIONS = [
        // Admin: tất cả các module
        'admin' => [
            self::MODULE_DASHBOARD,
            self::MODULE_BOOKING,
            self::MODULE_MENU,
            self::MODULE_CUSTOMER,
            self::MODULE_EMPLOYEE,
            self::MODULE_ORDER,
            self::MODULE_INVENTORY,
            self::MODULE_REPORT,
            self::MODULE_KITCHEN,
            self::MODULE_BRANCH,
        ],
        // Quản lý: dashboard + đặt bàn + thực đơn + khách hàng + nhân viên + gọi món + kho + báo cáo
        'manager' => [
            self::MODULE_DASHBOARD,
            self::MODULE_BOOKING,
            self::MODULE_MENU,
            self::MODULE_CUSTOMER,
            self::MODULE_EMPLOYEE,
            self::MODULE_ORDER,
        ],
        // Nhân viên phục vụ: dashboard + đặt bàn + khách hàng + gọi món
        'staff' => [
            self::MODULE_DASHBOARD,
            self::MODULE_BOOKING,
            self::MODULE_CUSTOMER,
            self::MODULE_ORDER,
        ],
        // Nhân viên bếp: dashboard + xem order (kitchen)
        'chef' => [
            self::MODULE_DASHBOARD,
            self::MODULE_KITCHEN,
        ],
        // Nhân viên kho: dashboard + kho nguyên liệu
        'warehouse' => [
            self::MODULE_DASHBOARD,
            self::MODULE_INVENTORY,
        ],
        // Thu ngân: dashboard + khách hàng + order (thanh toán) + báo cáo
        'cashier' => [
            self::MODULE_DASHBOARD,
            self::MODULE_CUSTOMER,
            self::MODULE_ORDER,
            self::MODULE_REPORT,
        ],
        // Kế toán: dashboard + báo cáo
        'accountant' => [
            self::MODULE_DASHBOARD,
            self::MODULE_REPORT,
        ],
    ];

    /**
     * Ánh xạ id_role (FK trong bảng users) -> role_code dùng cho phân quyền.
     * Dựa trên dữ liệu hiện có của bảng roles (chỉ có cột id_role và name).
     */
    public const ROLE_ID_TO_CODE = [
        1 => 'admin',
        2 => 'manager',
        3 => 'staff',
        4 => 'chef',
        5 => 'warehouse',
        6 => 'staff',      // trùng với "Nhân viên phục vụ" nếu DB có thêm
        7 => 'warehouse',  // trùng với "Nhân viên kho" nếu DB có thêm
    ];

    /**
     * Lấy role_code của admin hiện đang đăng nhập.
     * Ưu tiên:
     *   1) role->code (nếu bảng roles có cột code)
     *   2) Ánh xạ id_role -> code
     *   3) Mapping theo name (phòng hờ khi DB khác)
     * Mặc định 'staff' nếu không xác định được.
     */
    public static function currentRoleCode(): string
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return 'staff';
        }

        // 1) Nếu bảng roles có cột code thì ưu tiên dùng
        $code = $user->role?->code;
        if (!empty($code)) {
            return (string) $code;
        }

        // 2) Map theo id_role
        $idRole = (int) ($user->id_role ?? 0);
        if ($idRole > 0 && isset(self::ROLE_ID_TO_CODE[$idRole])) {
            return self::ROLE_ID_TO_CODE[$idRole];
        }

        // 3) Map theo name (fallback cuối)
        $name = mb_strtolower($user->role?->name ?? '');
        return match (true) {
            str_contains($name, 'admin') || str_contains($name, 'quản trị') => 'admin',
            str_contains($name, 'quản lý')  => 'manager',
            str_contains($name, 'bếp')      => 'chef',
            str_contains($name, 'kho')      => 'warehouse',
            str_contains($name, 'kế toán')  => 'accountant',
            str_contains($name, 'thu ngân') => 'cashier',
            str_contains($name, 'phục vụ') || str_contains($name, 'nhân viên') => 'staff',
            default => 'staff',
        };
    }

    /**
     * Lấy danh sách module mà role hiện tại được phép.
     */
    public static function allowedModules(): array
    {
        $role = self::currentRoleCode();
        return self::ROLE_PERMISSIONS[$role] ?? [];
    }

    /**
     * Kiểm tra role hiện tại có được phép truy cập module hay không.
     */
    public static function canAccess(string $module): bool
    {
        return in_array($module, self::allowedModules(), true);
    }

    /**
     * Đối chiếu một route name với bảng phân quyền.
     * Trả về true nếu route được phép với role hiện tại.
     */
    public static function canAccessRoute(string $routeName): bool
    {
        $allowed = self::allowedModules();
        // Admin luôn luôn được phép mọi route
        if (in_array('*', $allowed, true)) {
            return true;
        }
        foreach (self::routeNameToModules() as $module => $routes) {
            if (in_array($module, $allowed, true) && in_array($routeName, $routes, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ánh xạ module -> danh sách route name (dùng cho việc kiểm tra route).
     */
    public static function routeNameToModules(): array
    {
        return [
            self::MODULE_DASHBOARD => self::ROUTES_DASHBOARD,
            self::MODULE_BOOKING   => self::ROUTES_BOOKING,
            self::MODULE_MENU      => self::ROUTES_MENU,
            self::MODULE_CUSTOMER  => self::ROUTES_CUSTOMER,
            self::MODULE_EMPLOYEE  => self::ROUTES_EMPLOYEE,
            self::MODULE_ORDER     => self::ROUTES_ORDER,
            self::MODULE_INVENTORY => self::ROUTES_INVENTORY,
            self::MODULE_REPORT    => self::ROUTES_REPORT,
            self::MODULE_KITCHEN   => self::ROUTES_KITCHEN,
            self::MODULE_BRANCH    => self::ROUTES_BRANCH,
        ];
    }
}