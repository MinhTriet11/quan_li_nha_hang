<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_detail';
    public $timestamps = false;
    protected $primaryKey = 'id_ordetail';

    protected $fillable = [
        'id_ordetail',
        'id_order',
        'id_food',
        'quantity',
        'price',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'id_food', 'id_food');
    }
}
