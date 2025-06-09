# Laravel Vue Admin Panel

โปรเจกต์นี้เป็นแผงควบคุมผู้ดูแลระบบที่พัฒนาโดยใช้ Laravel + Vue.js เหมาะสำหรับการเริ่มต้นระบบ CRM หรือระบบจัดการภายในองค์กร โดยไม่จำเป็นต้องติดตั้ง Docker

## ✅ สิ่งที่ต้องมีก่อนเริ่ม

-   PHP >= 8.x
-   Composer
-   Node.js และ npm
-   MySQL หรือ MariaDB

## ⚙️ วิธีติดตั้งโปรเจกต์

```bash
# 1. ติดตั้งโปรเจกต์ด้วย Composer
composer create-project balajidharma/laravel-vue-admin-panel admin-app

# 2. เข้าสู่โฟลเดอร์โปรเจกต์
cd admin-app

# 3. สร้างฐานข้อมูลใหม่ใน MySQL เช่นชื่อว่า admin_app

# 4. แก้ไขไฟล์ .env ให้เชื่อมต่อฐานข้อมูล
# เปิด .env แล้วตั้งค่าดังนี้:
DB_DATABASE=admin_app
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password

# 5. รันคำสั่ง publish และ seed ข้อมูลเบื้องต้น
php artisan vendor:publish --tag=admin-core
php artisan migrate --seed --seeder=AdminCoreSeeder

# 6. สร้าง symbolic link สำหรับ storage
php artisan storage:link

# 7. ติดตั้ง front-end dependencies
npm install
npm run dev

# 8. เริ่มเซิร์ฟเวอร์ Laravel
php artisan serve
```

<!-- ข้อมูลเข้าสู่ระบบ (Super Admin) -->
<!-- superadmin@example.com -->
<!-- password -->
