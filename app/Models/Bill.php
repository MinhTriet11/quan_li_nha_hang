<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $table = 'bill';
    public $timestamps = false;
    protected $primaryKey = 'id_bill';

    protected $fillable = [
        'id_bill',
        'id_order',
        'total',
        'discount_percent',
        'discount_amount',
        'pay_method',
        'pay_time',
        'status'
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'id_order');
    }

}
