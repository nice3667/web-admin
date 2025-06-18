# คู่มือการใช้งานระบบ Sync อัตโนมัติและเรียลไทม์

## ภาพรวม

ระบบนี้รองรับการ sync ข้อมูลลูกค้าอัตโนมัติและแบบเรียลไทม์ ผ่านหลายวิธี:

1. **Cron Job** - อัตโนมัติตามเวลา
2. **Daemon Mode** - ทำงานต่อเนื่อง
3. **Queue Jobs** - Background processing
4. **Real-time API** - ควบคุมผ่าน API
5. **WebSocket** - อัพเดตเรียลไทม์

## 1. ระบบ Cron Job (อัตโนมัติตามเวลา)

### การตั้งค่า Cron Job

#### วิธีที่ 1: ใช้ Artisan Command
```bash
# Sync เฉพาะลูกค้าใหม่ทุก 30 นาที
*/30 * * * * cd /path/to/project && php artisan clients:auto-sync --new-only --interval=30

# Sync ข้อมูลทั้งหมดทุกวันเวลา 2:00 น.
0 2 * * * cd /path/to/project && php artisan clients:auto-sync --interval=1440

# Sync เฉพาะลูกค้าใหม่ทุกวันเวลา 6:00 น.
0 6 * * * cd /path/to/project && php artisan clients:auto-sync --new-only --interval=1440
```

#### วิธีที่ 2: ใช้ Daemon Mode
```bash
# รัน daemon ที่ sync ทุก 15 นาที
php artisan clients:auto-sync --daemon --interval=15 --new-only

# รัน daemon ที่ sync ทุก 1 ชั่วโมง
php artisan clients:auto-sync --daemon --interval=60 --new-only

# รัน daemon จำกัดจำนวนครั้ง
php artisan clients:auto-sync --daemon --interval=30 --new-only --max-runs=100
```

### การตั้งค่าใน crontab
```bash
# เปิด crontab
crontab -e

# เพิ่มบรรทัดเหล่านี้
# Sync เฉพาะลูกค้าใหม่ทุก 30 นาที
*/30 * * * * cd /path/to/your/project && php artisan clients:auto-sync --new-only --interval=30 >> /var/log/client-sync.log 2>&1

# Sync ข้อมูลทั้งหมดทุกวันเวลา 2:00 น.
0 2 * * * cd /path/to/your/project && php artisan clients:auto-sync --interval=1440 >> /var/log/client-sync.log 2>&1
```

## 2. ระบบ Daemon Mode (ทำงานต่อเนื่อง)

### การใช้งาน Daemon Mode

```bash
# เริ่มต้น daemon mode
php artisan clients:auto-sync --daemon --interval=30 --new-only

# เริ่มต้น daemon mode พร้อมจำกัดจำนวนครั้ง
php artisan clients:auto-sync --daemon --interval=15 --new-only --max-runs=200

# เริ่มต้น daemon mode สำหรับ sync ข้อมูลทั้งหมด
php artisan clients:auto-sync --daemon --interval=60
```

### การจัดการ Daemon Process

#### ใช้ screen (แนะนำ)
```bash
# สร้าง screen session
screen -S client-sync

# รัน daemon
php artisan clients:auto-sync --daemon --interval=30 --new-only

# ออกจาก screen (Ctrl+A, D)
# กลับเข้า screen
screen -r client-sync
```

#### ใช้ nohup
```bash
# รันใน background
nohup php artisan clients:auto-sync --daemon --interval=30 --new-only > /var/log/client-sync.log 2>&1 &

# ดู process
ps aux | grep "clients:auto-sync"

# หยุด process
kill -TERM [process_id]
```

#### ใช้ systemd service
```bash
# สร้างไฟล์ service
sudo nano /etc/systemd/system/client-sync.service

[Unit]
Description=Client Auto Sync Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php artisan clients:auto-sync --daemon --interval=30 --new-only
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target

# เริ่มต้น service
sudo systemctl enable client-sync
sudo systemctl start client-sync
sudo systemctl status client-sync
```

## 3. ระบบ Queue Jobs (Background Processing)

### การตั้งค่า Queue

#### 1. ตั้งค่า Queue Driver
ใน `.env`:
```env
QUEUE_CONNECTION=database
```

#### 2. สร้าง Queue Table
```bash
php artisan queue:table
php artisan migrate
```

