<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCheckDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_detail';
    protected $primaryKey = 'id_sto_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_stock',
        'id_ingre',
        'system_quantity',
        'actual_quantity',
        'note',
    ];

    protected $casts = [
        'actual_quantity' => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(StockCheckReceipt::class, 'id_stock', 'id_stock');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'id_ingre', 'id_ingre');
    }
}