<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    use HasFactory;
    public $table = "bookings";

    public function salon()
    {
        return $this->hasOne(Salons::class, 'id', 'salon_id');
    }
    public function user()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }
    public function review()
    {
        return $this->hasOne(SalonReviews::class, 'booking_id', 'id');
    }
    public function staff()
    {
        return $this->hasOne(Staff::class, 'id', 'staff_id');
    }
    public function service_address()
    {
        return $this->hasOne(UserAddress::class, 'id', 'address_id');
    }
}