#### 3. เริ่มต้น Queue Worker
```bash
# รัน queue worker
php artisan queue:work

# รัน queue worker ใน background
nohup php artisan queue:work > /var/log/queue.log 2>&1 &

# รัน queue worker พร้อม retry
php artisan queue:work --tries=3 --timeout=300
```

### การใช้งาน Queue Jobs

```php
// Dispatch job manually
use App\Jobs\AutoSyncClientsJob;

// Dispatch job for new clients sync
AutoSyncClientsJob::dispatch(true, 30);

// Dispatch job for full sync
AutoSyncClientsJob::dispatch(false, 60);

// Dispatch job with delay
AutoSyncClientsJob::dispatch(true, 30)->delay(now()->addMinutes(5));
```

## 4. ระบบ Real-time API

### API Endpoints

#### เริ่มต้นการ Monitor
```http
POST /api/realtime-sync/start
Content-Type: application/json

{
    "interval": 30,
    "new_only": true
}
```

#### หยุดการ Monitor
```http
POST /api/realtime-sync/stop
```

#### ดูสถานะ
```http
GET /api/realtime-sync/status
```

#### Trigger Sync แบบ Manual
```http
POST /api/realtime-sync/trigger
Content-Type: application/json

{
    "new_only": true
}
```

#### ดูประวัติการ Sync
```http
GET /api/realtime-sync/history
```

### ตัวอย่างการใช้งาน JavaScript

```javascript
// เริ่มต้น real-time sync
async function startRealtimeSync(interval = 30, newOnly = true) {
    const response = await fetch('/api/realtime-sync/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            interval: interval,
            new_only: newOnly
        })
    });
    
    const result = await response.json();
    console.log('Realtime sync started:', result);
}

// ดูสถานะ
async function getSyncStatus() {
    const response = await fetch('/api/realtime-sync/status');
    const result = await response.json();
    console.log('Sync status:', result);
    return result;
}

// Trigger sync manual
async function triggerSync(newOnly = true) {
    const response = await fetch('/api/realtime-sync/trigger', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            new_only: newOnly
        })
    });
    
    const result = await response.json();
    console.log('Sync triggered:', result);
}

// ดูประวัติ
async function getSyncHistory() {
    const response = await fetch('/api/realtime-sync/history');
    const result = await response.json();
    console.log('Sync history:', result);
    return result;
}

// ใช้งาน
startRealtimeSync(15, true); // Sync ทุก 15 นาที เฉพาะลูกค้าใหม่
```

## 5. ระบบ WebSocket (เรียลไทม์)

### การตั้งค่า WebSocket Server

#### ใช้ Laravel WebSockets
```bash
# ติดตั้ง Laravel WebSockets
composer require beyondcode/laravel-websockets

# Publish config
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"

# Migrate
php artisan migrate

# เริ่มต้น WebSocket server
php artisan websockets:serve
```

#### การใช้งาน WebSocket

```javascript
// เชื่อมต่อ WebSocket
const ws = new WebSocket('ws://localhost:6001');

ws.onopen = function() {
    console.log('WebSocket connected');
    
    // Subscribe to sync events
    ws.send(JSON.stringify({
        event: 'subscribe',
        channel: 'client-sync'
    }));
};

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    
    if (data.event === 'client-sync') {
        console.log('Sync update:', data.data);
        
        // อัพเดต UI
        updateSyncStatus(data.data);
    }
};

ws.onclose = function() {
    console.log('WebSocket disconnected');
};
```

## 6. การ Monitor และ Debug

### การดู Logs

```bash
# ดู Laravel logs
tail -f storage/logs/laravel.log | grep "AutoSyncClientsJob"

# ดู sync logs เฉพาะ
tail -f /var/log/client-sync.log

# ดู queue logs
tail -f /var/log/queue.log
```

### การดูสถานะ

```bash
# ดูสถานะ sync
php artisan clients:sync-stats

# ดูสถานะ queue
php artisan queue:work --once

# ดูสถานะ cache
php artisan cache:table
```

### การ Debug

```bash
# ดูข้อมูล API
php artisan clients:sync --show-api

# ดูข้อมูล Database
curl http://localhost:8000/api/clients/debug-db

# ดูสถานะ real-time sync
curl http://localhost:8000/api/realtime-sync/status
```

## 7. การตั้งค่าที่แนะนำ

### สำหรับ Production

