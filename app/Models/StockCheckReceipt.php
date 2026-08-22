<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCheckReceipt extends Model
{
    use HasFactory;

    protected $table = 'stocktakes';
    protected $primaryKey = 'id_stock';

    protected $fillable = [
        'id_br',
        'id_user',
        'note',
        'status',
    ];

    public function details()
    {
        return $this->hasMany(StockCheckDetail::class, 'id_stock', 'id_stock');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'id_br', 'id_br');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id_user');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => 'Chờ duyệt',
            1 => 'Đã duyệt',
            2 => 'Từ chối',
            default => '—',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            0 => 'warning',
            1 => 'success',
            2 => 'danger',
            default => 'secondary',
        };
    }
}