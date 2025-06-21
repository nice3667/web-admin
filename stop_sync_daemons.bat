@echo off
echo === Stopping Sync Daemons for All Reports ===

echo Stopping Report Data Sync processes...
taskkill /F /FI "WINDOWTITLE eq Report-Sync*" 2>nul
taskkill /F /FI "WINDOWTITLE eq Report1-Sync*" 2>nul  
taskkill /F /FI "WINDOWTITLE eq Report2-Sync*" 2>nul

echo Stopping any remaining sync processes...
taskkill /F /IM php.exe /FI "COMMANDLINE eq *sync:report*" 2>nul

echo.
echo === All Sync Daemons Stopped ===
echo.
pause 