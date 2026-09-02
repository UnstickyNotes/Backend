<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name','password','isProtected','user_id'])]
class Collection extends Model
{
    public function note(){
        return $this->hasMany(Note::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
