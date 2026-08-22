<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_br';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_br',
        'name',
        'address',
        'status',
        'image',
        'location'
    ];
}
