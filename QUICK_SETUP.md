# Quick Setup Guide สำหรับ Production

## ขั้นตอนการ Deploy อย่างรวดเร็ว

### 1. Build Assets
```bash
npm install
npm run build:prod
```

### 2. ตั้งค่า Environment
สร้างไฟล์ `.env` ใน production server และตั้งค่า:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### 3. รันคำสั่ง Laravel
```bash
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

### 4. ตั้งค่า Web Server
- Apache: ใช้ `.htaccess` ที่มีอยู่แล้ว
- Nginx: ดูตัวอย่างใน `DEPLOYMENT_GUIDE.md`

### 5. ตรวจสอบ
- เปิดเว็บไซต์
- ตรวจสอบว่า assets โหลดได้
- ตรวจสอบ logs ถ้ามีปัญหา

## ปัญหาที่พบบ่อย

### Assets ไม่โหลด
```bash
# ตรวจสอบว่า build แล้ว
ls -la public/build/

# ตรวจสอบ manifest.json
cat public/build/manifest.json
```

### 500 Error
```bash
# ตรวจสอบ Laravel logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan optimize:clear
```

### Permission Issues
```bash
# ตั้งค่า permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## คำสั่งที่มีประโยชน์

```bash
# Check Laravel status
php artisan about

# Check build assets
ls -la public/build/

# Clear all caches
php artisan optimize:clear

# Check storage permissions
ls -la storage/
```

## การตั้งค่า Vite ที่สำคัญ

ไฟล์ `vite.config.js` ได้รับการปรับปรุงแล้วเพื่อ:
- สร้าง manifest.json สำหรับ Laravel
- Optimize assets สำหรับ production
- ตั้งค่า base path ให้ถูกต้อง
- ใช้ terser สำหรับ minification

## การใช้ Windows Deployment Script

รันไฟล์ `deploy.bat` เพื่อ deploy อัตโนมัติ:
```bash
deploy.bat
```

## การตั้งค่า SSL

สำหรับ Let's Encrypt:
```bash
sudo certbot --nginx -d your-domain.com
```

## การ Monitor

ตรวจสอบ logs:
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
``` 
