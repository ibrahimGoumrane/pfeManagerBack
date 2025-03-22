<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Tag::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:tags',
        ],[
            'name.required' => 'The tag name is required.',
            'name.unique' => 'The tag name is already taken.'
        ]);

        $tag = Tag::create($validated);

        return response()->json($tag);
    }


    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        return $tag;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:tags',
        ],[
            'name.required' => 'The tag name is required.',
            'name.unique' => 'The tag name is already taken.'
        ]);

        $tag->update($validated);

        return response()->json($tag);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        Gate::authorize('delete');
        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully'
        ]);
    }
}
