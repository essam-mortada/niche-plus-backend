# Laravel Backend Setup

## Prerequisites
- PHP 8.1+
- Composer
- MySQL

## Installation Steps

1. **Install Laravel dependencies**
```bash
cd niche-backend
composer install
```

2. **Install JWT Auth**
```bash
composer require tymon/jwt-auth
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. **Update .env file**
```
DB_DATABASE=niche_plus
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run migrations and seed**
```bash
php artisan migrate
php artisan db:seed
```

6. **Register middleware in app/Http/Kernel.php**
Add to $routeMiddleware:
```php
'role' => \App\Http\Middleware\RoleMiddleware::class,
```

7. **Update config/auth.php**
```php
'defaults' => [
    'guard' => 'api',
    'passwords' => 'users',
],

'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

8. **Start server**
```bash
php artisan serve
```

## Test Accounts
- Admin: admin@nichemagazine.me / password
- User: sarah@example.com / password
- Supplier: supplier@example.com / password

## API Endpoints
Base URL: http://localhost:8000/api

### Auth
- POST /register
- POST /login
- GET /me
- POST /logout

### Posts
- GET /posts
- GET /posts/{id}

### Offers
- GET /offers
- POST /offers (supplier/admin)

### Awards
- GET /awards
- POST /nominations

### Magazine
- GET /issues
- GET /issues/{id}

### Concierge
- POST /concierge
- GET /concierge
