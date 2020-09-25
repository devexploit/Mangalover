<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SeriesCategories extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function serie() {
        return $this->belongsTo('App\Models\Serie');
    }
    public function category(){
        return $this->belongsTo('App\Models\Category');

    }
}
