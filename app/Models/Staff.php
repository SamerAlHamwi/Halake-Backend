<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;
    public $table = "salon_staff";
    public function salon()
    {
        return $this->hasOne(Salons::class, 'id', 'salon_id');
    }
    public function bookings()
    {
        return $this->hasMany(Bookings::class, 'staff_id', 'id');
    }
}
