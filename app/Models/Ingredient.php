<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $table = 'ingredients';
    protected $primaryKey = 'id_ingre';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'unit',
        'min_stock',
        'status'
    ];

    public function recipe(){
        return $this->hasMany(Recipe::class, 'id_ingre');
    }

}
