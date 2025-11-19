<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferView;
use App\Traits\NormalizesFilePaths;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierDashboardController extends Controller
{
    use NormalizesFilePaths;

    /**
     * Get supplier dashboard statistics
     */
    public function stats()
    {
        $user = auth()->user();
        
        if (!$user->supplier) {
            return response()->json(['message' => 'Supplier profile not found'], 404);
        }

        $offers = Offer::where('supplier_id', $user->supplier->id)->get();
        
        $stats = [
            'total_offers' => $offers->count(),
            'total_views' => OfferView::whereIn('offer_id', $offers->pluck('id'))->count(),
            'unique_views' => OfferView::whereIn('offer_id', $offers->pluck('id'))
                ->distinct('ip_address')
                ->count('ip_address'),
            'views_this_month' => OfferView::whereIn('offer_id', $offers->pluck('id'))
                ->whereMonth('created_at', now()->month)
                ->count(),
            'top_offer' => $offers->sortByDesc(function($offer) {
                return $offer->views()->count();
            })->first(),
        ];

        return response()->json($stats);
    }

    /**
     * Get supplier's offers with analytics
     */
    public function myOffers()
    {
        $user = auth()->user();
        
        if (!$user->supplier) {
            return response()->json(['message' => 'Supplier profile not found'], 404);
        }

        $offers = Offer::where('supplier_id', $user->supplier->id)
            ->withCount('views')
            ->with(['views' => function($query) {
                $query->latest()->limit(10);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Add analytics to each offer
        $offers->each(function($offer) {
            $offer->unique_views = $offer->views()->distinct('ip_address')->count('ip_address');
            $offer->views_today = $offer->views()->whereDate('created_at', today())->count();
            $offer->views_this_week = $offer->views()->where('created_at', '>=', now()->subWeek())->count();
        });

        return response()->json($offers);
    }

    /**
     * Get detailed analytics for a specific offer
     */
    public function offerAnalytics($id)
    {
        $user = auth()->user();
        
        $offer = Offer::where('id', $id)
            ->where('supplier_id', $user->supplier->id)
            ->firstOrFail();

        $analytics = [
            'offer' => $offer,
            'total_views' => $offer->views()->count(),
            'unique_views' => $offer->views()->distinct('ip_address')->count('ip_address'),
            'views_by_day' => $offer->views()
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get(),
            'recent_viewers' => $offer->views()
                ->with('user:id,name,email')
                ->latest()
                ->limit(20)
                ->get(),
        ];

        return response()->json($analytics);
    }

    /**
     * Create a new offer
     */
    public function createOffer(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->supplier) {
            return response()->json(['message' => 'Supplier profile not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'required|string',
            'price' => 'required|string|max:100',
            'description' => 'required|string',
            'city' => 'required|string|max:100',
            'whatsapp' => 'nullable|string|max:50',
        ]);

        // Normalize photo path
        if (isset($validated['photo'])) {
            $validated['photo'] = $this->normalizePath($validated['photo']);
        }

        $validated['supplier_id'] = $user->supplier->id;

        $offer = Offer::create($validated);

        return response()->json($offer, 201);
    }

    /**
     * Update an offer
     */
    public function updateOffer(Request $request, $id)
    {
        $user = auth()->user();
        
        $offer = Offer::where('id', $id)
            ->where('supplier_id', $user->supplier->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'required|string',
            'price' => 'required|string|max:100',
            'description' => 'required|string',
            'city' => 'required|string|max:100',
            'whatsapp' => 'nullable|string|max:50',
        ]);

        // Normalize photo path
        if (isset($validated['photo'])) {
            $validated['photo'] = $this->normalizePath($validated['photo']);
        }

        $offer->update($validated);

        return response()->json($offer);
    }

    /**
     * Delete an offer
     */
    public function deleteOffer($id)
    {
        $user = auth()->user();
        
        $offer = Offer::where('id', $id)
            ->where('supplier_id', $user->supplier->id)
            ->firstOrFail();

        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully']);
    }
}
