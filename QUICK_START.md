# Quick Start Guide - Niche Plus Backend

Since you're starting from scratch, here's the fastest way to get Laravel running:

## Option 1: Use Laravel Installer (Recommended - Fastest)

```bash
# Install Laravel globally (if not already installed)
composer global require laravel/installer

# Create a fresh Laravel project
cd "D:\Niche Plus"
laravel new niche-backend-fresh

# Copy our custom files into the new project
# Then copy these folders from niche-backend to niche-backend-fresh:
# - app/Models/*
# - app/Http/Controllers/*
# - app/Http/Middleware/RoleMiddleware.php
# - database/migrations/*
# - database/seeders/*
# - routes/api.php
```

## Option 2: Use Composer Create-Project

```bash
cd "D:\Niche Plus"
composer create-project laravel/laravel niche-backend-fresh

# Then copy our custom files as mentioned above
```

## Option 3: Manual Setup (What I'll do now)

I'll create all the missing Laravel core files for you. After that:

```bash
cd niche-backend
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

## After Setup

1. Create MySQL database named `niche_plus`
2. Update `.env` with your database credentials
3. Run migrations: `php artisan migrate --seed`
4. Start server: `php artisan serve`

The API will be available at: http://localhost:8000/api

---

**Recommendation:** Use Option 1 or 2 for a clean Laravel installation, then copy our custom files. This ensures all Laravel dependencies are properly set up.
