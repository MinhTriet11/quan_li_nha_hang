<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_table';
    public $timestamps = false;

    protected $fillable = [
        'id_table',
        'id_br',
        'table_name',
        'status',
        'table_type_id'
    ];

    public function TableTypes(){
        return $this->belongsTo(TableTypes::class, 'table_type_id', 'id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class, 'id_br', 'id_br');
    }

    public function orders(){
        return $this->hasMany(Order::class, 'id_table', 'id_table');
    }

    public function currentOrder(){
        return $this->hasOne(Order::class, 'id_table', 'id_table')->whereIn('status', [0, 1]);
    }
}

