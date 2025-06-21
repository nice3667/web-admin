# คู่มือการตั้งค่าตารางแยกสำหรับแต่ละ User และการ Sync อัตโนมัติ

## ภาพรวม
ระบบได้ถูกตั้งค่าให้มีตารางแยกสำหรับเก็บข้อมูลของแต่ละ user เพื่อป้องกันการปนกันของข้อมูล และมีระบบ sync อัตโนมัติจาก Exness API

## ตารางที่ถูกสร้าง

### 1. ham_clients
- เก็บข้อมูล client ของ Ham (hamsftmo@gmail.com)
- Model: `App\Models\HamClient`
- Service: `App\Services\HamExnessAuthService`

### 2. kantapong_clients  
- เก็บข้อมูล client ของ Kantapong (kantapong0592@gmail.com)
- Model: `App\Models\KantapongClient`
- Service: `App\Services\KantapongExnessAuthService`

### 3. janischa_clients
- เก็บข้อมูล client ของ Janischa
- Model: `App\Models\JanischaClient`
- Service: `App\Services\JanischaExnessAuthService`

## โครงสร้างตาราง
ทุกตารางมีโครงสร้างเหมือนกัน:

```sql
- id (Primary Key)
- partner_account
- client_uid (Unique)
- client_id
- reg_date
- client_country
- volume_lots (Decimal 20,8)
- volume_mln_usd (Decimal 20,8)
- reward_usd (Decimal 20,8)
- client_status
- kyc_passed (Boolean)
- ftd_received (Boolean)
- ftt_made (Boolean)
- raw_data (JSON)
- last_sync_at (Timestamp)
- created_at
- updated_at
```

## Console Commands ที่สร้าง

### Commands หลัก
```bash
# Sync ข้อมูลทุก user พร้อมกัน
php artisan sync:all-users [--force]

# Sync ข้อมูล Ham
php artisan sync:ham-data [--force]

# Sync ข้อมูล Kantapong  
php artisan sync:kantapong-data [--force]

# Sync ข้อมูล Janischa
php artisan sync:janischa-data [--force]
```

### ตัวอย่างการใช้งาน
```bash
# Sync ทุก user (ข้าม time limit)
php artisan sync:all-users --force

# Sync เฉพาะ Ham
php artisan sync:ham-data

# ดู logs
tail -f storage/logs/user-sync.log
```

## การตั้งค่า Sync อัตโนมัติ

### สำหรับ Linux/Mac
```bash
# ให้สิทธิ์ execute
chmod +x setup_user_sync_cron.sh

# รัน script
./setup_user_sync_cron.sh
```

### สำหรับ Windows
```cmd
# รันในฐานะ Administrator
setup_user_sync_windows.bat
```

## ตารางเวลา Sync อัตโนมัติ

| Task | Frequency | Time |
|------|-----------|------|
| All Users Sync | ทุก 30 นาที | - |
| Ham Sync | ทุกชั่วโมง | นาทีที่ 5 |
| Kantapong Sync | ทุกชั่วโมง | นาทีที่ 15 |
| Janischa Sync | ทุกชั่วโมง | นาทีที่ 25 |

## Log Files
- `storage/logs/user-sync.log` - All users sync
- `storage/logs/ham-sync.log` - Ham sync only
- `storage/logs/kantapong-sync.log` - Kantapong sync only
- `storage/logs/janischa-sync.log` - Janischa sync only

## การตั้งค่า Janischa Service

⚠️ **สำคัญ**: ต้องแก้ไขข้อมูลการเข้าสู่ระบบของ Janischa

แก้ไขไฟล์: `app/Services/JanischaExnessAuthService.php`

```php
private $email = 'janischa.actual@gmail.com'; // ใส่ email จริง
private $password = 'ActualPassword123'; // ใส่ password จริง
```

## การตรวจสอบข้อมูล

### ตรวจสอบจำนวนข้อมูลในตาราง
```bash
php artisan tinker
```

```php
// ตรวจสอบจำนวน records
App\Models\HamClient::count();
App\Models\KantapongClient::count();
App\Models\JanischaClient::count();

// ตรวจสอบข้อมูลล่าสุด
App\Models\HamClient::latest('last_sync_at')->first();
```

## การจัดการ Cron Jobs

### ดู Cron Jobs ปัจจุบัน
```bash
crontab -l
```

### ลบ Sync Cron Jobs
```bash
# Linux/Mac
crontab -l | grep -v 'sync:.*-data' | grep -v 'sync:all-users' | crontab -

# Windows - ลบ Scheduled Tasks
schtasks /delete /tn "UserDataSync_AllUsers" /f
schtasks /delete /tn "UserDataSync_Ham" /f
schtasks /delete /tn "UserDataSync_Kantapong" /f
schtasks /delete /tn "UserDataSync_Janischa" /f
```

## Features ของ Models

### Scopes ที่มีให้ใช้
```php
// ตัวอย่างการใช้ Scopes
$activeClients = HamClient::active()->get();
$kycClients = KantapongClient::kycPassed()->get();
$ftdClients = JanischaClient::ftdReceived()->get();
$thaiClients = HamClient::byCountry('TH')->get();
```

### Accessors
```php
$client = HamClient::first();
echo $client->formatted_volume_lots; // จำนวน lots แบบ format
echo $client->formatted_reward_usd;  // reward แบบ format
```

## การแก้ไขปัญหา

### ปัญหาที่อาจเกิดขึ้น

1. **Authentication Failed**
   - ตรวจสอบ email/password ใน Service
   - ตรวจสอบการเชื่อมต่อ internet

2. **Database Connection Error**
   - ตรวจสอบการตั้งค่า database ใน `.env`
   - ตรวจสอบว่า MySQL service ทำงานอยู่

3. **Cron Jobs ไม่ทำงาน**
   - ตรวจสอบ path ของ PHP และ Laravel
   - ตรวจสอบ permissions ของ log files

### คำสั่งแก้ไขปัญหา
```bash
# Clear cache
php artisan optimize:clear

# ตรวจสอบการเชื่อมต่อ database
php artisan migrate:status

# ทดสอบ API connection
php artisan tinker
$service = new App\Services\HamExnessAuthService();
$result = $service->testConnection();
var_dump($result);
```

## สถานะปัจจุบัน

✅ **สำเร็จแล้ว:**
- สร้างตาราง ham_clients, kantapong_clients, janischa_clients
- สร้าง Models และ Services
- สร้าง Console Commands
- ทดสอบ Ham sync สำเร็จ (1074 clients)
- ทดสอบ Kantapong sync สำเร็จ
- สร้าง scripts สำหรับ cron jobs

⚠️ **ต้องทำต่อ:**
- แก้ไขข้อมูลการเข้าสู่ระบบของ Janischa
- ทดสอบ Janischa sync
- ตั้งค่า cron jobs บน production server

## การใช้งานในอนาคต

### เพิ่ม User ใหม่
1. สร้าง migration สำหรับตารางใหม่
2. สร้าง Model
3. สร้าง Service สำหรับ Exness API
4. สร้าง Console Command
5. อัปเดต `SyncAllUsersData` command
6. อัปเดต cron setup scripts

### การ Monitor
- ตรวจสอบ log files เป็นประจำ
- ตั้งค่า alerts สำหรับ sync failures
- Monitor database size และ performance 