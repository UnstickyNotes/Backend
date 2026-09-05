<?php

namespace App\Http\Controllers;

use App\Http\Resources\NoteResource;
use App\Models\Collection;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Auth()->user()->notes;

        return response()->success($notes, 'User Notes', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:50',
            'body' => 'nullable|string',
            'collection_id' => 'integer|nullable',
        ]);

        if (! isset($validated['title']) && ! isset($validated['body'])) {
            return response()->error('You have to provide either title or body.', 422);
        }
        $validated['user_id'] = $request->user()->id;
        $col = Collection::where('id', $validated['collection_id'] ?? -1)
            ->where('user_id', $validated['user_id'])
            ->first();
        if (! $col) {
            $validated['collection_id'] = null;
        }
        $note = NoteResource::make(Note::create($validated));

        return response()->success($note, 'Note created', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user_id = Auth()->user()->id;
        $note = Note::where('id', $id)->where('user_id', $user_id)->first();
        if (! $note) {
            return response()->error('Unauthorized', 403);
        }

        return response()->success($note, 'Go nuts', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user_id = $request->user()->id;
        $note = Note::where('id', $id)->where('user_id', $user_id)->first();
        if (! $note) {
            return response()->error('Unauthorized', 403);
        }
        $validated = $request->validate([
            'title' => 'nullable|string|max:50',
            'body' => 'nullable|string',
            'collection_id' => 'integer|nullable',
        ]);

        if (! isset($validated['title']) && ! isset($validated['body']) && ! isset($validated['collection_id'])) {
            return response()->success('Nothing changed', 200);
        }

        $col = Collection::where('id', $validated['collection_id'] ?? -1)
            ->where('user_id', $user_id)
            ->first();
        if (! $col) {
            $validated['collection_id'] = null;
        }

        $note->update($validated);

        return response()->success(NoteResource::make($note), 'Note Updated', 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user_id = Auth()->user()->id;
        $note = Note::where('id', $id)->where('user_id', $user_id)->first();
        if (! $note) {
            return response()->error('Unauthorized', 403);
        }
        $note->delete();

        return response()->success(NoteResource::make($note), 'Note deleted', 200);
    }
}
