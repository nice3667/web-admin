@echo off
echo ====================================
echo   Enabling Auto-Sync for Reports
echo ====================================
echo.

echo Current directory: %CD%
echo.

echo Testing sync commands...
echo.

echo 1. Testing Ham sync...
php artisan sync:ham-data --force
echo.

echo 2. Testing Kantapong sync...
php artisan sync:kantapong-data --force
echo.

echo 3. Testing Janischa sync...
php artisan sync:janischa-data --force
echo.

echo 4. Testing all users sync...
php artisan sync:all-users --force
echo.

echo ====================================
echo   Auto-Sync Status
echo ====================================
echo.
echo Scheduled tasks in Laravel:
echo - All users sync: Every 30 minutes
echo - Ham backup sync: Every hour at minute 5
echo - Kantapong backup sync: Every hour at minute 15
echo - Janischa backup sync: Every hour at minute 25
echo.

echo To run Laravel scheduler (for development):
echo php artisan schedule:work
echo.

echo For production, add this to Windows Task Scheduler:
echo Command: php
echo Arguments: %CD%\artisan schedule:run
echo Start in: %CD%
echo Run every: 1 minute
echo.

echo Log files location:
echo - storage\logs\user-sync.log
echo - storage\logs\ham-sync.log
echo - storage\logs\kantapong-sync.log
echo - storage\logs\janischa-sync.log
echo.

pause 