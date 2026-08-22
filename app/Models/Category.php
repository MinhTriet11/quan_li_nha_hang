<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $primaryKey = 'id_cate';
    public $timestamps = false;

    protected $fillable = [
        'id_cate',
        'name',
        'image',
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'id_cate');
    }
}
