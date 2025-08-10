@echo off
echo ====================================
echo   Setting up Laravel Scheduler
echo ====================================
echo.

echo Current directory: %CD%
echo.

echo Creating Windows Task Scheduler for Laravel Scheduler...
echo.

REM สร้าง Task สำหรับ Laravel Scheduler ที่รันทุก 1 นาที
schtasks /create /tn "LaravelScheduler" /tr "php %CD%\artisan schedule:run" /sc minute /mo 1 /ru "SYSTEM" /f

if %ERRORLEVEL% EQU 0 (
    echo ✅ Laravel Scheduler Task created successfully!
    echo.
    echo Task Details:
    echo - Name: LaravelScheduler
    echo - Command: php %CD%\artisan schedule:run
    echo - Schedule: Every 1 minute
    echo - User: SYSTEM
    echo.
    echo To view the task:
    echo schtasks /query /tn "LaravelScheduler"
    echo.
    echo To delete the task:
    echo schtasks /delete /tn "LaravelScheduler" /f
) else (
    echo ❌ Failed to create Laravel Scheduler Task
    echo.
    echo Alternative: Run manually in development:
    echo php artisan schedule:work
)

echo.
echo ====================================
echo   Laravel Scheduler Status
echo ====================================
echo.
echo Scheduled Commands:
echo - sync:all-clients: Every hour (without overlapping, run in background)
echo.
echo To test scheduler manually:
echo php artisan schedule:run
echo.
echo To view scheduled tasks:
echo php artisan schedule:list
echo.
pause
