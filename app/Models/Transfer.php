<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'transfer_receipts';
    protected $primaryKey = 'id_tran';
    public $timestamps = true;

    protected $fillable = [
        'from_branch',
        'to_branch',
        'id_user',
        'status',
    ];

    public function details()
    {
        return $this->hasMany(TransDetail::class, 'id_tran', 'id_tran');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch', 'id_br');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch', 'id_br');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Trạng thái phiếu chuyển kho.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ((int) $this->status) {
            0       => 'Chờ duyệt',
            1       => 'Đã duyệt',
            2       => 'Từ chối',
            default => '—',
        };
    }
}
