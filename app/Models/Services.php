<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;
    public $table = "salon_services";

    public function images()
    {
        return $this->hasMany(ServiceImages::class, 'service_id', 'id');
    }
    public function salon()
    {
        return $this->hasOne(Salons::class, 'id', 'salon_id');
    }
    public function category()
    {
        return $this->hasOne(SalonCategories::class, 'id', 'category_id');
    }
}
