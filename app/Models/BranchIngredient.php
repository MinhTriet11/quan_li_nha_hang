<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchIngredient extends Model
{
    use HasFactory;

    protected $table = 'branch_ingredient';
    public $timestamps = false;
    protected $primaryKey = 'id_br_inger';

    protected $fillable = [
        'id_br',
        'id_ingre',
        'quantity',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'id_br', 'id_br');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'id_ingre', 'id_ingre');
    }
}
