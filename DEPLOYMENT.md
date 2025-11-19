# Deployment Guide for Hostinger

## Pre-Deployment Checklist

### 1. Update .env File
Copy `.env.production` to `.env` and update:
- `APP_URL` - Your domain
- `DB_*` - Database credentials from Hostinger
- `MAIL_*` - Email settings
- Set `APP_DEBUG=false`
- Set `APP_ENV=production`

### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. Run Migrations
```bash
php artisan migrate --force
```

### 5. Create Storage Link
```bash
php artisan storage:link
```

### 6. Optimize Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 7. Clear Old Caches (if updating)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Hostinger Specific Setup

### Document Root
Set to: `public_html/public` or wherever your `public` folder is

### PHP Version
Ensure PHP 8.1 or higher is selected in Hostinger control panel

### Database
1. Create MySQL database in Hostinger panel
2. Note the credentials
3. Update `.env` file

### File Structure
```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← Document root should point here
│   ├── index.php
│   ├── .htaccess
│   └── storage/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
└── artisan
```

## Troubleshooting

### 500 Error
1. Check `storage/logs/laravel.log`
2. Visit `/debug.php` to see detailed error
3. Ensure storage folders are writable
4. Check PHP version (must be 8.1+)
5. Verify `.env` file exists and has APP_KEY

### Storage Issues
```bash
php artisan storage:link
chmod -R 775 storage
```

### Permission Denied
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Database Connection Failed
- Verify database credentials in `.env`
- Check if database exists
- Ensure database user has proper permissions

## Post-Deployment

1. Test API endpoints: `/api/posts`, `/api/awards`, `/api/offers`
2. Test image uploads
3. Test authentication
4. Delete `public/debug.php` file
5. Monitor `storage/logs/laravel.log` for errors

## Update APP_URL in Mobile App

After deployment, update `niche-app/src/config/config.js`:
```javascript
const API_BASE_URL = 'https://darkslategrey-echidna-527508.hostingersite.com/api';
```
