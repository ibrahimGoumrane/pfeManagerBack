<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    //
    public  function users(){
        return $this->hasMany(User::class);
    }
}
