# Plan: Kiểm kê tồn kho

## Context

Trang quản lý tồn kho hiện chỉ cho xem snapshot `branch_ingredient.quantity` — không có cơ chế đối chiếu số tồn thực tế với hệ thống, không có audit trail khi phát hiện hao hụt/thừa. Cần thêm luồng **kiểm kê** để nhân viên kho:

1. Tạo phiếu kiểm kê cho 1 chi nhánh → render danh sách nguyên liệu của chi nhánh đó.
2. Nhập **số lượng thực tế** từng nguyên liệu (mặc định điền sẵn = tồn hệ thống, người dùng sửa).
3. Lưu phiếu ở trạng thái **chờ duyệt** (status=0).
4. Khi duyệt → cập nhật trực tiếp `branch_ingredient.quantity = actual_quantity` cho từng dòng có chênh lệch (không sinh phiếu nhập/xuất phụ).
5. Lưu lịch sử phiếu kiểm kê để tra cứu sau.

Quyết định đã chốt với user:
- Quy trình: **Tạo phiếu → Duyệt → áp vào tồn kho** (giống mẫu Import/Export Receipt).
- Cách cập nhật: **Update trực tiếp** `branch_ingredient.quantity` (không sinh phiếu phụ).
- Có lưu **lịch sử phiếu kiểm kê**.

## Phạm vi thay đổi

### 1. Migration mới (database/migrations)

Tạo 2 bảng:

**`stock_check_receipts`** — header phiếu kiểm kê
- `id_sc` (PK, bigint, auto)
- `id_br` (FK → branches.id_br)
- `id_user` (FK → users.id_user, nullable — người tạo)
- `note` (text, nullable)
- `status` (tinyint: 0=chờ duyệt, 1=đã duyệt, 2=từ chối)
- `approved_by` (FK → users.id_user, nullable)
- `approved_at` (timestamp, nullable)
- `created_at`, `updated_at`

**`stock_check_details`** — dòng chi tiết từng nguyên liệu trong phiếu
- `id_sc_detail` (PK, bigint, auto)
- `id_sc` (FK → stock_check_receipts.id_sc, cascade on delete)
- `id_ingre` (FK → ingredients.id_ingre)
- `system_quantity` (decimal(12,2) — tồn hệ thống lúc tạo phiếu, snapshot)
- `actual_quantity` (decimal(12,2) — số thực tế nhập vào)
- `difference` (decimal(12,2), generated: actual - system) — dùng `decimal` thuần, set trong controller, không cần DB generated column để đơn giản.

Index: `(id_sc)`, `(id_br, created_at)` để tra cứu nhanh.

### 2. Models mới (app/Models)

- **`StockCheckReceipt`** — table `stock_check_receipts`, fillable + relations:
  - `details()` hasMany StockCheckDetail
  - `branch()` belongsTo Branch
  - `user()` belongsTo User (id_user)
  - `approver()` belongsTo User (approved_by)
- **`StockCheckDetail`** — table `stock_check_details`, fillable + relations:
  - `receipt()` belongsTo StockCheckReceipt (id_sc)
  - `ingredient()` belongsTo Ingredient (id_ingre)

### 3. Controller mới (app/Http/Controllers/Admin/StockCheckController.php)

Tham khảo pattern từ `ImportReceiptController.php` (validate, transaction, toastr/JSON response).

**`index(Request)`** — danh sách phiếu:
- Lọc: `branch_id`, `status`, `search` (theo id_sc).
- `paginate(10)->withQueryString()`.
- Trả view `admin.pages.stock_check_index` với `receipts`, `branches`.

**`create(Request)`** — form tạo phiếu:
- Load `branches` (status=1).
- Nếu query có `?branch_id=`: load danh sách `BranchIngredient::with('ingredient')->where('id_br', $branchId)->get()` → truyền sang view để render sẵn bảng nhập.
- Trả view `admin.pages.stock_check_create` với `branches`, `preselectedBranchId`, `stocks` (nullable).

**`store(Request)`** — lưu phiếu mới:
- Validate:
  - `id_br` required, exists
  - `details` array, min 1
  - `details.*.id_ingre` exists
  - `details.*.actual_quantity` numeric, min 0
- DB transaction:
  - Tạo `StockCheckReceipt` (status=0, id_user=Auth::id()).
  - Snapshot `system_quantity = BranchIngredient::where(id_br,id_ingre)->value('quantity')` cho mỗi dòng (nếu chưa có row thì system=0).
  - Tạo `StockCheckDetail` cho từng dòng.
