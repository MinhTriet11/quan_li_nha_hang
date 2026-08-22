<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $table = 'customers';
    protected $primaryKey = 'id_cus';
    public $timestamps = false;

    protected $fillable = [
        'id_cus',
        'name',
        'email',
        'password',
        'status',
        'phone',
        'address',
        'last_activity'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function Books(){
        return $this->hasMany(Books::class, 'id_cus');
    }
}


