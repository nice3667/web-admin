@echo off
echo ====================================
echo   Testing Auto Sync System
echo ====================================
echo.

echo Current directory: %CD%
echo.

echo Testing Auto Sync Commands...
echo.

echo 1. Testing sync:all-clients command...
php artisan sync:all-clients
echo.

echo 2. Testing clients:sync command...
php artisan clients:sync
echo.

echo 3. Testing clients:auto-sync command (single run)...
php artisan clients:auto-sync --interval=1
echo.

echo 4. Testing clients:stats command...
php artisan clients:stats
echo.

echo 5. Testing schedule:list...
php artisan schedule:list
echo.

echo 6. Testing schedule:run (manual trigger)...
php artisan schedule:run
echo.

echo ====================================
echo   Test Results
echo ====================================
echo.
echo If all commands executed without errors, the system is ready.
echo.
echo Next steps:
echo 1. Run setup_laravel_scheduler.bat to set up Windows Task Scheduler
echo 2. Or run start_auto_sync_daemon.bat to start daemon mode
echo 3. Or run start_queue_worker.bat to start queue processing
echo.
pause
