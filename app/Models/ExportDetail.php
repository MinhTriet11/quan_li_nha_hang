<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportDetail extends Model
{
    use HasFactory;

    protected $table = 'export_detail';
    protected $primaryKey = 'id_export_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_ex',
        'id_food',
        'id_ingre',
        'quantity',
    ];

    public function exportReceipt()
    {
        return $this->belongsTo(ExportReceipts::class, 'id_ex', 'id_ex');
    }

    public function food()
    {
        return $this->belongsTo(MenuItem::class, 'id_food', 'id_food');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'id_ingre', 'id_ingre');
    }
}
