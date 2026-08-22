<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransDetail extends Model
{
    use HasFactory;

    protected $table = 'tran_detail';
    protected $primaryKey = 'id_tran_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_tran',
        'id_ingre',
        'quantity',
    ];

    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'id_tran', 'id_tran');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'id_ingre', 'id_ingre');
    }
}
