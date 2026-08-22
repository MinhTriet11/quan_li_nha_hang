<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id_role';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_role', 'id_role');
    }

    /**
     * Danh sách role mặc định dùng cho select (chỉ lấy role đang kích hoạt).
     */
    // public static function activeOptions()
    // {
    //     return self::where('status', 1)
    //         ->orderBy('id_role')
    //         ->get(['id_role', 'name']);
    // }
}
