<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\Offer;
use App\Models\Award;
use App\Models\Issue;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nichemagazine.me',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'tier' => 'vip',
            'city' => 'Dubai'
        ]);

        // Create Sample User
        $user = User::create([
            'name' => 'Sarah Al-Mansouri',
            'email' => 'sarah@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'tier' => 'premium',
            'city' => 'Riyadh',
            'company' => 'Luxury Brands ME'
        ]);

        // Create Supplier
        $supplierUser = User::create([
            'name' => 'Luxury Events Co',
            'email' => 'supplier@example.com',
            'password' => Hash::make('password'),
            'role' => 'supplier',
            'tier' => 'basic',
            'city' => 'Dubai'
        ]);

        $supplier = Supplier::create([
            'user_id' => $supplierUser->id,
            'company' => 'Luxury Events Co',
            'category' => 'Events & Venues',
            'city' => 'Dubai',
            'bio' => 'Premier event planning and luxury venue management in the Middle East'
        ]);

        // Sample Posts
        Post::create([
            'title' => 'The Rise of Sustainable Fashion in the Middle East',
            'cover' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b',
            'category' => 'Fashion',
            'excerpt' => 'Exploring how Middle Eastern designers are leading the sustainable fashion movement',
            'body' => 'Full article content here...',
            'published_at' => now()->subDays(2)
        ]);

        Post::create([
            'title' => 'Dubai\'s New Architectural Marvels',
            'cover' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c',
            'category' => 'Architecture',
            'excerpt' => 'A look at the stunning new developments reshaping Dubai\'s skyline',
            'body' => 'Full article content here...',
            'published_at' => now()->subDays(5)
        ]);

        Post::create([
            'title' => 'Luxury Hospitality Trends 2024',
            'cover' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945',
            'category' => 'Hospitality',
            'excerpt' => 'What to expect from luxury hotels and resorts this year',
            'body' => 'Full article content here...',
            'published_at' => now()->subDays(1)
        ]);

        // Sample Offers
        Offer::create([
            'supplier_id' => $supplier->id,
            'title' => 'Exclusive Venue Booking - 20% Off',
            'photo' => 'https://images.unsplash.com/photo-1519167758481-83f29da8c2b6',
            'price' => 15000,
            'description' => 'Book our premium venue for your next luxury event',
            'city' => 'Dubai',
            'whatsapp' => '+971501234567'
        ]);

        Offer::create([
            'supplier_id' => $supplier->id,
            'title' => 'VIP Event Planning Package',
            'photo' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622',
            'price' => 25000,
            'description' => 'Complete event planning with luxury touches',
            'city' => 'Dubai',
            'whatsapp' => '+971501234567'
        ]);

        // Sample Awards
        Award::create([
            'city' => 'Riyadh, KSA',
            'venue' => 'Four Seasons Hotel',
            'date' => now()->addMonths(2),
            'description' => 'Celebrating excellence in luxury lifestyle across Saudi Arabia',
            'ticket_link' => 'https://buy.stripe.com/test_ksa_awards'
        ]);

        Award::create([
            'city' => 'London, UK',
            'venue' => 'The Dorchester',
            'date' => now()->addMonths(4),
            'description' => 'International edition of the prestigious Niche Awards',
            'ticket_link' => 'https://buy.stripe.com/test_london_awards'
        ]);

        Award::create([
            'city' => 'Milan, Italy',
            'venue' => 'Palazzo Parigi',
            'date' => now()->addMonths(6),
            'description' => 'Fashion and design excellence awards in Milan',
            'ticket_link' => 'https://buy.stripe.com/test_milan_awards'
        ]);

        // Sample Issues
        Issue::create([
            'issue_no' => 'Issue 24 - Winter 2024',
            'cover' => 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d',
            'pdf_url' => 'https://example.com/issues/24.pdf',
            'premium' => false
        ]);

        Issue::create([
            'issue_no' => 'Issue 25 - Spring 2025',
            'cover' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36',
            'pdf_url' => 'https://example.com/issues/25.pdf',
            'premium' => true
        ]);
    }
}
