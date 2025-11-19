<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Award;
use App\Models\Issue;
use App\Models\Offer;
use App\Models\Concierge;
use App\Models\Nomination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function dashboard()
    {
        // Get counts
        $totalUsers = User::count();
        $totalPosts = Post::count();
        $totalAwards = Award::count();
        $totalIssues = Issue::count();
        $totalOffers = Offer::count();
        $pendingConcierge = Concierge::where('status', 'pending')->count();
        $totalNominations = Nomination::count();

        // Get recent activity counts (last 30 days)
        $thirtyDaysAgo = now()->subDays(30);
        $newUsersThisMonth = User::where('created_at', '>=', $thirtyDaysAgo)->count();
        $newPostsThisMonth = Post::where('created_at', '>=', $thirtyDaysAgo)->count();
        $newOffersThisMonth = Offer::where('created_at', '>=', $thirtyDaysAgo)->count();

        // Get user tier distribution
        $usersByTier = User::select('tier', DB::raw('count(*) as count'))
            ->groupBy('tier')
            ->get()
            ->pluck('count', 'tier');

        // Get posts by category
        $postsByCategory = Post::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category');

        // Get recent posts
        $recentPosts = Post::orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'title', 'category', 'created_at']);

        // Get upcoming awards
        $upcomingAwards = Award::where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->take(5)
            ->get(['id', 'city', 'venue', 'date']);

        // Get concierge stats
        $conciergeByStatus = Concierge::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return response()->json([
            'overview' => [
                'total_users' => $totalUsers,
                'total_posts' => $totalPosts,
                'total_awards' => $totalAwards,
                'total_issues' => $totalIssues,
                'total_offers' => $totalOffers,
                'pending_concierge' => $pendingConcierge,
                'total_nominations' => $totalNominations,
            ],
            'growth' => [
                'new_users_this_month' => $newUsersThisMonth,
                'new_posts_this_month' => $newPostsThisMonth,
                'new_offers_this_month' => $newOffersThisMonth,
            ],
            'distribution' => [
                'users_by_tier' => $usersByTier,
                'posts_by_category' => $postsByCategory,
                'concierge_by_status' => $conciergeByStatus,
            ],
            'recent_activity' => [
                'recent_posts' => $recentPosts,
                'upcoming_awards' => $upcomingAwards,
            ],
        ]);
    }
}
