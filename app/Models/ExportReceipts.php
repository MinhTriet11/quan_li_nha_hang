<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportReceipts extends Model
{
    use HasFactory;

    protected $table = 'export_receipts';
    protected $primaryKey = 'id_ex';
    public $timestamps = true;
    // DB dùng cột timestamp chuẩn created_at / updated_at — không cần khai báo const.

    protected $fillable = [
        'id_br',
        'id_user',
        'note',
        'reason',
        'source_ordetail',
        'status',
    ];

    public function details()
    {
        return $this->hasMany(ExportDetail::class, 'id_ex', 'id_ex');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'id_br', 'id_br');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Lý do xuất hiển thị tiếng Việt.
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'sale'       => 'Bán hàng (tự động)',
            'waste'      => 'Nguyên liệu hư',
            'broken'     => 'Nguyên liệu đổ/bể',
            'adjustment' => 'Điều chỉnh tồn kho',
            default      => $this->reason ?? '—',
        };
    }
}