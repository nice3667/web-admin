# คู่มือการ Sync ข้อมูลแยกตาม User สำหรับระบบ Report

## ภาพรวม

ระบบได้รับการปรับปรุงให้แต่ละ Report ใช้ข้อมูลของ user ตัวเองโดยเฉพาะ:

- **Report** (เดิม): ใช้ข้อมูลของ `Janischa.trade@gmail.com`
- **Report1**: ใช้ข้อมูลของ `hamsftmo@gmail.com`  
- **Report2**: ใช้ข้อมูลของ `kantapong0592@gmail.com`

## โครงสร้างการจัดเก็บข้อมูล

### 1. Report (Janischa)
- **ตาราง**: `clients` (ตารางเดิม)
- **Service**: `ExnessAuthService`
- **Command**: `sync:report-data`

### 2. Report1 (Ham)
- **ตาราง**: `ham_clients` (สร้างอัตโนมัติ)
- **Service**: `HamExnessAuthService`
- **Command**: `sync:report1-data`

### 3. Report2 (Kantapong)
- **ตาราง**: `kantapong_clients` (สร้างอัตโนมัติ)
- **Service**: `KantapongExnessAuthService`
- **Command**: `sync:report2-data`

## การใช้งาน Commands

### 1. Sync ข้อมูล Report (Janischa)

```bash
# Sync เฉพาะลูกค้าใหม่
php artisan sync:report-data --new-only

# Sync ข้อมูลทั้งหมด
php artisan sync:report-data

# รัน daemon mode (sync ทุก 30 นาที)
php artisan sync:report-data --daemon --interval=30 --new-only

# รัน daemon mode พร้อมจำกัดจำนวนครั้ง
php artisan sync:report-data --daemon --interval=15 --new-only --max-runs=100
```

### 2. Sync ข้อมูล Report1 (Ham)

```bash
# Sync เฉพาะลูกค้าใหม่
php artisan sync:report1-data --new-only

# Sync ข้อมูลทั้งหมด
php artisan sync:report1-data

# รัน daemon mode
php artisan sync:report1-data --daemon --interval=30 --new-only
```

### 3. Sync ข้อมูล Report2 (Kantapong)

```bash
# Sync เฉพาะลูกค้าใหม่
php artisan sync:report2-data --new-only

# Sync ข้อมูลทั้งหมด
php artisan sync:report2-data

# รัน daemon mode
php artisan sync:report2-data --daemon --interval=30 --new-only
```

## การทดสอบระบบ

### ทดสอบทั้งหมดพร้อมกัน
```bash
php sync_test.php
```

### ทดสอบแยกรายตัว
```bash
# ทดสอบ Report (Janischa)
php artisan sync:report-data --new-only

# ทดสอบ Report1 (Ham)
php artisan sync:report1-data --new-only

# ทดสอบ Report2 (Kantapong)
php artisan sync:report2-data --new-only
```

## การตั้งค่า Cron Jobs

### สำหรับ Production (แยกตาม user)

```bash
# เปิด crontab
crontab -e

# เพิ่มบรรทัดเหล่านี้
# Sync Report (Janischa) ทุก 15 นาที
*/15 * * * * cd /path/to/project && php artisan sync:report-data --new-only >> /var/log/report-sync.log 2>&1

# Sync Report1 (Ham) ทุก 15 นาที
*/15 * * * * cd /path/to/project && php artisan sync:report1-data --new-only >> /var/log/report1-sync.log 2>&1

# Sync Report2 (Kantapong) ทุก 15 นาที
*/15 * * * * cd /path/to/project && php artisan sync:report2-data --new-only >> /var/log/report2-sync.log 2>&1

# Sync ข้อมูลทั้งหมดทุกวันเวลา 2:00 น.
0 2 * * * cd /path/to/project && php artisan sync:report-data >> /var/log/report-full-sync.log 2>&1
0 2 * * * cd /path/to/project && php artisan sync:report1-data >> /var/log/report1-full-sync.log 2>&1
0 2 * * * cd /path/to/project && php artisan sync:report2-data >> /var/log/report2-full-sync.log 2>&1
```

## การใช้งาน Daemon Mode

### วิธีที่ 1: ใช้ screen (แนะนำ)

```bash
# สร้าง screen sessions แยกกัน
screen -S report-sync
php artisan sync:report-data --daemon --interval=15 --new-only
# กด Ctrl+A, D เพื่อออก

screen -S report1-sync
php artisan sync:report1-data --daemon --interval=15 --new-only
# กด Ctrl+A, D เพื่อออก

screen -S report2-sync
php artisan sync:report2-data --daemon --interval=15 --new-only
# กด Ctrl+A, D เพื่อออก

# ดู screen sessions
screen -ls

# กลับเข้า screen
screen -r report-sync
screen -r report1-sync
screen -r report2-sync
```

### วิธีที่ 2: ใช้ systemd services

```bash
# สร้าง service files
sudo nano /etc/systemd/system/report-sync.service
sudo nano /etc/systemd/system/report1-sync.service
sudo nano /etc/systemd/system/report2-sync.service
```

