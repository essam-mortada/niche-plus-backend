<?php

namespace App\Http\Controllers;

use App\Models\Nomination;
use Illuminate\Http\Request;

class NominationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:awards,id',
            'category' => 'required|string',
            'nominee' => 'required|string',
            'instagram' => 'nullable|string'
        ]);

        $nomination = Nomination::create([
            ...$validated,
            'submitted_by' => auth()->id()
        ]);

        return response()->json($nomination, 201);
    }

    public function index()
    {
        $nominations = Nomination::with(['award', 'user'])
            ->where('submitted_by', auth()->id())
            ->get();

        return response()->json($nominations);
    }
}
