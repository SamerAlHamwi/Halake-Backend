<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonCategories extends Model
{
    use HasFactory;
    public $table = "salon_categories";

    public function services()
    {
        return $this->hasMany(Services::class, 'category_id', 'id');
    }
}
