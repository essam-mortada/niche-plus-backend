<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\Offer;
use App\Models\Award;
use App\Models\Nomination;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $type = $request->input('type', 'all'); // all, posts, offers, awards, suppliers
            
            if (empty($query)) {
                return response()->json([
                    'results' => [],
                    'count' => 0
                ]);
            }

            $results = [];

        // Search Posts
        if ($type === 'all' || $type === 'posts') {
            $posts = Post::where(function($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('body', 'LIKE', "%{$query}%")
                      ->orWhere('excerpt', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();
            
            \Log::info('Posts found: ' . $posts->count() . ' for query: ' . $query);
            
            foreach ($posts as $post) {
                $postData = $post->toArray();
                $postData['type'] = 'post';
                $postData['description'] = $post->excerpt ?? substr($post->body, 0, 100) . '...';
                $postData['image'] = $post->cover_url;
                $results[] = $postData;
            }
        }

        // Search Offers
        if ($type === 'all' || $type === 'offers') {
            \Log::info('Searching offers with query: ' . $query);
            \Log::info('Total offers in DB: ' . Offer::count());
            
            $offers = Offer::with('supplier')
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();
            
            \Log::info('Offers found: ' . $offers->count() . ' for query: ' . $query);
            \Log::info('Offers SQL: ' . Offer::where(function($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%");
                })->toSql());
            
            foreach ($offers as $offer) {
                $offerData = $offer->toArray();
                $offerData['type'] = 'offer';
                $offerData['image'] = $offer->photo_url;
                $results[] = $offerData;
            }
        }

        // Search Awards
        if ($type === 'all' || $type === 'awards') {
            $awards = Award::where(function($q) use ($query) {
                    $q->where('city', 'LIKE', "%{$query}%")
                      ->orWhere('venue', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();
            
            \Log::info('Awards found: ' . $awards->count() . ' for query: ' . $query);
            
            foreach ($awards as $award) {
                $awardData = $award->toArray();
                $awardData['type'] = 'award';
                $awardData['title'] = $award->city . ' - ' . $award->venue;
                $awardData['image'] = $award->image_url;
                $results[] = $awardData;
            }
        }

        // Search Suppliers
        if ($type === 'all' || $type === 'suppliers') {
            $suppliers = Supplier::where(function($q) use ($query) {
                    $q->where('company', 'LIKE', "%{$query}%")
                      ->orWhere('bio', 'LIKE', "%{$query}%")
                      ->orWhere('category', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();
            
            \Log::info('Suppliers found: ' . $suppliers->count() . ' for query: ' . $query);
            
            foreach ($suppliers as $supplier) {
                $results[] = [
                    'id' => $supplier->id,
                    'type' => 'supplier',
                    'title' => $supplier->company,
                    'description' => substr($supplier->bio ?? '', 0, 100) . '...',
                    'image' => null,
                    'category' => $supplier->category,
                    'city' => $supplier->city,
                    'created_at' => $supplier->created_at
                ];
            }
        }

            // Sort by relevance (created_at desc)
            if (!empty($results)) {
                usort($results, function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
            }

            return response()->json([
                'results' => $results,
                'count' => count($results),
                'query' => $query
            ]);
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'results' => [],
                'count' => 0,
                'query' => $query ?? '',
                'error' => $e->getMessage()
            ], 200);
        }
    }

    public function suggestions(Request $request)
    {
        try {
            $query = $request->input('q', '');
            
            if (strlen($query) < 2) {
                return response()->json(['suggestions' => []]);
            }

            $suggestions = [];

            // Get popular search terms from titles
            $postTitles = Post::where('title', 'LIKE', "%{$query}%")
                ->limit(3)
                ->pluck('title')
                ->toArray();
            
            $awardCities = Award::where('city', 'LIKE', "%{$query}%")
                ->orWhere('venue', 'LIKE', "%{$query}%")
                ->limit(3)
                ->get()
                ->map(function($award) {
                    return $award->city . ' - ' . $award->venue;
                })
                ->toArray();
            
            $offerTitles = Offer::where('title', 'LIKE', "%{$query}%")
                ->limit(3)
                ->pluck('title')
                ->toArray();

            $suggestions = array_merge(
                $postTitles,
                $awardCities,
                $offerTitles
            );

            // Remove duplicates and limit
            $suggestions = array_unique($suggestions);
            $suggestions = array_slice($suggestions, 0, 5);

            return response()->json(['suggestions' => array_values($suggestions)]);
        } catch (\Exception $e) {
            \Log::error('Search suggestions error: ' . $e->getMessage());
            return response()->json(['suggestions' => []], 200);
        }
    }

    public function test(Request $request)
    {
        $offers = Offer::with('supplier')->get();
        $awards = Award::all();
        
        return response()->json([
            'offers_count' => $offers->count(),
            'awards_count' => $awards->count(),
            'sample_offer' => $offers->first(),
            'sample_award' => $awards->first(),
        ]);
    }
}
