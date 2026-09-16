<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Note;
use DateTime;
use Illuminate\Http\Request;

class SyncController extends Controller {
    public function pull($last_synced_at, $user_id){
        $user = Auth()->user();
        if ($user->id !== $user_id){
            return response()->error('Unauthorized', 403);
        }
        $last_synced_at_user = new DateTime($last_synced_at);
        $last_synced_at_server = new DateTime($user->last_synced_at);
        if($last_synced_at_user == $last_synced_at_server){
            return response()->success([],'Up to date', 200);
        }

        $cols = Collection::where('user_id', $user_id)->get();
        $notes = Note::where('user_id', $user_id)->get();
    }

    public function push(Request $request){

    }
}