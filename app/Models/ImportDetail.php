<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportDetail extends Model
{
    use HasFactory;

    protected $table = 'import_detail';
    protected $primaryKey = 'id_import_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_im',
        'id_ingre',
        'id_su',
        'quantity',
        'price'
    ];

    public function supplier(){
        return $this->belongsTo(Supplier::class, 'id_su');
    }

    public function ingredient(){
        return $this->belongsTo(Ingredient::class, 'id_ingre');
    }
}