- Trả JSON success/redirect → `admin.stock_check.index`.

**`show(string $id)`** — xem chi tiết phiếu:
- Load `receipt` với details.ingredient, branch, user, approver.
- Trả view `admin.pages.stock_check_show`.

**`approve(string $id)`** — duyệt phiếu:
- DB transaction:
  - Load phiếu + details.
  - Nếu đã duyệt → throw.
  - Với mỗi detail: cập nhật `branch_ingredient.quantity = actual_quantity` (nếu chênh, dùng `updateOrCreate` để xử lý trường hợp chi nhánh chưa có row nguyên liệu đó — system_quantity=0).
  - Set `status=1`, `approved_by=Auth::id()`, `approved_at=now()`.
- Trả JSON success.

**`reject(string $id)`** — từ chối:
- Set status=2, lưu.

**`destroy(string $id)`** — xóa (chỉ khi status≠1):
- DB transaction: xóa details + receipt.

### 4. Routes (routes/web.php)

Trong nhóm `auth:admin` middleware (xem `routes/web.php:104-271`):

```
Route::get   '/admin/stock-checks'                  → index   (admin.stock_check.index)
Route::get   '/admin/stock-checks/create'           → create  (admin.stock_check.create)
Route::post  '/admin/stock-checks'                  → store   (admin.stock_check.store)
Route::get   '/admin/stock-checks/{id}'             → show    (admin.stock_check.show)
Route::post  '/admin/stock-checks/{id}/approve'     → approve (admin.stock_check.approve)
Route::post  '/admin/stock-checks/{id}/reject'      → reject  (admin.stock_check.reject)
Route::delete'/admin/stock-checks/{id}'             → destroy (admin.stock_check.destroy)
```

### 5. Views mới (resources/views/admin/pages)

Tham khảo cấu trúc `inventory_stock.blade.php` (extends layout, topbar, card shadow, badge) và `import_receipt_create.blade.php` (form chọn + bảng dynamic).

**`stock_check_index.blade.php`**
- Header: nút "Tạo phiếu kiểm kê".
- Filter: chi nhánh + trạng thái + search id_sc.
- Bảng: STT, Mã phiếu, Chi nhánh, Người tạo, Số dòng, Trạng thái (badge), Ngày tạo, Thao tác (Xem / Duyệt / Xóa).
- Pagination.

**`stock_check_create.blade.php`**
- Form chọn chi nhánh đầu trang. Submit bằng GET để reload + render bảng nguyên liệu (giống pattern filter ở `inventory_stock.blade.php`).
- Khi đã chọn chi nhánh → bảng nguyên liệu:
  - Cột: STT, Tên nguyên liệu, Đơn vị, **Tồn hệ thống** (read-only), **Số lượng thực tế** (input number, mặc định = tồn hệ thống), **Chênh lệch** (read-only, JS tính = actual - system, đổi màu đỏ nếu âm, xanh nếu dương, xám nếu 0).
  - Hidden input `id_ingre`, `system_quantity`.
- Nút "Lưu phiếu (chờ duyệt)" submit POST.

**`stock_check_show.blade.php`**
- Header: thông tin phiếu (mã, chi nhánh, người tạo, ngày, trạng thái, người duyệt nếu có).
- Bảng chi tiết: Tên, Đơn vị, Tồn hệ thống, Thực tế, Chênh lệch (màu).
- Tổng kết: tổng dòng chênh âm, tổng dòng chênh dương, tổng chênh lệch.
- Nếu status=0: nút Duyệt / Từ chối / Xóa.
- Nếu status≠0: read-only.

### 6. Link từ trang tồn kho

Trong `resources/views/admin/pages/inventory_stock.blade.php`, cập nhật cụm nút bên phải header (đoạn 18-25) — thêm nút "Kiểm kê" cạnh nút "Tạo phiếu chuyển kho":

```html
<a href="{{ route('admin.stock_check.create') }}" class="btn btn-sm btn-warning shadow-sm">
    <i class="fas fa-clipboard-check fa-sm text-white-50"></i> Kiểm kê
</a>
```

## Files sẽ tạo / sửa

