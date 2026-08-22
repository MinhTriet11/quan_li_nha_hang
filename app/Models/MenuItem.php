<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;
    protected $table = 'menu_items';
    public $timestamps = false;
    protected $primaryKey = 'id_food';

    protected $fillable = [
        'id_food',
        'name',
        'description',
        'price',
        'status',
        'id_cate',
        'image',
    ];

    public function category(){
        return $this->belongsTo(Category::class, 'id_cate');
    }

    public function recipe(){
        return $this->hasMany(Recipe::class, 'id_food');
    }
}
