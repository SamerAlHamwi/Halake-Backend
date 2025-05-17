<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformEarningHistory extends Model
{
    use HasFactory;
    public $table = "platform_earning_history";

    public function booking()
    {
        return $this->hasOne(Bookings::class, 'id', 'booking_id');
    }
    public function salon()
    {
        return $this->hasOne(Salons::class, 'id', 'salon_id');
    }
}
