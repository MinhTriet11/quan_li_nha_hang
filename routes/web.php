<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBookController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Clients\AuthController;
use App\Http\Controllers\Clients\BookController;
use App\Http\Controllers\Clients\BranchController;
use App\Http\Controllers\Clients\ForgotPassWordController;
use App\Http\Controllers\Clients\HomeController;
use App\Http\Controllers\Clients\MenuItemController as ClientMenuItemController;
use App\Http\Controllers\Clients\ProfileController;
use App\Http\Controllers\Clients\ResetPasswordController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ImportReceiptController;
use App\Http\Controllers\Admin\ExportReceiptController;
use App\Http\Controllers\Admin\InventoryDashboardController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\StockCheckController;
use App\Http\Controllers\Admin\InventoryForecastController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('client.home');

Route::get('/home', [HomeController::class, 'index'])->name('client.home');

Route::get('/branches', [BranchController::class, 'index'])->name('client.branches');

Route::middleware('guest:customer')->group(function () {
    Route::get('/auth', [AuthController::class, 'showLoginForm'])->name('auth');

    Route::post('/auth-register', [AuthController::class, 'register'])->name('register');

    Route::post('/auth-login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth:customer')->group(function () {
    Route::get('/auth-logout', [AuthController::class, 'logout'])->name('logout');
    // profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

    Route::put('/profile-update', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    // cancel booking
    Route::post('/profile/bookings/{id}/cancel', [ProfileController::class, 'cancelBooking'])->name('profile.booking.cancel');

    // order history
});


// Route::get('/auth-logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/forgot-password', [ForgotPassWordController::class, 'resetPasswordLink'])->name('password.request');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');

Route::post('/reset-password/{token}', [ResetPasswordController::class, 'resetPassword'])->name('password.update');

Route::get('/menu-items/{id_cate}', [ClientMenuItemController::class, 'getByCategory']);

//lay table theo branchId
Route::get('/book/table-type/{branchId}', [BookController::class, 'getTableType'])->name('booking');

//Lay ban trong theo ngay gio
Route::get('/book/available-tables', [BookController::class, 'getAvailableTables']);

//Lay cac khung gio da bi chiem trong ngay (de disable option trong dropdown)
Route::get('/book/booked-slots', [BookController::class, 'getBookedSlots']);



//booking
Route::post('/reservation', [BookController::class, 'reserVation'])->name('reservation');


//=========================================ADMIN============================================

Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login-form', [AdminAuthController::class, 'showLoginAdmin'])->name('admin.login-form');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');
});



