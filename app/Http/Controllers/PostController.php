<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Traits\NormalizesFilePaths;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use NormalizesFilePaths;
    public function index(Request $request)
    {
        $query = Post::query()->whereNotNull('published_at');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate(20);

        return response()->json($posts);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return response()->json($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'cover' => 'required|string',
            'category' => 'required|in:Fashion,Beauty,Hospitality,Architecture,Lifestyle',
            'excerpt' => 'required|string',
            'body' => 'required|string',
            'published_at' => 'nullable|date'
        ]);

        // Normalize cover path (remove domain if present)
        $validated['cover'] = $this->normalizePath($validated['cover']);

        $post = Post::create($validated);
        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        $data = $request->all();
        
        // Normalize cover path if provided
        if (isset($data['cover'])) {
            $data['cover'] = $this->normalizePath($data['cover']);
        }
        
        $post->update($data);
        return response()->json($post);
    }

    public function destroy($id)
    {
        Post::findOrFail($id)->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}
