<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonReviews extends Model
{
    use HasFactory;
    public $table = "salon_reviews";

    public function salon()
    {
        return $this->hasOne(Salons::class, 'id', 'salon_id');
    }
    public function user()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }
    public function booking()
    {
        return $this->hasOne(Bookings::class, 'id', 'booking_id');
    }
}
