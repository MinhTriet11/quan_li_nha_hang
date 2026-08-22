<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    public $timestamps = false;
    protected $primaryKey = 'id_order';

    protected $fillable = [
        'id_order',
        'id_br',
        'id_user',
        'id_table',
        'id_cus',
        'order_time',
        'status',
        'note'
    ];

    public function table()
    {
        return $this->belongsTo(Table::class, 'id_table', 'id_table');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'id_br', 'id_br');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_cus', 'id_cus');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id_order', 'id_order');
    }
}
