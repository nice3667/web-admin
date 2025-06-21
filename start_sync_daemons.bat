@echo off
echo === Starting Sync Daemons for All Reports ===

echo Starting Report Data Sync (Janischa) in background...
start "Report-Sync" cmd /k "php artisan sync:report-data --daemon --interval=15 --new-only"

echo Starting Report1 Data Sync (Ham) in background...
start "Report1-Sync" cmd /k "php artisan sync:report1-data --daemon --interval=15 --new-only"

echo Starting Report2 Data Sync (Kantapong) in background...
start "Report2-Sync" cmd /k "php artisan sync:report2-data --daemon --interval=15 --new-only"

echo.
echo === All Sync Daemons Started ===
echo.
echo Management:
echo - Report (Janischa): Window titled "Report-Sync"
echo - Report1 (Ham): Window titled "Report1-Sync" 
echo - Report2 (Kantapong): Window titled "Report2-Sync"
echo.
echo To stop: Close the respective command windows or run stop_sync_daemons.bat
echo.
pause 