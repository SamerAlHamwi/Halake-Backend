<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonEarningHistory extends Model
{
    use HasFactory;
    public $table = "salon_earning_history";

    public function booking()
    {
        return $this->hasOne(Bookings::class, 'id', 'booking_id');
    }
}