#### report-sync.service (Janischa)
```ini
[Unit]
Description=Report Data Sync Service (Janischa)
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php artisan sync:report-data --daemon --interval=15 --new-only
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

#### report1-sync.service (Ham)
```ini
[Unit]
Description=Report1 Data Sync Service (Ham)
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php artisan sync:report1-data --daemon --interval=15 --new-only
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

#### report2-sync.service (Kantapong)
```ini
[Unit]
Description=Report2 Data Sync Service (Kantapong)
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php artisan sync:report2-data --daemon --interval=15 --new-only
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
# เริ่มต้น services
sudo systemctl enable report-sync
sudo systemctl enable report1-sync
sudo systemctl enable report2-sync

sudo systemctl start report-sync
sudo systemctl start report1-sync
sudo systemctl start report2-sync

# ตรวจสอบสถานะ
sudo systemctl status report-sync
sudo systemctl status report1-sync
sudo systemctl status report2-sync
```

## การ Monitor และ Debug

### ดู Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log | grep "Report.*Sync"

# Sync logs แยกรายตัว
tail -f /var/log/report-sync.log
tail -f /var/log/report1-sync.log
tail -f /var/log/report2-sync.log

# Systemd logs
sudo journalctl -u report-sync -f
sudo journalctl -u report1-sync -f
sudo journalctl -u report2-sync -f
```

### ตรวจสอบข้อมูลในฐานข้อมูล

```sql
-- ตรวจสอบจำนวนข้อมูลในแต่ละตาราง
SELECT 'clients' as table_name, COUNT(*) as count FROM clients
UNION ALL
SELECT 'ham_clients' as table_name, COUNT(*) as count FROM ham_clients
UNION ALL
SELECT 'kantapong_clients' as table_name, COUNT(*) as count FROM kantapong_clients;

-- ตรวจสอบข้อมูลล่าสุด
SELECT 'clients' as table_name, MAX(updated_at) as last_update FROM clients
UNION ALL
SELECT 'ham_clients' as table_name, MAX(updated_at) as last_update FROM ham_clients
UNION ALL
SELECT 'kantapong_clients' as table_name, MAX(updated_at) as last_update FROM kantapong_clients;
```

### ตรวจสอบสถานะ Process

```bash
# ดู daemon processes
ps aux | grep "sync:report"

# ดู systemd services
sudo systemctl status report-sync report1-sync report2-sync
```

## การแก้ไขปัญหา

### 1. Daemon หยุดทำงาน

```bash
# ตรวจสอบ logs
tail -f storage/logs/laravel.log

# รีสตาร์ท daemon
pkill -f "sync:report"
# จากนั้นรันใหม่
php artisan sync:report-data --daemon --interval=15 --new-only
```

### 2. ตารางไม่ถูกสร้าง

ตาราง `ham_clients` และ `kantapong_clients` จะถูกสร้างอัตโนมัติเมื่อรัน command ครั้งแรก ถ้าไม่ถูกสร้าง:

```bash
# รัน command เพื่อสร้างตาราง
php artisan sync:report1-data --new-only
php artisan sync:report2-data --new-only
```

### 3. API ไม่ตอบสนอง

```bash
# ตรวจสอบการเชื่อมต่อ API
curl -X GET http://localhost:8000/admin/reports1/test-connection
curl -X GET http://localhost:8000/admin/reports2/test-connection
```

## การอัพเดทข้อมูล

### Manual Sync ทั้งหมด

```bash
# อัพเดทข้อมูลทั้งหมดพร้อมกัน
php artisan sync:report-data &
php artisan sync:report1-data &
php artisan sync:report2-data &
wait
echo "All syncs completed"
```

### Sync เฉพาะลูกค้าใหม่

```bash
# อัพเดทเฉพาะลูกค้าใหม่ทั้งหมด
php artisan sync:report-data --new-only &
php artisan sync:report1-data --new-only &
php artisan sync:report2-data --new-only &
wait
echo "New client syncs completed"
```

## ข้อมูลสำคัญ

### Data Source Priority

1. **Report1 & Report2**: 
   - ลำดับ 1: Database table (ham_clients/kantapong_clients)
   - ลำดับ 2: Exness API (fallback)

2. **Report** (เดิม):
   - ลำดับ 1: Database table (clients) 
   - ลำดับ 2: Exness API (fallback)

### User Accounts

- **Report**: `Janischa.trade@gmail.com` / `Janis@2025`
- **Report1**: `hamsftmo@gmail.com` / `Ham@240446`
- **Report2**: `kantapong0592@gmail.com` / `Kantapong.0592z`

### Performance

- แต่ละ command จะสร้างตารางและ index อัตโนมัติ
- ข้อมูลจะถูก cache เพื่อประสิทธิภาพ
- API fallback จะทำงานเมื่อไม่มีข้อมูลในตาราง

ระบบนี้ทำให้แต่ละ Report มีข้อมูลที่เป็นอิสระและอัพเดทตามเวลาที่กำหนด โดยไม่รบกวนกัน! 