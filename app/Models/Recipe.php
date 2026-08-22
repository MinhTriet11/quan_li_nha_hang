<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $table = 'menu_item_ingre';
    protected $primaryKey = 'id_mii';
    public $timestamps = false;

    protected $fillable = [
        'id_food',
        'id_ingre',
        'quantity', 
        'note'
    ];

    public function MenuItems(){
        return $this->belongsTo(MenuItem::class, 'id_food');
    }

    public function ingredient(){
        return $this->belongsTo(Ingredient::class, 'id_ingre');
    }
}
