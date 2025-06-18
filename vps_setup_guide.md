# คู่มือการตั้งค่า Auto Sync บน VPS/Dedicated Server

## 1. Systemd Service (แนะนำ)

### สร้าง Service File:

```bash
# สร้าง service file
sudo nano /etc/systemd/system/client-sync.service
```

### เนื้อหา Service File:

```ini
[Unit]
Description=Client Auto Sync Service
After=network.target mysql.service
Wants=mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/html/your-project
ExecStart=/usr/bin/php artisan clients:auto-sync --daemon --interval=15 --new-only
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
Environment=APP_ENV=production

# Security settings
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/www/html/your-project/storage

[Install]
WantedBy=multi-user.target
```

### เริ่มต้น Service:

```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable service (เริ่มต้นอัตโนมัติเมื่อ boot)
sudo systemctl enable client-sync

# Start service
sudo systemctl start client-sync

# ตรวจสอบสถานะ
sudo systemctl status client-sync
```

### การจัดการ Service:

```bash
# หยุด service
sudo systemctl stop client-sync

# รีสตาร์ท service
sudo systemctl restart client-sync

# ดู logs
sudo journalctl -u client-sync -f

# ดู logs ล่าสุด
sudo journalctl -u client-sync -n 50
```

## 2. Supervisor (ทางเลือกที่ 2)

### ติดตั้ง Supervisor:

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install supervisor

# CentOS/RHEL
sudo yum install supervisor
sudo systemctl enable supervisord
sudo systemctl start supervisord
```

### สร้าง Config File:

```bash
sudo nano /etc/supervisor/conf.d/client-sync.conf
```

### เนื้อหา Config:

```ini
[program:client-sync]
command=/usr/bin/php artisan clients:auto-sync --daemon --interval=15 --new-only
directory=/var/www/html/your-project
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/client-sync.log
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=10
environment=APP_ENV=production
```

### เริ่มต้น Supervisor:

```bash
# Reload config
sudo supervisorctl reread
sudo supervisorctl update

# Start program
sudo supervisorctl start client-sync

# ตรวจสอบสถานะ
sudo supervisorctl status client-sync

# ดู logs
sudo tail -f /var/log/client-sync.log
```

## 3. Cron Job (ทางเลือกที่ 3)

### ตั้งค่า Crontab:

```bash
# เปิด crontab
crontab -e

# เพิ่มบรรทัดเหล่านี้
# Sync เฉพาะลูกค้าใหม่ทุก 15 นาที
*/15 * * * * cd /var/www/html/your-project && /usr/bin/php artisan clients:auto-sync --new-only --interval=15 >> /var/log/client-sync.log 2>&1

# Sync ข้อมูลทั้งหมดทุกวันเวลา 2:00 น.
0 2 * * * cd /var/www/html/your-project && /usr/bin/php artisan clients:auto-sync --interval=1440 >> /var/log/client-sync.log 2>&1

# ตรวจสอบสถานะทุกวันเวลา 8:00 น.
0 8 * * * cd /var/www/html/your-project && /usr/bin/php artisan clients:stats >> /var/log/client-stats.log 2>&1
```

### ตรวจสอบ Cron Jobs:

```bash
# ดู cron jobs ที่ตั้งไว้
crontab -l

# ตรวจสอบ cron service
sudo systemctl status cron
```

## 4. การตั้งค่า Log Rotation

### สร้าง Logrotate Config:

```bash
sudo nano /etc/logrotate.d/client-sync
```

### เนื้อหา Logrotate:

```
/var/log/client-sync.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload client-sync
    endscript
}
```

## 5. การตั้งค่า Monitoring

### สร้าง Monitoring Script:

```bash
sudo nano /usr/local/bin/monitor-client-sync.sh
```

### เนื้อหา Script:

```bash
#!/bin/bash

# ตรวจสอบ service status
if ! systemctl is-active --quiet client-sync; then
    echo "Client sync service is not running!"
    
    # รีสตาร์ท service
    systemctl restart client-sync
    
    # ส่ง notification
    echo "Client sync service restarted at $(date)" | mail -s "Client Sync Alert" admin@example.com
fi

# ตรวจสอบ log file size
LOG_SIZE=$(du -m /var/log/client-sync.log | cut -f1)
if [ $LOG_SIZE -gt 100 ]; then
    echo "Client sync log file is too large: ${LOG_SIZE}MB"
    # Rotate log
    logrotate /etc/logrotate.d/client-sync
