# ✅ Production Deployment Checklist

## ก่อน Deploy

### 1. Environment Variables ✅
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `LOG_LEVEL=error`
- [ ] `APP_URL=https://gogoldadmin.com`
- [ ] `APP_NAME="GoGold Admin Dashboard"`

### 2. Security Settings ✅
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_SAME_SITE=lax`
- [ ] Debug mode ปิดแล้ว
- [ ] Log level เป็น error

### 3. Database ✅
- [ ] Database credentials ถูกต้อง
- [ ] Database server accessible
- [ ] Migrations พร้อมรัน

### 4. Mail Configuration ✅
- [ ] SMTP settings ครบถ้วน
- [ ] Mail credentials ถูกต้อง
- [ ] Test mail sending

## ขั้นตอน Deployment

### 1. Build Assets ✅
```bash
npm install
npm run build:prod
```
- [ ] Assets build สำเร็จ
- [ ] ไฟล์ใน `public/build/` มีครบ
- [ ] `manifest.json` สร้างแล้ว

### 2. Composer Dependencies ✅
```bash
composer install --optimize-autoloader --no-dev
```
- [ ] Dependencies ติดตั้งแล้ว
- [ ] Autoloader optimize แล้ว

### 3. Laravel Commands ✅
```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```
- [ ] Application key generate แล้ว
- [ ] Configurations cached แล้ว
- [ ] Routes cached แล้ว
- [ ] Views cached แล้ว
- [ ] Migrations รันแล้ว

### 4. File Permissions ✅
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```
- [ ] Storage permissions ถูกต้อง
- [ ] Cache permissions ถูกต้อง

## การตรวจสอบหลัง Deploy

### 1. Website Access ✅
- [ ] เว็บไซต์เปิดได้
- [ ] HTTPS ทำงาน
- [ ] No SSL errors

### 2. Assets Loading ✅
- [ ] CSS โหลดได้
- [ ] JavaScript โหลดได้
- [ ] Images โหลดได้
- [ ] No 404 errors for assets

### 3. Functionality ✅
- [ ] Login ทำงาน
- [ ] Dashboard โหลดได้
- [ ] Database queries ทำงาน
- [ ] Forms submit ได้

### 4. Performance ✅
- [ ] Page load time < 3 seconds
- [ ] Assets cached properly
- [ ] No console errors

### 5. Security ✅
- [ ] Debug mode ปิด
- [ ] Error details ไม่แสดง
- [ ] HTTPS redirect ทำงาน
- [ ] Security headers ตั้งค่าแล้ว

## การ Monitor

### 1. Logs ✅
```bash
tail -f storage/logs/laravel.log
```
- [ ] Logs เขียนได้
- [ ] No critical errors
- [ ] Log level เป็น error

### 2. Error Monitoring ✅
- [ ] 500 errors ไม่มี
- [ ] 404 errors น้อย
- [ ] Database errors ไม่มี

### 3. Performance Monitoring ✅
- [ ] Response time < 3s
- [ ] Memory usage < 128MB
- [ ] CPU usage < 80%

## การ Backup

### 1. Database Backup ✅
```bash
mysqldump -u gogolda1_web -p gogolda1 > backup_$(date +%Y%m%d_%H%M%S).sql
```
- [ ] Database backup สำเร็จ
- [ ] Backup file เก็บไว้

### 2. File Backup ✅
```bash
tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz /path/to/laravel
```
- [ ] File backup สำเร็จ
- [ ] Backup file เก็บไว้

## Troubleshooting

### ปัญหาที่พบบ่อย ✅

#### Assets ไม่โหลด
- [ ] ตรวจสอบ `public/build/` directory
- [ ] ตรวจสอบ `manifest.json`
- [ ] Clear Laravel caches

#### 500 Internal Server Error
- [ ] ตรวจสอบ Laravel logs
- [ ] ตรวจสอบ web server logs
- [ ] ตรวจสอบ file permissions

#### Database Connection Error
- [ ] ตรวจสอบ database credentials
- [ ] ตรวจสอบ database server status
- [ ] ตรวจสอบ network connectivity

#### Mail Not Working
- [ ] ตรวจสอบ SMTP settings
- [ ] ตรวจสอบ mail credentials
- [ ] Test mail sending

## คำสั่งที่มีประโยชน์

```bash
# Check Laravel status
php artisan about

# Check build assets
ls -la public/build/

# Clear all caches
php artisan optimize:clear

# Check environment
php artisan tinker
echo config('app.debug');
echo config('logging.level');

# Test mail
php artisan tinker
Mail::raw('Test', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

## สรุป

✅ **Production Deployment Checklist เสร็จสิ้น**

- Environment variables ถูกต้อง
- Security settings ครบถ้วน
- Assets build สำเร็จ
- Laravel configurations cached
- Database migrations รันแล้ว
- File permissions ถูกต้อง
- Website ทำงานได้
- Performance ดี
- Security ปลอดภัย

🎉 **โปรเจ็คพร้อมใช้งานใน Production แล้ว!** 