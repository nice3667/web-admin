@echo off
echo ========================================
echo GoGold Admin Dashboard - Production Deploy
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
echo 8. Checking production environment...
echo.
echo Current Environment Settings:
echo - APP_ENV: %APP_ENV%
echo - APP_DEBUG: %APP_DEBUG%
echo - APP_URL: %APP_URL%

echo.
echo ========================================
echo Production Deployment completed!
echo ========================================
echo.
echo IMPORTANT: Please ensure your .env file has:
echo - APP_DEBUG=false
echo - LOG_LEVEL=error
echo - Proper MAIL settings configured
echo - SESSION_SECURE_COOKIE=true
echo.
echo Check PRODUCTION_ENV_FIXES.md for details
echo.
pause