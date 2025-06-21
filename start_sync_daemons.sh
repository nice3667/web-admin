#!/bin/bash

echo "=== Starting Sync Daemons for All Reports ==="

# Check if screen is available
if ! command -v screen &> /dev/null; then
    echo "Error: screen is not installed. Please install screen first:"
    echo "  Ubuntu/Debian: sudo apt-get install screen"
    echo "  CentOS/RHEL: sudo yum install screen"
    echo "  macOS: brew install screen"
    exit 1
fi

# Function to start a daemon in screen
start_daemon() {
    local name=$1
    local command=$2
    
    echo "Starting $name daemon..."
    
    # Kill existing screen session if it exists
    screen -S "$name" -X quit 2>/dev/null || true
    
    # Start new screen session
    screen -dmS "$name" bash -c "$command"
    
    # Check if session was created
    if screen -list | grep -q "$name"; then
        echo "  ✓ $name daemon started successfully"
    else
        echo "  ✗ Failed to start $name daemon"
    fi
}

# Start all sync daemons
start_daemon "report-sync" "php artisan sync:report-data --daemon --interval=15 --new-only"
start_daemon "report1-sync" "php artisan sync:report1-data --daemon --interval=15 --new-only"
start_daemon "report2-sync" "php artisan sync:report2-data --daemon --interval=15 --new-only"

echo ""
echo "=== Active Screen Sessions ==="
screen -list

echo ""
echo "=== Daemon Management Commands ==="
echo "View logs:"
echo "  screen -r report-sync    # View Report (Janischa) daemon"
echo "  screen -r report1-sync   # View Report1 (Ham) daemon"
echo "  screen -r report2-sync   # View Report2 (Kantapong) daemon"
echo ""
echo "Exit from screen: Ctrl+A, D"
echo ""
echo "Stop all daemons:"
echo "  ./stop_sync_daemons.sh"
echo ""
echo "=== All Sync Daemons Started ===" 