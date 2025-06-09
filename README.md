# Laravel Vue Admin Panel

# แก้ไขไฟล์ .env ให้เชื่อมต่อฐานข้อมูล

# เปิด .env แล้วตั้งค่าดังนี้:

DB_DATABASE=admin_app
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password

# รันคำสั่ง publish และ seed ข้อมูลเบื้องต้น

php artisan vendor:publish --tag=admin-core
php artisan migrate --seed --seeder=AdminCoreSeeder

# สร้าง symbolic link สำหรับ storage

php artisan storage:link

# ติดตั้ง front-end dependencies

.
npm install
npm run dev

# เริ่มเซิร์ฟเวอร์ Laravel

php artisan serve

```

<!-- ข้อมูลเข้าสู่ระบบ (Super Admin) -->
<!-- superadmin@example.com -->
<!-- password -->
```