#### 1. Cron Job (แนะนำ)
```bash
# Sync เฉพาะลูกค้าใหม่ทุก 15 นาที
*/15 * * * * cd /path/to/project && php artisan clients:auto-sync --new-only --interval=15 >> /var/log/client-sync.log 2>&1

# Sync ข้อมูลทั้งหมดทุกวันเวลา 2:00 น.
0 2 * * * cd /path/to/project && php artisan clients:auto-sync --interval=1440 >> /var/log/client-sync.log 2>&1
```

#### 2. Systemd Service
```bash
# สร้าง service file
sudo nano /etc/systemd/system/client-sync.service

[Unit]
Description=Client Auto Sync Service
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php artisan clients:auto-sync --daemon --interval=15 --new-only
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target

# เริ่มต้น service
sudo systemctl enable client-sync
sudo systemctl start client-sync
```

#### 3. Queue Worker
```bash
# สร้าง queue worker service
sudo nano /etc/systemd/system/queue-worker.service

[Unit]
Description=Queue Worker Service
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php artisan queue:work --tries=3 --timeout=300
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target

# เริ่มต้น service
sudo systemctl enable queue-worker
sudo systemctl start queue-worker
```

### สำหรับ Development

```bash
# รัน daemon mode ใน terminal
php artisan clients:auto-sync --daemon --interval=5 --new-only

# รัน queue worker ใน terminal อีกตัว
php artisan queue:work

# ใช้ API สำหรับทดสอบ
curl -X POST http://localhost:8000/api/realtime-sync/start \
  -H "Content-Type: application/json" \
  -d '{"interval": 10, "new_only": true}'
```

## 8. การแก้ไขปัญหา

### ปัญหาที่พบบ่อย

#### 1. Daemon หยุดทำงาน
```bash
# ตรวจสอบ logs
tail -f storage/logs/laravel.log

# ตรวจสอบ process
ps aux | grep "clients:auto-sync"

# รีสตาร์ท daemon
pkill -f "clients:auto-sync"
php artisan clients:auto-sync --daemon --interval=30 --new-only
```

#### 2. Queue Jobs ไม่ทำงาน
```bash
# ตรวจสอบ queue table
php artisan queue:table

# ตรวจสอบ queue worker
php artisan queue:work --once

# ล้าง queue
php artisan queue:flush
```

#### 3. API ไม่ตอบสนอง
```bash
# ตรวจสอบ Laravel server
php artisan serve

# ตรวจสอบ logs
tail -f storage/logs/laravel.log

# ตรวจสอบ cache
php artisan cache:clear
```

## 9. การ Monitor และ Alert

### การตั้งค่า Alert

```php
// ใน AutoSyncClientsJob.php
protected function sendFailureNotification(\Throwable $exception): void
{
    // ส่ง email
    Mail::to('admin@example.com')->send(new SyncFailureMail($exception));
    
    // ส่ง Slack notification
    // Slack::to('#alerts')->send('Client sync failed: ' . $exception->getMessage());
    
    // ส่ง SMS
    // SMS::send('+1234567890', 'Client sync failed');
}
```

### การ Monitor

```bash
# สร้าง monitoring script
nano /usr/local/bin/monitor-client-sync.sh

#!/bin/bash
if ! pgrep -f "clients:auto-sync" > /dev/null; then
    echo "Client sync daemon is not running!"
    # ส่ง alert
    curl -X POST http://localhost:8000/api/realtime-sync/start \
      -H "Content-Type: application/json" \
      -d '{"interval": 30, "new_only": true}'
fi

# ทำให้ executable
chmod +x /usr/local/bin/monitor-client-sync.sh

# เพิ่มใน crontab
*/5 * * * * /usr/local/bin/monitor-client-sync.sh
```

## 10. สรุป

ระบบ sync อัตโนมัติและเรียลไทม์มีหลายระดับ:

1. **Cron Job** - เหมาะสำหรับ sync ตามเวลา
2. **Daemon Mode** - เหมาะสำหรับ sync ต่อเนื่อง
3. **Queue Jobs** - เหมาะสำหรับ background processing
4. **Real-time API** - เหมาะสำหรับควบคุมผ่าน web interface
5. **WebSocket** - เหมาะสำหรับอัพเดตเรียลไทม์

เลือกใช้ตามความเหมาะสมของระบบและความต้องการของคุณ! 