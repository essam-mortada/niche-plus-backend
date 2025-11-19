@echo off
echo Setting up storage for image uploads...

REM Create upload directories
if not exist "public\uploads\posts" mkdir public\uploads\posts
if not exist "public\uploads\offers" mkdir public\uploads\offers
if not exist "public\uploads\issues" mkdir public\uploads\issues
if not exist "public\uploads\awards" mkdir public\uploads\awards

echo Directories created successfully!

REM Create symbolic link
echo Creating storage symbolic link...
php artisan storage:link

echo.
echo Setup complete!
echo.
echo Upload directories created:
echo - public\uploads\posts
echo - public\uploads\offers
echo - public\uploads\issues
echo - public\uploads\awards
echo.
echo Storage link created at: public\storage
echo.
pause
