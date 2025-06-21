#!/bin/bash

# Setup User Data Sync Cron Jobs
# This script sets up automatic synchronization for Ham, Kantapong, and Janischa data

echo "Setting up User Data Sync Cron Jobs..."

# Get the current directory (where Laravel is installed)
LARAVEL_PATH=$(pwd)
PHP_PATH=$(which php)

echo "Laravel Path: $LARAVEL_PATH"
echo "PHP Path: $PHP_PATH"

# Create a temporary cron file
TEMP_CRON_FILE="/tmp/user_sync_cron"

# Get existing cron jobs (if any)
crontab -l > $TEMP_CRON_FILE 2>/dev/null || echo "" > $TEMP_CRON_FILE

# Remove any existing user sync cron jobs
sed -i '/sync:.*-data/d' $TEMP_CRON_FILE
sed -i '/sync:all-users/d' $TEMP_CRON_FILE

echo "" >> $TEMP_CRON_FILE
echo "# User Data Sync Jobs - Auto generated $(date)" >> $TEMP_CRON_FILE

# Add new cron jobs
echo "# Sync all users data every 30 minutes" >> $TEMP_CRON_FILE
echo "*/30 * * * * cd $LARAVEL_PATH && $PHP_PATH artisan sync:all-users >> storage/logs/user-sync.log 2>&1" >> $TEMP_CRON_FILE

echo "# Individual user sync jobs (backup)" >> $TEMP_CRON_FILE
echo "# Sync Ham data every hour at minute 5" >> $TEMP_CRON_FILE
echo "5 * * * * cd $LARAVEL_PATH && $PHP_PATH artisan sync:ham-data >> storage/logs/ham-sync.log 2>&1" >> $TEMP_CRON_FILE

echo "# Sync Kantapong data every hour at minute 15" >> $TEMP_CRON_FILE
echo "15 * * * * cd $LARAVEL_PATH && $PHP_PATH artisan sync:kantapong-data >> storage/logs/kantapong-sync.log 2>&1" >> $TEMP_CRON_FILE

echo "# Sync Janischa data every hour at minute 25" >> $TEMP_CRON_FILE
echo "25 * * * * cd $LARAVEL_PATH && $PHP_PATH artisan sync:janischa-data >> storage/logs/janischa-sync.log 2>&1" >> $TEMP_CRON_FILE

# Install the new cron jobs
crontab $TEMP_CRON_FILE

# Clean up
rm $TEMP_CRON_FILE

echo "Cron jobs installed successfully!"
echo ""
echo "Scheduled jobs:"
echo "- All users sync: Every 30 minutes"
echo "- Ham sync: Every hour at minute 5"
echo "- Kantapong sync: Every hour at minute 15"
echo "- Janischa sync: Every hour at minute 25"
echo ""
echo "Log files:"
echo "- All users: storage/logs/user-sync.log"
echo "- Ham: storage/logs/ham-sync.log"
echo "- Kantapong: storage/logs/kantapong-sync.log"
echo "- Janischa: storage/logs/janischa-sync.log"
echo ""
echo "To view current cron jobs: crontab -l"
echo "To remove all sync cron jobs, run: crontab -l | grep -v 'sync:.*-data' | grep -v 'sync:all-users' | crontab -"
echo ""
echo "You can test the sync manually with:"
echo "php artisan sync:all-users"
echo "php artisan sync:ham-data"
echo "php artisan sync:kantapong-data"
echo "php artisan sync:janischa-data" 