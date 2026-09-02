<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
#[Fillable(['originalNote_id','updatedNote_id'])]
class UpdatedNote extends Model
{
    public function note(){
        return $this->belongsTo(Note::class);
    }
}
