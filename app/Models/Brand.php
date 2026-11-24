<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    public function getConnectionName()
    {
        return config('tenancy.database.central_connection', 'mysql');
    }

    protected $fillable = ['name', 'slug','photo', 'status','is_popular'];
    public $timestamps = false;

    public function items()
    {
        return $this->hasMany('App\Models\Item');
    }

}
