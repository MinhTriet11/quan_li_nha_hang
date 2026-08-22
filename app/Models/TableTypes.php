<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableTypes extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $fillable = [
        'id',
        'name',
        'description',
        'price_per_hour',
    ];

    public function tables()
    {
        return $this->hasMany(Table::class, 'table_type_id', 'id');
    }

    public function books()
    {
        return $this->hasMany(Books::class, 'id_table_type');
    }
}
