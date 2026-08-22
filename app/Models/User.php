<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_br',
        'id_role',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function order(){
        return $this->hasMany(Order::class, 'id_user', 'id_user');
    }

    public function importReceipts(){
        return $this->hasMany(ImportReceipts::class, 'id_user', 'id_user');
    }

    public function exportReceipts(){
        return $this->hasMany(ExportReceipts::class, 'id_user', 'id_user');
    }

    public function stockCheckReceipts(){
        return $this->hasMany(StockCheckReceipt::class, 'id_user', 'id_user');
    }

    public function transfers(){
        return $this->hasMany(Transfer::class, 'id_user', 'id_user');
    }

    // Tổng số bản ghi lịch sử liên quan tới nhân viên
    public function getHistoryCountAttribute(){
        return $this->order()->count()
            + $this->importReceipts()->count()
            + $this->exportReceipts()->count()
            + $this->stockCheckReceipts()->count()
            + $this->transfers()->count();
    }

    public function branch(){
        return $this->belongsTo(Branch::class, 'id_br', 'id_br');
    }

    public function role(){
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    // Tên chức vụ (tiếng Việt) — fallback '—' nếu chưa gán role
    public function getRoleNameAttribute(): string
    {
        return $this->role?->name ?? '—';
    }

    // Mã code chức vụ (admin, manager, staff...) — dùng cho phân quyền
    public function getRoleCodeAttribute(): string
    {
        return $this->role?->code ?? 'staff';
    }

    // Danh sách role code mà user này được phép (helper cho Gate/Middleware sau này)
    public function hasRole(string ...$codes): bool
    {
        return in_array($this->role_code, $codes, true);
    }
}
