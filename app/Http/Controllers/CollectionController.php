<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    // helper function
    private static function generateUniqueName(?string $basename, $userId)
    {
        $regx = '^'.preg_quote($basename, '/').'(-[0-9]+)?$';
        $existingNames = Collection::where('user_id', $userId)
            ->where('name', 'LIKE', $basename.'%')
            ->pluck('name')
            ->filter(function ($name) use ($regx) {
                return preg_match('/'.$regx.'/', $name);
            })
            ->toArray();
        if (count($existingNames) == 0) {
            return $basename;
        }

        $usedNums = [];
        foreach ($existingNames as $name) {
            if (preg_match('/^'.preg_quote($basename, '/').'-([0-9]+)$/', $name, $match)) {
                $usedNums[] = (int) $match[1];
            }
        }

        $counter = 1;
        while (in_array($counter, $usedNums)) {
            $counter++;
        }

        return "{$basename}-{$counter}";
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $collections = Auth()->user()->collections;

        return response()->success($collections, 'User collections', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);
        $validated['user_id'] = $request->user()->id;
        $validated['name'] = CollectionController::generateUniqueName($validated['name'], $request->user()->id);

        $collection = Collection::create($validated);

        return response()->success($collection->only(['id', 'name', 'user_id', 'created_at']), 'Collection created', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user_id = $request->user()->id;
        $col = Collection::find($id);
        if (! $col) {
            return response()->error('Collection not found', 404);
        }
        if ($col->user_id != $user_id) {
            return response()->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string',
        ]);
        if (! isset($validated['name']) || $validated['name'] == '') {
            return response()->success($validated, 'Nothing changed', 200);
        }
        $existingCollection = Collection::where('user_id', $user_id)->where('name', $validated['name'])->get();
        if (count($existingCollection) > 1) {
            $validated['name'] = CollectionController::generateUniqueName($validated['name'], $request->user()->id);
        }
        $col->update($validated);

        return response()->success($col, 'Updated', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user_id = Auth()->user()->id;
        $col = Collection::find($id);
        if (! $col) {
            return response()->error('Collection not found', 404);
        }
        if ($col->user_id != $user_id) {
            return response()->error('Unauthorized', 403);
        }

        $col->delete();

        return response()->success($col, 'Deleted', 200);
    }
}
