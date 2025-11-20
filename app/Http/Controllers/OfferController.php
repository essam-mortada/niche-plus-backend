<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Traits\NormalizesFilePaths;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use NormalizesFilePaths;
    public function index(Request $request)
    {
        $query = Offer::with('supplier.user');

        $user = auth()->user();
        $isSupplier = $user->supplier !== null;

        // Determine what offers to show based on user role and request
        if ($request->has('my_offers') && $isSupplier) {
            // Supplier viewing their own offers - show all statuses
            $query->where('supplier_id', $user->supplier->id);
        } else {
            // Everyone else (including admins) viewing marketplace - only approved
            // Admins should use the admin endpoints to see pending/rejected offers
            $query->where('status', 'approved');
        }

        // Filter by supplier if requested
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        $offers = $query->paginate(20);
        return response()->json($offers);
    }

    public function show(Request $request, $id)
    {
        $offer = Offer::with('supplier.user')->findOrFail($id);
        
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        $isOwner = $user->supplier && $user->supplier->id === $offer->supplier_id;

        // Check if user can view this offer
        // Admins and owners can view any status, regular users only approved
        if (!$isAdmin && !$isOwner && $offer->status !== 'approved') {
            return response()->json([
                'message' => 'This offer is not available'
            ], 403);
        }
        
        // Track view only for approved offers or when owner/admin is viewing
        if ($offer->status === 'approved' || $isOwner || $isAdmin) {
            try {
                $view = \App\Models\OfferView::create([
                    'offer_id' => $offer->id,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                \Log::info("👁️ Offer view tracked", [
                    'offer_id' => $offer->id,
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                    'view_id' => $view->id,
                ]);
            } catch (\Exception $e) {
                \Log::error("❌ Failed to track offer view: " . $e->getMessage());
            }
        }
        
        return response()->json($offer);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'photo' => 'required|string',
            'price' => 'nullable|numeric',
            'description' => 'required|string',
            'city' => 'required|string',
            'whatsapp' => 'nullable|string'
        ]);

        // Normalize photo path
        if (isset($validated['photo'])) {
            $validated['photo'] = $this->normalizePath($validated['photo']);
        }

        $supplier = auth()->user()->supplier;
        if (!$supplier) {
            return response()->json(['error' => 'Not a supplier'], 403);
        }

        // Set status to pending by default (admin will approve)
        $validated['status'] = 'pending';

        $offer = $supplier->offers()->create($validated);
        
        return response()->json([
            'message' => 'Offer created successfully and is pending admin approval',
            'offer' => $offer
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);
        
        if ($offer->supplier->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->all();
        
        // Normalize photo path if provided
        if (isset($data['photo'])) {
            $data['photo'] = $this->normalizePath($data['photo']);
        }

        $offer->update($data);
        return response()->json($offer);
    }

    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        
        if ($offer->supplier->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $offer->delete();
        return response()->json(['message' => 'Offer deleted']);
    }
}
