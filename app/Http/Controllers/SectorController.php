<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Sector::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create');
        $validated = $request->validate([
            'name' => 'required|string|unique:sectors',

        ], [
            'name.required' => 'The sector name is required.',
            'name.unique' => 'The sector name is already taken.'
        ]);
        //capitalize the entire string
        $validated['name'] = strtoupper($validated['name']);

        $sector = Sector::create($validated);

        return response()->json($sector);

    }

    /**
     * Display the specified resource.
     */
    public function show(Sector $sector)
    {
        return $sector;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sector $sector)
    {
        Gate::authorize('update', $sector);
        $validated = $request->validate([
            'name' => 'required|string|unique:sectors',
        ], [
            'name.required' => 'The sector name is required.',
            'name.unique' => 'The sector name is already taken.'
        ]);
        //capitalize the entire string
        $validated['name'] = strtoupper($validated['name']);

        $sector->update($validated);

        return response()->json($sector);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sector $sector)
    {
        Gate::authorize('delete', $sector);
        $sector->delete();
        return response()->json(['message' => 'Sector deleted']);
    }
}
