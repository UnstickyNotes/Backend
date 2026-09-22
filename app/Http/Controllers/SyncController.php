<?php

namespace App\Http\Controllers;

use \Illuminate\Database\Eloquent\Casts\Json;
use App\Models\Collection;
use App\Models\Note;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SyncController extends Controller {
    private static function handleCreate(&$q){
        $payload = Json::decode($q->payload);

        if($q->entity_type === 'collection'){
            $col = Collection::create([
                'local_id' => $q->entity_id,
                'user_id' => $q->user_id,
                'name' => $payload->name
            ])->first(['id', 'local_id', 'user_id']);
            if ($col) return $col;
            else return null;
        }
        else {
            $note = Note::create([
                'local_id' => $q->entity_id,
                'user_id' => $q->user_id,
                'title' => $payload->title ?? null,
                'body' => $payload->body ?? null,
                'collection_id' => $payload->collection_id ?? null
            ]);
            if ($note) return $note;
            else return null;
        }
    }
    private static function handleUpdate(&$q){
        $payload = json::decode($q->payload);

        if($q->entity_type === 'collection'){
            $col = Collection::where('user_id', $q->user_id)
                    ->where('local_id', $q->entity_id)
                    ->first(['id', 'local_id', 'user_id']);
            if($col)
                $col->update($payload);
            else 
                $col = null;
            return $col;
        }
        else{
            $note = Note::where('user_id', $q->user_id)
                    ->where('local_id', $q->entity_id)
                    ->first(['id', 'local_id', 'user_id']);
            if($note)
                $note->update($payload);
            else 
                $note = null;
            return $note;
        }
    }
    private static function handleDelete(&$q){
        if($q->entity_type === 'collection'){
            $col = Collection::where('user_id', $q->user_id)
                    ->where('local_id', $q->entity_id)
                    ->first(['id', 'local_id', 'user_id']);
            if($col)
                $col->delete();
            else 
                $col = null;
            return $col;
        }
        else{
            $note = Note::where('user_id', $q->user_id)
                    ->where('local_id', $q->entity_id)
                    ->first(['id', 'local_id', 'user_id']);
            if($note)
                $note->delete();
            else 
                $note = null;
            return $note;
        }
    }
    public function pull(Request $request,$user_id){
        $user = Auth()->user();
        if ($user->id != $user_id){
            return response()->error('Unauthorized', 403);
        }
        $last_synced_at = $request->query('last_synced_at');
        // $last_synced_at_user = new DateTime($last_synced_at);
        // dd($last_synced_at, $user->last_synced_at);
        if($last_synced_at == null){
            $last_synced_at_user = Carbon::createFromTimestamp(0);
        }
        else{
            $last_synced_at_user = Carbon::parse($last_synced_at);
        }

        $last_synced_at_server = Carbon::parse($user->last_synced_at ?? 'now');

        if($last_synced_at_user == $last_synced_at_server){
            return response()->success([],'Up to date', 200);
        }

        $cols = Collection::where('user_id', $user_id)
                        ->orderBy('created_at')
                        ->get()
                        ->except(['created_at','updated_at'])
                        ->collect();
        $cols = $cols->map(fn($col) => [
            'remote_id' => $col->id,
            'local_id' => $col->local_id,
            'user_id' => $col->user_id,
            'name' => $col->name
        ])->toArray();
        
        $notes = Note::where('user_id', $user_id)
                        ->orderBy('created_at')
                        ->get()
                        ->except(['created_at','updated_at'])
                        ->collect();
        $notes = $notes->map(fn($note) => [
            'remote_id' => $note->id,
            'local_id' => $note->local_id,
            'collection_id' => $note->collection_id,
            'user_id' => $note->user_id,
            'title' => $note->title,
            'body' => $note->body
        ])->toArray();
        $data = [
            'last_synced_at_server' => $user->last_synced_at,
            'collections' => Json::encode($cols),
            'notes' => Json::encode($notes)
        ];
        return response()->success($data, 'updated data', 200);
    }

    public function push(Request $request, $user_id){
        if($user_id != $request->user->id) return response()->error('Unauthorized', 403);

        $validated = $request->validate([
            'data' => 'nullable|string'
        ]);
        $queueData = Json::decode($validated['data']);
        // dd($payload);
        // if($queueData->user_id != $user_id) return response()->error('Unauthorized', 403);

        $passedOperations = [];
        $failedOperations = [];
        $unauthorizedOperations = 0;

        foreach($queueData as $q){
            if ($q->user_id != $user_id ){
                $unauthorizedOperations++;
            }
            else{
                $dbres = null;
                match ($q->action) {
                    "CREATE" => $dbres = SyncController::handleCreate($q),
                    "UPDATE" => $dbres = SyncController::handleUpdate($q),
                    "DELETE" => $dbres = SyncController::handleDelete($q),
                };
                if (!$dbres){
                    $failedOperations[] = $q;
                }
                else{
                    $dbres['type'] = $q->entity_type;
                    $passedOperations[] = $dbres;
                }
            }
        }
        $now = now()->toIso8601String();
        User::where('id', $user_id)->update(['last_synced_at' => $now]);
        $data = [
            'synced_at' => $now,
            'passed' => $passedOperations,
            'failed' => $failedOperations,
            'unauthorized' => $unauthorizedOperations
        ];
        return response()->success($data, 'push status', 200);
    }
}