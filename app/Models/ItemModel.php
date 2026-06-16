<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use illuminate\Database\eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class ItemModel extends Model implements JWTSubject
{
    protected $table = 'tbl_items';
    protected $fillable = ['name', 'code', 'price','updated','created'];

     public function getJWTIdentifier()
    {
        return $this->getKey(); // biasanya ID user
    }

    /**
     * Tambahkan klaim (claims) tambahan jika diperlukan.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