**Tạo mới:**
- `database/migrations/2026_08_07_100000_create_stock_check_receipts_table.php`
- `database/migrations/2026_08_07_100001_create_stock_check_details_table.php`
- `app/Models/StockCheckReceipt.php`
- `app/Models/StockCheckDetail.php`
- `app/Http/Controllers/Admin/StockCheckController.php`
- `resources/views/admin/pages/stock_check_index.blade.php`
- `resources/views/admin/pages/stock_check_create.blade.php`
- `resources/views/admin/pages/stock_check_show.blade.php`

**Sửa:**
- `routes/web.php` — thêm 7 route trong nhóm `auth:admin`.
- `resources/views/admin/pages/inventory_stock.blade.php` — thêm 1 nút "Kiểm kê" (~3 dòng).

## Tái sử dụng

- Pattern validate + transaction + JSON response: `ImportReceiptController.php` (xem `store()` dòng 55-115, `approve()` dòng 251-294).
- Pattern select2 + filter GET: `inventory_stock.blade.php` (xem form filter dòng 82-115).
- Pattern card + table responsive: `inventory_stock.blade.php` (dòng 119-184).
- `Branch`, `Ingredient`, `BranchIngredient` models: dùng nguyên xi, không sửa.
- `Auth::id()` cho `id_user` và `approved_by` (xem `ImportReceiptController.php:83`).

## Lưu ý kỹ thuật

1. **Snapshot `system_quantity`**: lưu tại thời điểm tạo phiếu để dù có người nhập/xuất trong lúc chờ duyệt thì chênh lệch vẫn đúng so với lúc kiểm. Khi duyệt, dùng `actual_quantity` ghi đè trực tiếp `branch_ingredient.quantity` (không cộng/trừ — vì user nhập số tuyệt đối).

2. **Edge case nguyên liệu chưa có trong `branch_ingredient`**: nếu chi nhánh chưa từng nhập nguyên liệu X, `BranchIngredient` chưa có row → snapshot `system_quantity=0`, khi duyệt dùng `updateOrCreate(['id_br'=>$id_br,'id_ingre'=>$id_ingre], ['quantity'=>$actual])`.

3. **Cho phép `actual_quantity` nhập dưới dạng 0**: validate `min:0` (không âm). Số âm không hợp lệ về mặt vật lý.

4. **Quyền duyệt**: tạm thời bất kỳ admin đăng nhập đều duyệt được. Nếu sau này cần phân quyền "thủ kho" vs "quản lý", có thể thêm middleware `role:warehouse` — không nằm trong scope hiện tại.

5. **Không xóa phiếu đã duyệt**: để bảo toàn audit. Controller `destroy` check `status == 1` → throw.

## Verification

1. **Migration**: chạy `php artisan migrate` trong `C:/xampp/htdocs/quanlynhahang/` — kiểm tra 2 bảng mới tồn tại.
2. **Route list**: `php artisan route:list --name=stock_check` — 7 route xuất hiện.
3. **Tạo phiếu end-to-end** (qua UI):
   - Vào `/admin/stock-checks/create`.
   - Chọn chi nhánh A → bảng render nguyên liệu, ô thực tế điền sẵn = tồn.
   - Sửa 2 dòng: 1 dòng giảm 5 đơn vị, 1 dòng tăng 3 đơn vị → chênh lệch đỏ/xanh.
   - Bấm Lưu → toastr success, chuyển sang index, phiếu mới hiển thị status=chờ duyệt.
4. **Duyệt phiếu**:
   - Click Xem → bảng chi tiết khớp số đã nhập.
   - Click Duyệt → trong DB check `branch_ingredient` của chi nhánh A: 2 dòng đúng = actual_quantity.
   - Reload `/admin/inventory-stock?branch_id=A` → số tồn mới khớp với đã nhập.
5. **Từ chối**: tạo phiếu khác → Từ chối → `status=2`, tồn kho không đổi.
6. **Xóa phiếu chờ duyệt**: OK. Thử xóa phiếu đã duyệt → báo lỗi.
7. **Edge case**: chọn chi nhánh B chưa có nguyên liệu X → tạo phiếu nhập actual=10 → duyệt → `branch_ingredient` có row mới quantity=10.

## Ngoài phạm vi (không làm bây giờ)

- Phân quyền duyệt (thủ kho vs quản lý).
- Export Excel/PDF phiếu kiểm kê.
- Cho phép chỉnh sửa dòng sau khi lưu (hiện tại chỉ xóa + tạo lại).
- Thống kê lịch sử chênh lệch theo tháng.