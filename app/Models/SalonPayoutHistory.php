<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonPayoutHistory extends Model
{
    use HasFactory;
    public $table = "salon_payouts";

    public function salon()
    {
        return $this->hasOne(Salons::class, 'id', 'salon_id');
    }
}