fi
```

### ทำให้ Script Executable:

```bash
chmod +x /usr/local/bin/monitor-client-sync.sh
```

### ตั้งค่า Monitoring Cron:

```bash
# ตรวจสอบทุก 5 นาที
*/5 * * * * /usr/local/bin/monitor-client-sync.sh
```

## 6. การตั้งค่า Backup

### สร้าง Backup Script:

```bash
sudo nano /usr/local/bin/backup-clients.sh
```

### เนื้อหา Backup Script:

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/client-sync"
PROJECT_DIR="/var/www/html/your-project"

# สร้าง backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u username -p'password' database_name clients > $BACKUP_DIR/clients_$DATE.sql

# Backup stats
cd $PROJECT_DIR
php artisan clients:stats > $BACKUP_DIR/stats_$DATE.log

# Compress backup
tar -czf $BACKUP_DIR/backup_$DATE.tar.gz $BACKUP_DIR/clients_$DATE.sql $BACKUP_DIR/stats_$DATE.log

# ลบไฟล์เก่า (เก็บไว้ 7 วัน)
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $BACKUP_DIR/backup_$DATE.tar.gz"
```

### ตั้งค่า Backup Cron:

```bash
# Backup ทุกวันเวลา 1:00 น.
0 1 * * * /usr/local/bin/backup-clients.sh
```

## 7. การตั้งค่า Security

### ตั้งค่า Firewall:

```bash
# อนุญาตเฉพาะ port ที่จำเป็น
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### ตั้งค่า File Permissions:

```bash
# ตั้งค่า permission สำหรับ Laravel
sudo chown -R www-data:www-data /var/www/html/your-project
sudo chmod -R 755 /var/www/html/your-project
sudo chmod -R 775 /var/www/html/your-project/storage
sudo chmod -R 775 /var/www/html/your-project/bootstrap/cache
```

## 8. การตั้งค่า Performance

### ตั้งค่า PHP-FPM:

```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

### ปรับแต่ง Settings:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### รีสตาร์ท PHP-FPM:

```bash
sudo systemctl restart php8.1-fpm
```

## 9. การตั้งค่า SSL (ถ้าจำเป็น)

### ติดตั้ง Certbot:

```bash
sudo apt install certbot python3-certbot-nginx
```

### สร้าง SSL Certificate:

```bash
sudo certbot --nginx -d yourdomain.com
```

## 10. การตรวจสอบและ Troubleshooting

### ตรวจสอบ Service Status:

```bash
# ตรวจสอบ service
sudo systemctl status client-sync

# ตรวจสอบ logs
sudo journalctl -u client-sync -f

# ตรวจสอบ process
ps aux | grep "clients:auto-sync"
```

### ตรวจสอบ Database Connection:

```bash
# ทดสอบ database connection
cd /var/www/html/your-project
php artisan tinker
>>> DB::connection()->getPdo();
```

### ตรวจสอบ Disk Space:

```bash
# ตรวจสอบ disk space
df -h

# ตรวจสอบ log file size
du -sh /var/log/client-sync.log
```

## 11. การตั้งค่าที่แนะนำ

### สำหรับ Production Server:

1. **ใช้ Systemd Service** - เสถียรที่สุด
2. **ตั้งค่า Log Rotation** - ป้องกัน disk full
3. **ตั้งค่า Monitoring** - ตรวจสอบสถานะอัตโนมัติ
4. **ตั้งค่า Backup** - สำรองข้อมูลอัตโนมัติ
5. **ตั้งค่า Security** - ป้องกันการโจมตี

### คำสั่งเริ่มต้นที่แนะนำ:

```bash
# 1. สร้าง service
sudo systemctl enable client-sync
sudo systemctl start client-sync

# 2. ตั้งค่า monitoring
sudo crontab -e
# เพิ่ม: */5 * * * * /usr/local/bin/monitor-client-sync.sh

# 3. ตั้งค่า backup
sudo crontab -e
# เพิ่ม: 0 1 * * * /usr/local/bin/backup-clients.sh

# 4. ตรวจสอบสถานะ
sudo systemctl status client-sync
sudo journalctl -u client-sync -f
``` 