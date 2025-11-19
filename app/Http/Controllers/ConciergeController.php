<?php

namespace App\Http\Controllers;

use App\Models\Concierge;
use Illuminate\Http\Request;

class ConciergeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:Dining,PR,Introductions,Venues,Photoshoots',
            'message' => 'required|string',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'preferred_date' => 'nullable|string',
            'preferred_time' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'number_of_people' => 'nullable|integer|min:1',
            'budget' => 'nullable|numeric|min:0',
            'special_requirements' => 'nullable|string'
        ]);

        $concierge = Concierge::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'message' => $validated['message'],
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
            'company_name' => $validated['company_name'] ?? null,
            'preferred_date' => $validated['preferred_date'] ?? null,
            'preferred_time' => $validated['preferred_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'number_of_people' => $validated['number_of_people'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'special_requirements' => $validated['special_requirements'] ?? null,
            'status' => 'pending'
        ]);

        return response()->json($concierge, 201);
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $requests = Concierge::with('user')->orderBy('created_at', 'desc')->get();
        } else {
            $requests = Concierge::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        }

        return response()->json($requests);
    }

    public function show($id)
    {
        $concierge = Concierge::with('user')->findOrFail($id);

        // Users can only see their own requests, admins can see all
        if (auth()->user()->role !== 'admin' && $concierge->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($concierge);
    }

    public function update(Request $request, $id)
    {
        $concierge = Concierge::findOrFail($id);

        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'admin_notes' => 'nullable|string'
        ]);

        $concierge->update($validated);
        return response()->json($concierge);
    }
}
