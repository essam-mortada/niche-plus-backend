<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    /**
     * Get all pending offers for admin review
     */
    public function pending()
    {
        $offers = Offer::with(['supplier.user'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($offers);
    }

    /**
     * Get all approved offers
     */
    public function approved()
    {
        $offers = Offer::with(['supplier.user', 'reviewer'])
            ->approved()
            ->orderBy('reviewed_at', 'desc')
            ->paginate(20);

        return response()->json($offers);
    }

    /**
     * Get all rejected offers
     */
    public function rejected()
    {
        $offers = Offer::with(['supplier.user', 'reviewer'])
            ->rejected()
            ->orderBy('reviewed_at', 'desc')
            ->paginate(20);

        return response()->json($offers);
    }

    /**
     * Get all offers with filters
     */
    public function index(Request $request)
    {
        $query = Offer::with(['supplier.user', 'reviewer']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $offers = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($offers);
    }

    /**
     * Approve an offer
     */
    public function approve(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);

        if (!$offer->isPending()) {
            return response()->json([
                'message' => 'Only pending offers can be approved'
            ], 400);
        }

        $offer->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'rejection_reason' => null
        ]);

        return response()->json([
            'message' => 'Offer approved successfully',
            'offer' => $offer->load(['supplier.user', 'reviewer'])
        ]);
    }

    /**
     * Reject an offer
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $offer = Offer::findOrFail($id);

        if (!$offer->isPending()) {
            return response()->json([
                'message' => 'Only pending offers can be rejected'
            ], 400);
        }

        $offer->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Offer rejected successfully',
            'offer' => $offer->load(['supplier.user', 'reviewer'])
        ]);
    }

    /**
     * Get offer statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => Offer::count(),
            'pending' => Offer::pending()->count(),
            'approved' => Offer::approved()->count(),
            'rejected' => Offer::rejected()->count(),
            'pending_today' => Offer::pending()->whereDate('created_at', today())->count(),
        ];

        return response()->json($stats);
    }
}
