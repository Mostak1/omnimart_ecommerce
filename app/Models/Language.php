<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public function getConnectionName()
    {
        return config('tenancy.database.central_connection', 'mysql');
    }

    public $timestamps = false;
    protected $fillable = ['name','file','language','rtl','is_default','type'];
}
