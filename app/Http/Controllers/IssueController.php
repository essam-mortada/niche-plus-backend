<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Traits\NormalizesFilePaths;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    use NormalizesFilePaths;
    public function index()
    {
        $issues = Issue::orderBy('created_at', 'desc')->get();
        
        $user = auth()->user();
        $canAccessPremium = $user && in_array($user->tier, ['premium', 'vip']);

        return response()->json([
            'issues' => $issues,
            'can_access_premium' => $canAccessPremium
        ]);
    }

    public function show($id)
    {
        $issue = Issue::findOrFail($id);
        $user = auth()->user();

        if ($issue->premium && (!$user || !in_array($user->tier, ['premium', 'vip']))) {
            return response()->json(['error' => 'Premium membership required'], 403);
        }

        return response()->json($issue);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'issue_no' => 'required|string',
            'cover' => 'required|string',
            'pdf_url' => 'required|string',
            'premium' => 'boolean'
        ]);

        // Normalize file paths
        $validated = $this->normalizeFilePaths($validated, ['cover', 'pdf_url']);

        $issue = Issue::create($validated);
        return response()->json($issue, 201);
    }

    public function update(Request $request, $id)
    {
        $issue = Issue::findOrFail($id);

        $validated = $request->validate([
            'issue_no' => 'required|string',
            'cover' => 'required|string',
            'pdf_url' => 'required|string',
            'premium' => 'boolean'
        ]);

        // Normalize file paths
        $validated = $this->normalizeFilePaths($validated, ['cover', 'pdf_url']);

        $issue->update($validated);
        return response()->json($issue);
    }

    public function destroy($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->delete();
        return response()->json(['message' => 'Issue deleted successfully']);
    }
}
