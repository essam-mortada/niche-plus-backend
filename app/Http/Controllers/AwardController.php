<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Traits\NormalizesFilePaths;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    use NormalizesFilePaths;

    public function index()
    {
        $awards = Award::orderBy('date', 'asc')->get();
        return response()->json($awards);
    }

    public function show($id)
    {
        $award = Award::with('nominations')->findOrFail($id);
        return response()->json($award);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string',
            'venue' => 'required|string',
            'date' => 'required|date',
            'description' => 'required|string',
            'image' => 'nullable|string',
            'ticket_link' => 'nullable|url'
        ]);

        // Normalize image path
        if (isset($validated['image'])) {
            $validated['image'] = $this->normalizePath($validated['image']);
        }

        $award = Award::create($validated);
        return response()->json($award, 201);
    }

    public function update(Request $request, $id)
    {
        $award = Award::findOrFail($id);
        
        $validated = $request->validate([
            'city' => 'required|string',
            'venue' => 'required|string',
            'date' => 'required|date',
            'description' => 'required|string',
            'image' => 'nullable|string',
            'ticket_link' => 'nullable|url'
        ]);
        
        // Normalize image path
        if (isset($validated['image'])) {
            $validated['image'] = $this->normalizePath($validated['image']);
        }
        
        $award->update($validated);
        return response()->json($award);
    }

    public function destroy($id)
    {
        Award::findOrFail($id)->delete();
        return response()->json(['message' => 'Award deleted']);
    }
}
