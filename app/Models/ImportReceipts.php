<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportReceipts extends Model
{
    use HasFactory;

    protected $table = 'import_receipts';
    protected $primaryKey = 'id_im';
    public $timestamps = true;

    protected $fillable = [
        'id_br',
        'id_user',
        'status',
    ];

    public function details()
    {
        return $this->hasMany(ImportDetail::class, 'id_im');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'id_br', 'id_br');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    

    // Tổng tiền phiếu: sum(quantity * price) các detail
    public function getTotalAttribute()
    {
        return $this->details->sum(function ($detail) {
            return $detail->quantity * $detail->price;
        });
    }
}
