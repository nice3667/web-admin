#!/bin/bash

echo "=== Stopping Sync Daemons for All Reports ==="

# Function to stop a daemon
stop_daemon() {
    local name=$1
    
    echo "Stopping $name daemon..."
    
    if screen -list | grep -q "$name"; then
        screen -S "$name" -X quit
        echo "  ✓ $name daemon stopped"
    else
        echo "  ⚠ $name daemon was not running"
    fi
}

# Stop all sync daemons
stop_daemon "report-sync"
stop_daemon "report1-sync" 
stop_daemon "report2-sync"

echo ""
echo "=== Remaining Screen Sessions ==="
screen -list 2>/dev/null || echo "No screen sessions running"

echo ""
echo "=== All Sync Daemons Stopped ===" 