<?php

namespace App\Http\Controllers;

use App\Models\EventPackage;
use Illuminate\Http\Request;

class EventPackageController extends Controller
{
    public function index(Request $request)
    {
        $query = EventPackage::with('award');

        // Filter by award if provided
        if ($request->has('award_id')) {
            $query->where('award_id', $request->award_id);
        }

        $packages = $query->orderBy('created_at', 'desc')->get();
        return response()->json($packages);
    }

    public function show($id)
    {
        $package = EventPackage::with('award')->findOrFail($id);
        return response()->json($package);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'award_id' => 'required|exists:awards,id',
            'package_type' => 'required|in:nomination,majesty,sovereign,monarch',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'benefits' => 'nullable|array',
            'is_available' => 'boolean',
        ]);

        $package = EventPackage::create($validated);
        return response()->json($package->load('award'), 201);
    }

    public function update(Request $request, $id)
    {
        $package = EventPackage::findOrFail($id);

        $validated = $request->validate([
            'award_id' => 'sometimes|exists:awards,id',
            'package_type' => 'sometimes|in:nomination,majesty,sovereign,monarch',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'benefits' => 'nullable|array',
            'is_available' => 'boolean',
        ]);

        $package->update($validated);
        return response()->json($package->load('award'));
    }

    public function destroy($id)
    {
        $package = EventPackage::findOrFail($id);
        $package->delete();
        return response()->json(['message' => 'Package deleted successfully']);
    }
}
