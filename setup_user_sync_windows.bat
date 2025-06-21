@echo off
echo Setting up User Data Sync Scheduled Tasks for Windows...
echo.

set CURRENT_DIR=%cd%
set PHP_PATH=php
set TASK_PREFIX=UserDataSync

echo Current Directory: %CURRENT_DIR%
echo.

echo Removing existing tasks (if any)...
schtasks /delete /tn "%TASK_PREFIX%_AllUsers" /f >nul 2>&1
schtasks /delete /tn "%TASK_PREFIX%_Ham" /f >nul 2>&1
schtasks /delete /tn "%TASK_PREFIX%_Kantapong" /f >nul 2>&1
schtasks /delete /tn "%TASK_PREFIX%_Janischa" /f >nul 2>&1

echo.
echo Creating new scheduled tasks...

REM Create task for syncing all users every 30 minutes
echo Creating All Users Sync task (every 30 minutes)...
schtasks /create /tn "%TASK_PREFIX%_AllUsers" /tr "cmd /c cd /d \"%CURRENT_DIR%\" && %PHP_PATH% artisan sync:all-users >> storage\logs\user-sync.log 2>&1" /sc minute /mo 30 /f

REM Create task for Ham sync every hour at minute 5
echo Creating Ham Sync task (hourly at minute 5)...
schtasks /create /tn "%TASK_PREFIX%_Ham" /tr "cmd /c cd /d \"%CURRENT_DIR%\" && %PHP_PATH% artisan sync:ham-data >> storage\logs\ham-sync.log 2>&1" /sc hourly /st 00:05 /f

REM Create task for Kantapong sync every hour at minute 15
echo Creating Kantapong Sync task (hourly at minute 15)...
schtasks /create /tn "%TASK_PREFIX%_Kantapong" /tr "cmd /c cd /d \"%CURRENT_DIR%\" && %PHP_PATH% artisan sync:kantapong-data >> storage\logs\kantapong-sync.log 2>&1" /sc hourly /st 00:15 /f

REM Create task for Janischa sync every hour at minute 25
echo Creating Janischa Sync task (hourly at minute 25)...
schtasks /create /tn "%TASK_PREFIX%_Janischa" /tr "cmd /c cd /d \"%CURRENT_DIR%\" && %PHP_PATH% artisan sync:janischa-data >> storage\logs\janischa-sync.log 2>&1" /sc hourly /st 00:25 /f

echo.
echo Scheduled tasks created successfully!
echo.
echo Task Summary:
echo - All Users Sync: Every 30 minutes
echo - Ham Sync: Every hour at minute 5
echo - Kantapong Sync: Every hour at minute 15
echo - Janischa Sync: Every hour at minute 25
echo.
echo Log files will be created in:
echo - All users: storage\logs\user-sync.log
echo - Ham: storage\logs\ham-sync.log
echo - Kantapong: storage\logs\kantapong-sync.log
echo - Janischa: storage\logs\janischa-sync.log
echo.
echo To view scheduled tasks: schtasks /query /tn "%TASK_PREFIX%*"
echo To delete all sync tasks: 
echo   schtasks /delete /tn "%TASK_PREFIX%_AllUsers" /f
echo   schtasks /delete /tn "%TASK_PREFIX%_Ham" /f
echo   schtasks /delete /tn "%TASK_PREFIX%_Kantapong" /f
echo   schtasks /delete /tn "%TASK_PREFIX%_Janischa" /f
echo.
echo You can test the sync manually with:
echo   php artisan sync:all-users
echo   php artisan sync:ham-data
echo   php artisan sync:kantapong-data
echo   php artisan sync:janischa-data
echo.
pause 