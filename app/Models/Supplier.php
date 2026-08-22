<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';
    protected $primaryKey = 'id_su';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'status'
    ];

    public function importReceipts()
    {
        return $this->hasMany(ImportDetail::class, 'id_su');
    }
}