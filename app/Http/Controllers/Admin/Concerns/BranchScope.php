<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * BranchScope
 *
 * Trait dùng cho các controller admin để tự động xác định chi nhánh hiện tại
 * dựa trên `id_br` của admin đang đăng nhập.
 *
 * Quy tắc:
 *  - Nếu user admin có `id_br` được gán → coi như scope cố định vào chi nhánh đó
 *    (các query sẽ tự động lọc theo, các dropdown chi nhánh sẽ bị ẩn).
 *  - Nếu user không có `id_br` (admin tổng / chưa gán) → được chọn tự do chi nhánh
 *    qua request `branch_id`; mặc định là chi nhánh đầu tiên nếu không truyền.
 */
trait BranchScope
{
    /**
     * Trả về id_br "cố định" của admin đang đăng nhập (nếu có), ngược lại null.
     * Khi khác null, user bị khóa cứng vào chi nhánh này.
     */
    protected function userBranchId(): ?int
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return null;
        }
        $id = $user->id_br ?? null;
        return $id ? (int) $id : null;
    }

    /**
     * Xác định id_br thực sự dùng cho query:
     *  - User có id_br cố định → trả về id_br đó (bỏ qua request).
     *  - User không có id_br    → lấy từ request('branch_id') hoặc fallback chi nhánh đầu tiên.
     *
     * @return int|null null nếu DB chưa có chi nhánh nào
     */
    protected function currentBranchId(Request $request): ?int
    {
        $locked = $this->userBranchId();
        if ($locked) {
            return $locked;
        }

        if ($request->filled('branch_id')) {
            return (int) $request->branch_id;
        }

        return Branch::orderBy('id_br')->value('id_br');
    }

    /**
     * Lấy danh sách chi nhánh để hiển thị lên dropdown lọc.
     * Nếu user đã bị khóa cứng vào 1 chi nhánh → trả về collection chỉ có 1 phần tử.
     */
    protected function availableBranches()
    {
        $locked = $this->userBranchId();
        if ($locked) {
            return Branch::where('id_br', $locked)->orderBy('name')->get();
        }
        return Branch::orderBy('name')->get();
    }

    /**
     * Kiểm tra dropdown chọn chi nhánh có nên hiển thị hay không.
     * Ẩn khi user bị khóa cứng vào 1 chi nhánh.
     */
    protected function showBranchSelector(): bool
    {
        return $this->userBranchId() === null;
    }

    /**
     * Áp dụng scope id_br cho Eloquent Builder nếu user bị khóa cứng.
     * Dùng cho query đã tự filter id_br sẵn, chỉ cần ép thêm điều kiện nếu bị khóa.
     */
    protected function applyBranchScope(Builder $query, Request $request, string $column = 'id_br'): Builder
    {
        $locked = $this->userBranchId();
        if ($locked) {
            return $query->where($column, $locked);
        }
        if ($request->filled('branch_id')) {
            return $query->where($column, (int) $request->branch_id);
        }
        return $query;
    }
}