Route::middleware(['auth:admin', 'role'])->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    //tao dat ban
    Route::get('/admin/books/available-tables', [AdminBookController::class, 'availableTables'])->name('admin.book.availableTables');
    Route::get('/admin/books', [AdminBookController::class, 'index'])->name('admin.book');
    Route::get('/admin/books/create', [AdminBookController::class, 'create'])->name('admin.book.create');

    Route::post('/admin/books/confirm/{id}', [AdminBookController::class, 'confirm'])->name('admin.book.confirm');
    Route::post('/admin/books/cancel/{id}', [AdminBookController::class, 'cancel'])->name('admin.book.cancel');


    Route::post('/admin/books', [AdminBookController::class, 'store'])->name('admin.book.store');
    
    Route::get('/admin/books/{id}', [AdminBookController::class, 'show'])->name('admin.book.show');
    Route::get('/admin/books/{id}/edit', [AdminBookController::class, 'edit'])->name('admin.book.edit');
    Route::put('/admin/books/{id}', [AdminBookController::class, 'update'])->name('admin.book.update');
    Route::delete('/admin/books/{id}', [AdminBookController::class, 'destroy'])->name('admin.book.destroy');

    // Quan ly ban
    Route::get('/admin/tables', [TableController::class, 'index'])->name('admin.table');
    Route::get('/admin/tables/create', [TableController::class, 'create'])->name('admin.table.create');
    Route::post('/admin/tables', [TableController::class, 'store'])->name('admin.table.store');
    Route::get('/admin/tables/{id}', [TableController::class, 'show'])->name('admin.table.show');
    Route::get('/admin/tables/{id}/edit', [TableController::class, 'edit'])->name('admin.table.edit');
    Route::put('/admin/tables/{id}', [TableController::class, 'update'])->name('admin.table.update');
    Route::delete('/admin/tables/{id}', [TableController::class, 'destroy'])->name('admin.table.destroy');

    // Table Types routes
    Route::get('/admin/table-types', [TableController::class, 'tableTypeIndex'])->name('admin.tableType');
    Route::get('/admin/table-types/create', [TableController::class, 'tableTypeCreate'])->name('admin.tableType.create');
    Route::post('/admin/table-types', [TableController::class, 'tableTypeStore'])->name('admin.tableType.store');
    Route::get('/admin/table-types/{id}/edit', [TableController::class, 'tableTypeEdit'])->name('admin.tableType.edit');
    Route::put('/admin/table-types/{id}', [TableController::class, 'tableTypeUpdate'])->name('admin.tableType.update');
    Route::delete('/admin/table-types/{id}', [TableController::class, 'tableTypeDestroy'])->name('admin.tableType.destroy');

    // Branch routes
    Route::get('/admin/branches', [AdminBranchController::class, 'index'])->name('admin.branch');
    Route::get('/admin/branches/create', [AdminBranchController::class, 'create'])->name('admin.branch.create');
    Route::post('/admin/branches', [AdminBranchController::class, 'store'])->name('admin.branch.store');
    Route::get('/admin/branches/{id}', [AdminBranchController::class, 'show'])->name('admin.branch.show');
    Route::get('/admin/branches/{id}/edit', [AdminBranchController::class, 'edit'])->name('admin.branch.edit');
    Route::put('/admin/branches/{id}', [AdminBranchController::class, 'update'])->name('admin.branch.update');
    Route::delete('/admin/branches/{id}', [AdminBranchController::class, 'destroy'])->name('admin.branch.destroy');

    // Tra cứu khách hàng theo SĐT (phục vụ modal thanh toán)
    Route::get('/admin/customers/lookup', [OrderController::class, 'lookupCustomer'])->name('admin.customer.lookup');

    // Customer routes
    Route::get('/admin/customers', [CustomerController::class, 'index'])->name('admin.customer');
    Route::get('/admin/customers/create', [CustomerController::class, 'create'])->name('admin.customer.create');
    Route::post('/admin/customers', [CustomerController::class, 'store'])->name('admin.customer.store');
    Route::get('/admin/customers/{id}', [CustomerController::class, 'show'])->name('admin.customer.show');
    Route::get('/admin/customers/{id}/edit', [CustomerController::class, 'edit'])->name('admin.customer.edit');
    Route::put('/admin/customers/{id}', [CustomerController::class, 'update'])->name('admin.customer.update');
    Route::post('/admin/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('admin.customer.toggleStatus');
    Route::delete('/admin/customers/{id}', [CustomerController::class, 'destroy'])->name('admin.customer.destroy');

    // User (nhân viên) routes
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.user');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.user.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.user.store');
    Route::get('/admin/users/{id}', [UserController::class, 'show'])->name('admin.user.show');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.user.update');
    Route::post('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.user.toggleStatus');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

    // Category routes
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('admin.category');
    Route::get('/admin/categories/create', [CategoryController::class, 'create'])->name('admin.category.create');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->name('admin.category.store');
    Route::get('/admin/categories/{id}/edit', [CategoryController::class, 'edit'])->name('admin.category.edit');
    Route::put('/admin/categories/{id}', [CategoryController::class, 'update'])->name('admin.category.update');
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy'])->name('admin.category.destroy');

    // Menu Items routes
    Route::get('/admin/menu-items', [MenuItemController::class, 'index'])->name('admin.menuItem');
    Route::get('/admin/menu-items/create', [MenuItemController::class, 'create'])->name('admin.menuItem.create');
    Route::post('/admin/menu-items', [MenuItemController::class, 'store'])->name('admin.menuItem.store');
    Route::get('/admin/menu-items/{id}', [MenuItemController::class, 'show'])->name('admin.menuItem.show');
    Route::get('/admin/menu-items/{id}/edit', [MenuItemController::class, 'edit'])->name('admin.menuItem.edit');
    Route::put('/admin/menu-items/{id}', [MenuItemController::class, 'update'])->name('admin.menuItem.update');
    Route::delete('/admin/menu-items/{id}', [MenuItemController::class, 'destroy'])->name('admin.menuItem.destroy');

    // Order - Quản lý order theo bàn
    Route::get('/admin/orders/tables', [OrderController::class, 'index'])->name('admin.order.tables');
    Route::get('/admin/orders/management', [OrderController::class, 'orderManagement'])->name('admin.order.management');
    Route::get('/admin/orders/menu-items', [OrderController::class, 'getMenuItems'])->name('admin.order.menuItems');
    Route::post('/admin/orders/create', [OrderController::class, 'createOrder'])->name('admin.order.create');

    Route::get('/admin/orders/available-tables', [OrderController::class, 'getAvailableTables'])->name('admin.order.availableTables');
    
    Route::get('/admin/orders/{tableId}', [OrderController::class, 'showOrder'])->name('admin.order.show');
    Route::put('/admin/orders/{orderId}/status', [OrderController::class, 'updateOrderStatus'])->name('admin.order.updateStatus');
    Route::post('/admin/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder'])->name('admin.order.cancel');
    Route::post('/admin/orders/{orderId}/complete', [OrderController::class, 'completeOrder'])->name('admin.order.complete');
    Route::post('/admin/orders/add-item', [OrderController::class, 'addItemToOrder'])->name('admin.order.addItem');
    Route::put('/admin/orders/item/{itemId}', [OrderController::class, 'updateOrderItem'])->name('admin.order.updateItem');
    Route::post('/admin/orders/transfer', [OrderController::class, 'transferTable'])->name('admin.order.transfer');
    Route::post('/admin/orders/merge', [OrderController::class, 'mergeTable'])->name('admin.order.merge');
    Route::post('/admin/orders/payment', [OrderController::class, 'payment'])->name('admin.order.payment');
    // Đánh dấu hóa đơn chưa thanh toán (khách ăn xong bỏ đi)
    Route::post('/admin/orders/{orderId}/mark-unpaid', [OrderController::class, 'markUnpaid'])->name('admin.order.markUnpaid');


    Route::get('/admin/orders/table-info/{tableId}', [OrderController::class, 'getTableInfo'])->name('admin.order.tableInfo');

    // Kitchen - Bếp xem danh sách order
    Route::get('/admin/kitchen', [OrderController::class, 'kitchen'])->name('admin.kitchen');
    Route::put('/admin/kitchen/order/{orderId}/item/{itemId}/status', [OrderController::class, 'updateKitchenItemStatus'])->name('admin.kitchen.updateItemStatus');

    // Ingredient - Nguyên liệu
    Route::get('/admin/ingredient', [IngredientController::class , 'index'])->name('admin.ingredient');
    Route::get('/admin/ingredient/create', [IngredientController::class , 'create'])->name('admin.ingredient.create');
    Route::post('/admin/ingredient', [IngredientController::class , 'store'])->name('admin.ingredient.store');
    Route::get('/admin/ingredient/{id}/edit', [IngredientController::class , 'edit'])->name('admin.ingredient.edit');
    Route::put('/admin/ingredient/{id}', [IngredientController::class, 'update'])->name('admin.ingredient.update');
    Route::delete('/admin/ingredient/{id}', [IngredientController::class, 'destroy'])->name('admin.ingredient.destroy');

    Route::get('/admin/ingredient/get-unit/{id}', [IngredientController::class, 'getUnit']);

    // Supplier - Nhà cung cấp
    Route::get('/admin/suppliers', [SupplierController::class, 'index'])->name('admin.supplier');
    Route::get('/admin/suppliers/create', [SupplierController::class, 'create'])->name('admin.supplier.create');
    Route::post('/admin/suppliers', [SupplierController::class, 'store'])->name('admin.supplier.store');
    Route::get('/admin/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('admin.supplier.edit');
    Route::put('/admin/suppliers/{id}', [SupplierController::class, 'update'])->name('admin.supplier.update');
    Route::delete('/admin/suppliers/{id}', [SupplierController::class, 'destroy'])->name('admin.supplier.destroy');

    // Inventory Dashboard - Tổng quan tồn kho
    // Route::get('/admin/inventory-dashboard', [InventoryDashboardController::class, 'index'])->name('admin.inventory.dashboard');
    Route::get('/admin/inventory-stock', [InventoryDashboardController::class, 'stock'])->name('admin.inventory.stock');

    // Inventory Forecast - Dự báo nhập kho
    Route::get('/admin/inventory-forecast', [InventoryForecastController::class, 'index'])->name('admin.inventory.forecast');

    // Import Receipts - Nhập kho
    Route::get('/admin/import-receipts', [ImportReceiptController::class, 'index'])->name('admin.import.index');
    Route::get('/admin/import-receipts/create', [ImportReceiptController::class, 'create'])->name('admin.import.create');
    Route::post('/admin/import-receipts', [ImportReceiptController::class, 'store'])->name('admin.import.store');
    Route::get('/admin/import-receipts/{id}', [ImportReceiptController::class, 'show'])->name('admin.import.show');
    Route::post('/admin/import-receipts/{id}/approve', [ImportReceiptController::class, 'approve'])->name('admin.import.approve');
    Route::post('/admin/import-receipts/{id}/reject', [ImportReceiptController::class, 'reject'])->name('admin.import.reject');
    Route::delete('/admin/import-receipts/{id}', [ImportReceiptController::class, 'destroy'])->name('admin.import.destroy');
    Route::post('/admin/import-receipts/{id}/details', [ImportReceiptController::class, 'addDetail'])->name('admin.import.detail.store');
    Route::put('/admin/import-receipts/{id}/details/{detailId}', [ImportReceiptController::class, 'updateDetail'])->name('admin.import.detail.update');
    Route::delete('/admin/import-receipts/{id}/details/{detailId}', [ImportReceiptController::class, 'deleteDetail'])->name('admin.import.detail.destroy');

    // Export Receipts - Xuất kho (gồm cả phiếu auto khi bếp chế biến và phiếu thủ công hư/đổ/điều chỉnh)
    Route::get('/admin/export-receipts', [ExportReceiptController::class, 'index'])->name('admin.export.index');
    Route::get('/admin/export-receipts/create', [ExportReceiptController::class, 'create'])->name('admin.export.create');
    Route::get('/admin/export-receipts/branch-ingredients/{id_br}', [ExportReceiptController::class, 'getBranchIngredients'])->name('admin.export.branchIngredients');
    Route::post('/admin/export-receipts', [ExportReceiptController::class, 'store'])->name('admin.export.store');
    Route::get('/admin/export-receipts/{id}', [ExportReceiptController::class, 'show'])->name('admin.export.show');
    Route::post('/admin/export-receipts/{id}/approve', [ExportReceiptController::class, 'approve'])->name('admin.export.approve');
    Route::post('/admin/export-receipts/{id}/reject', [ExportReceiptController::class, 'reject'])->name('admin.export.reject');
    Route::delete('/admin/export-receipts/{id}', [ExportReceiptController::class, 'destroy'])->name('admin.export.destroy');
    Route::post('/admin/export-receipts/{id}/details', [ExportReceiptController::class, 'addDetail'])->name('admin.export.detail.store');
    Route::put('/admin/export-receipts/{id}/details/{detailId}', [ExportReceiptController::class, 'updateDetail'])->name('admin.export.detail.update');
    Route::delete('/admin/export-receipts/{id}/details/{detailId}', [ExportReceiptController::class, 'deleteDetail'])->name('admin.export.detail.destroy');

    // Recipe
    Route::post('/admin/recipe', [RecipeController::class, 'store'])->name('admin.recipe.store');
    Route::delete('/admin/recipe/{id}', [RecipeController::class, 'destroy'])->name('admin.recipe.destroy');
    Route::put('/admin/recipe/item/{itemId}', [RecipeController::class, 'update'])->name('admin.recipe.updateItem');

    // Transfer - Chuyển kho giữa các chi nhánh
    Route::get('/admin/transfers', [TransferController::class, 'index'])->name('admin.transfer.index');
    Route::get('/admin/transfers/create', [TransferController::class, 'create'])->name('admin.transfer.create');
    Route::get('/admin/transfer-receipts/branch/{id_br}', [TransferController::class, 'getBranchTo'])->name('admin.transfer.getBranchTo');
    Route::post('/admin/transfers', [TransferController::class, 'store'])->name('admin.transfer.store');
    Route::get('/admin/transfers/{id}', [TransferController::class, 'show'])->name('admin.transfer.show');
    Route::post('/admin/transfers/{id}/approve', [TransferController::class, 'approve'])->name('admin.transfer.approve');
    Route::post('/admin/transfers/{id}/reject', [TransferController::class, 'reject'])->name('admin.transfer.reject');
    Route::delete('/admin/transfers/{id}', [TransferController::class, 'destroy'])->name('admin.transfer.destroy');
    Route::post('/admin/transfers/{id}/details', [TransferController::class, 'addDetail'])->name('admin.transfer.detail.store');
    Route::put('/admin/transfers/{id}/details/{detailId}', [TransferController::class, 'updateDetail'])->name('admin.transfer.detail.update');
    Route::delete('/admin/transfers/{id}/details/{detailId}', [TransferController::class, 'deleteDetail'])->name('admin.transfer.detail.destroy');

    // Báo cáo
    Route::get('/admin/reports',[ReportController::class, 'index'])->name('admin.report.index');

    // Stock Check - Kiểm kê tồn kho
    Route::get   ('/admin/stock-checks', [StockCheckController::class, 'index'])->name('admin.stock_check.index');
    Route::get   ('/admin/stock-checks/create', [StockCheckController::class, 'create'])->name('admin.stock_check.create');
    Route::post  ('/admin/stock-checks', [StockCheckController::class, 'store'])->name('admin.stock_check.store');
    Route::get   ('/admin/stock-checks/{id}', [StockCheckController::class, 'show'])->name('admin.stock_check.show');
    Route::post  ('/admin/stock-checks/{id}/approve',[StockCheckController::class, 'approve'])->name('admin.stock_check.approve');
    Route::post  ('/admin/stock-checks/{id}/reject', [StockCheckController::class, 'reject'])->name('admin.stock_check.reject');
    Route::delete('/admin/stock-checks/{id}',     [StockCheckController::class, 'destroy'])->name('admin.stock_check.destroy');

    // Stock Check - Chi tiết (chỉ cho phép sửa số lượng khi phiếu còn chờ duyệt)
    Route::put('/admin/stock-checks/{id}/details/{detailId}', [StockCheckController::class, 'updateDetail'])->name('admin.stock_check.detail.update');

    // Hóa đơn
    Route::get('/admin/bills',[BillController::class, 'index'])->name('admin.bill.index');
    Route::get('/admin/bills/{id}',[BillController::class, 'show'])->name('admin.bill.show');
    Route::get('/admin/bills/{id}/print',[BillController::class, 'print'])->name('admin.bill.print');

});
