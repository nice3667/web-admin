@echo off
echo ====================================
echo   Starting Queue Worker
echo ====================================
echo.

echo Current directory: %CD%
echo.

echo Starting Laravel Queue Worker...
echo This will process background jobs including AutoSyncClientsJob
echo.

php artisan queue:work --daemon --tries=3 --timeout=300

echo.
echo ====================================
echo   Queue Worker Stopped
echo ====================================
echo.
echo The queue worker has stopped.
echo To restart, run this script again.
echo.
pause
