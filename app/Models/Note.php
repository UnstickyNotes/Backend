<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
#[Fillable(['title','body','collection_id','user_id','isOrginal'])]
class Note extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function collection(){
        return $this->belongsTo(Collection::class);
    }
    public function updatedNote(){
        return $this->hasMany(UpdatedNote::class);
    }
}
