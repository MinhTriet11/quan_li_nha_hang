<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    use HasFactory;

    protected $table = 'books';
    public $timestamps = false;
    protected $primaryKey = 'id_book';

    protected $fillable = [
        'id_book',
        'id_cus',
        'id_table',
        'id_br',
        'id_table_type',
        'guest_name',
        'guest_phone',
        'guest_email',
        'time',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_cus', 'id_cus');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'id_table', 'id_table');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'id_br', 'id_br');
    }

    public function tableType()
    {
        return $this->belongsTo(TableTypes::class, 'id_table_type', 'id');
    }
}
