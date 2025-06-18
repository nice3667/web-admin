# คู่มือการตั้งค่า Cron Job บน Shared Hosting

## 1. cPanel Cron Jobs

### ขั้นตอนการตั้งค่า:

1. **เข้าสู่ cPanel**
   - เข้าไปที่ cPanel ของ hosting
   - หา "Cron Jobs" ในเมนู

2. **สร้าง Cron Job ใหม่**
   - Common Settings: เลือก "Every 15 minutes"
   - Command: ใส่คำสั่งด้านล่าง

### คำสั่งสำหรับ cPanel:

```bash
# สำหรับ sync เฉพาะลูกค้าใหม่ทุก 15 นาที
cd /home/username/public_html && /usr/bin/php artisan clients:auto-sync --new-only --interval=15 >> /home/username/logs/client-sync.log 2>&1

# สำหรับ sync ข้อมูลทั้งหมดทุกวันเวลา 2:00 น.
cd /home/username/public_html && /usr/bin/php artisan clients:auto-sync --interval=1440 >> /home/username/logs/client-sync.log 2>&1
```

### ตั้งค่า Cron Schedule:

#### **ทุก 15 นาที (แนะนำ)**
```
Minute: */15
Hour: *
Day: *
Month: *
Weekday: *
```

#### **ทุก 30 นาที**
```
Minute: */30
Hour: *
Day: *
Month: *
Weekday: *
```

#### **ทุกวันเวลา 6:00 น.**
```
Minute: 0
Hour: 6
Day: *
Month: *
Weekday: *
```

## 2. Plesk Cron Jobs

### ขั้นตอนการตั้งค่า:

1. **เข้าสู่ Plesk**
   - เข้าไปที่ Plesk Control Panel
   - เลือก Domain
   - ไปที่ "Scheduled Tasks"

2. **สร้าง Task ใหม่**
   - Task Type: "Run a PHP script"
   - Script Path: `/artisan`
   - Arguments: `clients:auto-sync --new-only --interval=15`

## 3. การตรวจสอบ Path ของ PHP

### ตรวจสอบ PHP Path:
```bash
# ใน cPanel Terminal หรือ SSH
which php
# ผลลัพธ์: /usr/bin/php หรือ /opt/alt/php81/usr/bin/php
```

### ตรวจสอบ Project Path:
```bash
# ตรวจสอบ path ของ project
pwd
# ผลลัพธ์: /home/username/public_html หรือ /var/www/html
```

## 4. การตั้งค่า Log File

### สร้าง Log Directory:
```bash
# สร้างโฟลเดอร์สำหรับ log
mkdir -p /home/username/logs
chmod 755 /home/username/logs
```

### ตรวจสอบ Log:
```bash
# ดู log การทำงาน
tail -f /home/username/logs/client-sync.log
```

## 5. การทดสอบ Cron Job

### ทดสอบคำสั่ง:
```bash
# ทดสอบคำสั่งก่อนตั้ง cron
cd /home/username/public_html
/usr/bin/php artisan clients:auto-sync --new-only --interval=15
```

### ตรวจสอบผลลัพธ์:
```bash
# ดู log หลังจากรัน
cat /home/username/logs/client-sync.log
```

## 6. การแก้ไขปัญหา

### ปัญหาที่พบบ่อย:

#### 1. Permission Denied
```bash
# แก้ไข permission
chmod 755 /home/username/public_html/artisan
chmod -R 755 /home/username/public_html/storage
```

#### 2. PHP Path ไม่ถูกต้อง
```bash
# ตรวจสอบ PHP version และ path
php -v
which php
```

#### 3. Database Connection Error
```bash
# ตรวจสอบ .env file
cat .env | grep DB_
```

## 7. การ Monitor

### ตรวจสอบ Cron Job:
```bash
# ดู cron jobs ที่ตั้งไว้
crontab -l
```

### ตรวจสอบ Log:
```bash
# ดู log ล่าสุด
tail -20 /home/username/logs/client-sync.log
```

### ตรวจสอบสถานะ:
```bash
# ดูสถานะ sync
php artisan clients:stats
```

## 8. ตัวอย่างการตั้งค่าที่สมบูรณ์

### Cron Job สำหรับ Production:
```bash
# Sync เฉพาะลูกค้าใหม่ทุก 15 นาที
*/15 * * * * cd /home/username/public_html && /usr/bin/php artisan clients:auto-sync --new-only --interval=15 >> /home/username/logs/client-sync.log 2>&1

# Sync ข้อมูลทั้งหมดทุกวันเวลา 2:00 น.
0 2 * * * cd /home/username/public_html && /usr/bin/php artisan clients:auto-sync --interval=1440 >> /home/username/logs/client-sync.log 2>&1

# ตรวจสอบสถานะทุกวันเวลา 8:00 น.
0 8 * * * cd /home/username/public_html && /usr/bin/php artisan clients:stats >> /home/username/logs/client-stats.log 2>&1
```

### การตั้งค่าใน cPanel:
1. Common Settings: Custom
2. Minute: */15
3. Hour: *
4. Day: *
5. Month: *
6. Weekday: *
7. Command: `cd /home/username/public_html && /usr/bin/php artisan clients:auto-sync --new-only --interval=15 >> /home/username/logs/client-sync.log 2>&1`

## 9. การแจ้งเตือน

### สร้าง Email Notification:
```bash
# เพิ่ม email notification ใน cron job
*/15 * * * * cd /home/username/public_html && /usr/bin/php artisan clients:auto-sync --new-only --interval=15 >> /home/username/logs/client-sync.log 2>&1 && echo "Sync completed at $(date)" | mail -s "Client Sync Status" your-email@example.com
```

## 10. การ Backup

### สร้าง Backup Script:
```bash
#!/bin/bash
# backup-clients.sh
DATE=$(date +%Y%m%d_%H%M%S)
cd /home/username/public_html
php artisan clients:stats > /home/username/backups/client-stats-$DATE.log
```

### ตั้งค่า Backup Cron:
```bash
# Backup ทุกวันเวลา 1:00 น.
0 1 * * * /home/username/backup-clients.sh
``` 