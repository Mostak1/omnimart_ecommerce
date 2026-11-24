<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function getConnectionName()
    {
        return config('tenancy.database.central_connection', 'mysql');
    }

    protected $fillable = ['name','slug', 'photo','status','is_feature','meta_keywords','meta_descriptions','serial'];
    public $timestamps = false;

    public function items()
    {
        return $this->hasMany('App\Models\Item');
    }

    public function subcategory()
    {
        return $this->hasMany('App\Models\Subcategory');
    }

}
