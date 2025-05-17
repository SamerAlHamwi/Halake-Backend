<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salons extends Model
{
    use HasFactory;
    public $table = "salon";
    // protected $hidden = [
    //     'password',
    // ];

    public function avgRating()
    {
        return $this->reviews->avg('rating');
    }

    public function images()
    {
        return $this->hasMany(SalonImages::class, 'salon_id', 'id');
    }
    public function slots()
    {
        return $this->hasMany(SalonBookingSlots::class, 'salon_id', 'id');
    }
    public function staff()
    {
        return $this->hasMany(Staff::class, 'salon_id', 'id');
    }
    public function services()
    {
        return $this->hasMany(Services::class, 'salon_id', 'id');
    }
    public function awards()
    {
        return $this->hasMany(SalonAwards::class, 'salon_id', 'id');
    }
    public function gallery()
    {
        return $this->hasMany(SalonGallery::class, 'salon_id', 'id');
    }
    public function reviews()
    {
        return $this->hasMany(SalonReviews::class, 'salon_id', 'id');
    }
    public function bankAccount()
    {
        return $this->hasOne(SalonBankAccounts::class, 'salon_id', 'id');
    }
}
