@echo off
echo ========================================
echo Laravel Production Deployment Script
echo ========================================

echo.
echo 1. Installing/Updating NPM dependencies...
call npm install

echo.
echo 2. Building production assets...
call npm run build:prod

echo.
echo 3. Installing/Updating Composer dependencies...
call composer install --optimize-autoloader --no-dev

echo.
echo 4. Clearing Laravel caches...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear
call php artisan cache:clear

echo.
echo 5. Caching Laravel configurations...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache

echo.
echo 6. Running database migrations...
call php artisan migrate --force

echo.
echo 7. Setting file permissions...
echo Note: On Windows, permissions are usually handled by the web server

echo.
echo ========================================
echo Deployment completed!
echo ========================================
echo.
echo Please ensure:
echo - Your .env file is properly configured
echo - Database credentials are correct
echo - Web server is configured to point to the public/ directory
echo - SSL certificate is installed (if using HTTPS)
echo.
pause
