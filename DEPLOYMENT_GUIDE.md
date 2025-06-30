# Production Deployment Guide

## การตั้งค่า Vite สำหรับ Production

### 1. การ Build Assets
```bash
# Install dependencies
npm install

# Build for production
npm run build:prod
```

### 2. การตั้งค่า Environment Variables
สร้างไฟล์ `.env` ใน production server:

```env
APP_NAME="Admin Dashboard"
APP_ENV=production
APP_KEY=base64:your-production-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Cache and Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail settings
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. การตั้งค่า Web Server

#### สำหรับ Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### สำหรับ Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/project/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4. การตั้งค่า Laravel

#### 1. Generate Application Key
```bash
php artisan key:generate
```

#### 2. Clear and Cache Configuration
```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
```

#### 3. Run Migrations
```bash
php artisan migrate --force
```

#### 4. Set Proper Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. การตั้งค่า Vite Assets

#### 1. Build Assets
```bash
npm run build:prod
```

#### 2. ตรวจสอบไฟล์ที่สร้างขึ้น
หลังจาก build แล้ว จะมีไฟล์ใน `public/build/`:
- `manifest.json` - สำหรับ Laravel Vite plugin
- `assets/` - ไฟล์ CSS, JS ที่ optimize แล้ว

#### 3. การใช้ Vite Assets ใน Blade
ในไฟล์ Blade templates ใช้:
```php
@vite(['resources/js/app.js', 'resources/css/app.css'])
```

### 6. การตั้งค่า Cache และ Optimization

#### 1. Optimize Composer Autoloader
```bash
composer install --optimize-autoloader --no-dev
```

#### 2. Cache Routes และ Config
```bash
php artisan route:cache
php artisan config:cache
php artisan view:cache
```

### 7. การตั้งค่า Security

#### 1. ตั้งค่า File Permissions
```bash
find /path/to/laravel -type f -exec chmod 644 {} \;
find /path/to/laravel -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

#### 2. ตั้งค่า .env
```env
APP_DEBUG=false
APP_ENV=production
```

### 8. การตั้งค่า SSL (HTTPS)

#### สำหรับ Let's Encrypt
```bash
sudo certbot --nginx -d your-domain.com
```

### 9. การตั้งค่า Queue (ถ้าต้องการ)

#### 1. ตั้งค่า Queue Driver
```env
QUEUE_CONNECTION=database
```

#### 2. สร้าง Queue Table
```bash
php artisan queue:table
php artisan migrate
```

#### 3. รัน Queue Worker
```bash
php artisan queue:work
```

### 10. การตั้งค่า Cron Jobs

#### 1. เปิด Crontab
```bash
crontab -e
```

#### 2. เพิ่ม Laravel Scheduler
```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### 11. การ Monitor และ Logs

#### 1. ตรวจสอบ Logs
```bash
tail -f storage/logs/laravel.log
```

#### 2. ตรวจสอบ Error Logs
```bash
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
```

### 12. การ Backup

#### 1. Backup Database
```bash
mysqldump -u username -p database_name > backup.sql
```

#### 2. Backup Files
```bash
tar -czf backup.tar.gz /path/to/laravel
```

## Troubleshooting

### ปัญหาที่พบบ่อย

1. **Assets ไม่โหลด**
   - ตรวจสอบว่า build แล้ว
   - ตรวจสอบ path ใน vite.config.js
   - ตรวจสอบ manifest.json

2. **Permission Denied**
   - ตั้งค่า permissions ให้ถูกต้อง
   - ตรวจสอบ ownership ของไฟล์

3. **Database Connection Error**
   - ตรวจสอบ database credentials
   - ตรวจสอบ database server status

4. **500 Internal Server Error**
   - ตรวจสอบ Laravel logs
   - ตรวจสอบ web server logs
   - ตรวจสอบ PHP error logs

### คำสั่งที่มีประโยชน์

```bash
# Clear all caches
php artisan optimize:clear

# Check Laravel status
php artisan about

# Check storage permissions
ls -la storage/

# Check build assets
ls -la public/build/
``` 