<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use illuminate\Database\eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use illuminate\Support\Facades\Auth;

class ItemModel extends Model implements JWTSubject
{
    protected $table = 'tbl_items';
    protected $fillable = ['name', 'code', 'price','updated_by','created_by','created_at','updated_at'];

     public function getJWTIdentifier()
    {
        return $this->getKey(); // biasanya ID user

    }

    protected static function booted()
    {
        // Saat data baru mau dibuat (Insert)
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id(); // Biasanya saat dibuat, updated_by disamakan dulu
            }
        });

        // Saat data mau diubah (Update)
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
    /**
     * Tambahkan klaim (claims) tambahan jika diperlukan.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
