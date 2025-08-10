@echo off
echo ====================================
echo   Starting Auto Sync Daemon
echo ====================================
echo.

echo Current directory: %CD%
echo.

echo Choose Auto Sync Mode:
echo 1. New clients only (every 15 minutes)
echo 2. New clients only (every 30 minutes) 
echo 3. All clients (every 1 hour)
echo 4. Custom interval
echo.

set /p choice="Enter your choice (1-4): "

if "%choice%"=="1" (
    echo Starting Auto Sync Daemon - New clients only, every 15 minutes...
    php artisan clients:auto-sync --daemon --interval=15 --new-only
) else if "%choice%"=="2" (
    echo Starting Auto Sync Daemon - New clients only, every 30 minutes...
    php artisan clients:auto-sync --daemon --interval=30 --new-only
) else if "%choice%"=="3" (
    echo Starting Auto Sync Daemon - All clients, every 1 hour...
    php artisan clients:auto-sync --daemon --interval=60
) else if "%choice%"=="4" (
    set /p interval="Enter interval in minutes: "
    set /p new_only="Sync new clients only? (y/n): "
    
    if /i "%new_only%"=="y" (
        echo Starting Auto Sync Daemon - New clients only, every %interval% minutes...
        php artisan clients:auto-sync --daemon --interval=%interval% --new-only
    ) else (
        echo Starting Auto Sync Daemon - All clients, every %interval% minutes...
        php artisan clients:auto-sync --daemon --interval=%interval%
    )
) else (
    echo Invalid choice. Using default: New clients only, every 30 minutes...
    php artisan clients:auto-sync --daemon --interval=30 --new-only
)

echo.
echo ====================================
echo   Auto Sync Daemon Started
echo ====================================
echo.
echo To stop the daemon: Press Ctrl+C
echo.
echo The daemon will:
echo - Run continuously in the background
echo - Sync data at the specified interval
echo - Log all activities to Laravel logs
echo - Auto-restart on errors
echo.
pause
