# คู่มือการใช้งานระบบ Report แบบ Multi-User

## ภาพรวม

ระบบนี้ใช้สำหรับแสดงรายงานข้อมูลลูกค้าจาก Exness API สำหรับผู้ใช้หลายคน โดยแต่ละผู้ใช้จะมีข้อมูลแยกกันตาม account ของตนเอง รองรับการแสดงข้อมูลแบบ real-time จาก Exness API

## ฟีเจอร์หลัก

### 1. ระบบ Multi-User Reports
- **Report1**: สำหรับผู้ใช้ `hamsftmo@gmail.com`
- **Report2**: สำหรับผู้ใช้ `kantapong0592@gmail.com`
- แต่ละ Report แยกข้อมูลและ API credentials อิสระ

### 2. Real-time Data from Exness API
- เชื่อมต่อ Exness API แบบ real-time
- แสดงข้อมูลปัจจุบันจาก Exness dashboard
- มี fallback ไปยังฐานข้อมูลเมื่อ API ล้มเหลว

### 3. หน้ารายงานหลัก
- **Clients**: รายงานลูกค้าทั้งหมด
- **ClientAccount**: รายงานบัญชีลูกค้า
- **ClientTransaction**: รายงานธุรกรรมลูกค้า
- **TransactionsPending**: รายงานธุรกรรมที่รอดำเนินการ
- **RewardHistory**: ประวัติรางวัล

### 4. ระบบสถิติแบบ Real-time
- จำนวน Client UID (unique clients)
- Volume (lots) - ปริมาณการเทรด
- Volume (USD) - มูลค่าการเทรด
- Reward (USD) - รางวัลที่ได้รับ

## โครงสร้างระบบ

### 1. Report1 (Ham's Account)
- **User**: `hamsftmo@gmail.com`
- **Password**: `Ham@240446`
- **Service**: `HamExnessAuthService`
- **Controller**: `Report1Controller`
- **Routes**: `/admin/reports1/*`
- **API Data**: 376 unique clients, ~$101,853 total reward

### 2. Report2 (Kantapong's Account)
- **User**: `kantapong0592@gmail.com`
- **Password**: `Kantapong.0592z`
- **Service**: `KantapongExnessAuthService`
- **Controller**: `Report2Controller`
- **Routes**: `/admin/reports2/*`
- **API Data**: 161 unique clients, $2,475.70 total reward

## วิธีการใช้งาน

### 1. เข้าถึงรายงาน

#### Report1 (Ham)
```
/admin/reports1/clients1          - รายงานลูกค้า
/admin/reports1/client-account1   - รายงานบัญชีลูกค้า
/admin/reports1/client-transaction1 - รายงานธุรกรรม
/admin/reports1/transactions-pending1 - ธุรกรรมที่รอ
/admin/reports1/reward-history1   - ประวัติรางวัล
/admin/reports1/test-connection   - ทดสอบการเชื่อมต่อ
```

#### Report2 (Kantapong)
```
/admin/reports2/clients2          - รายงานลูกค้า
/admin/reports2/client-account2   - รายงานบัญชีลูกค้า
/admin/reports2/client-transaction2 - รายงานธุรกรรม
/admin/reports2/transactions-pending2 - ธุรกรรมที่รอ
/admin/reports2/reward-history2   - ประวัติรางวัล
/admin/reports2/test-connection2  - ทดสอบการเชื่อมต่อ
```

### 2. การกรองข้อมูล
- **ค้นหา**: ตาม Client UID
- **กรองตามประเทศ**: เลือกประเทศที่ต้องการ
- **กรองตามสถานะ**: ACTIVE, INACTIVE
- **กรองตามวันที่**: ช่วงวันที่ลงทะเบียน

### 3. การแสดงข้อมูล
- **Data Source Indicator**: แสดงว่าข้อมูลมาจาก Exness API หรือ Database
- **User Info**: แสดงอีเมลของผู้ใช้ที่เกี่ยวข้อง
- **Real-time Stats**: สถิติแบบ real-time จาก API

## โครงสร้างไฟล์

### 1. Controllers
```
app/Http/Controllers/Admin/Report1Controller.php
app/Http/Controllers/Admin/Report2Controller.php
```

### 2. Services
```
app/Services/HamExnessAuthService.php
app/Services/KantapongExnessAuthService.php
```

### 3. Vue Components
```
resources/js/Pages/Admin/Report1/
├── ClientAccount1.vue
├── Clients1.vue
├── ClientTransaction1.vue
├── TransactionsPending1.vue
└── RewardHistory1.vue

resources/js/Pages/Admin/Report2/
├── ClientAccount2.vue
├── Clients2.vue
├── ClientTransaction2.vue
├── TransactionsPending2.vue
└── RewardHistory2.vue
```

### 4. Routes
```
routes/admin.php - ประกอบด้วย routes สำหรับทั้ง Report1 และ Report2
```

### 5. Menu Configuration
```
resources/js/menuAside.js - เมนูหลัก
resources/js/Components/BottomNavBar.vue - เมนู dropdown
```

## การตั้งค่า API

### 1. Ham's API Configuration
```php
// app/Services/HamExnessAuthService.php
private $email = 'hamsftmo@gmail.com';
private $password = 'Ham@240446';
private $loginUrl = 'https://my.exnessaffiliates.com/api/v2/auth';
private $apiUrlV1 = 'https://my.exnessaffiliates.com/api/reports/clients/';
```

### 2. Kantapong's API Configuration
```php
// app/Services/KantapongExnessAuthService.php
private $login = 'kantapong0592@gmail.com';
private $password = 'Kantapong.0592z';
private $loginUrl = 'https://my.exnessaffiliates.com/api/v2/auth/';
private $apiUrlV1 = 'https://my.exnessaffiliates.com/api/reports/clients/';
```

## การทำงานของระบบ

### 1. การ Authentication
1. ระบบจะทำ login ไปยัง Exness API
2. รับ JWT token กลับมา
3. ใช้ token สำหรับเรียก API endpoints

### 2. การดึงข้อมูล
1. เรียกข้อมูลจาก Exness API V1
2. ประมวลผลและรวมข้อมูล
3. คำนวณสถิติ (unique clients, volume, reward)
4. ส่งข้อมูลไปยัง Vue components

### 3. การแสดงผล
1. Vue components รับข้อมูลจาก Controller
2. แสดงสถิติในรูปแบบ cards
3. แสดงตารางข้อมูลพร้อมการกรอง
4. แสดง data source indicator

## สถิติและข้อมูล

### Report1 (Ham) - ข้อมูลจริงจาก API
- **Unique Clients**: 376
- **Total Volume (lots)**: 17,657.1852
- **Total Volume (USD)**: 9,855.4593
- **Total Reward (USD)**: 101,853.59

### Report2 (Kantapong) - ข้อมูลจริงจาก API
- **Unique Clients**: 161
- **Total Volume (lots)**: 428.8036
- **Total Volume (USD)**: 275.9274
- **Total Reward (USD)**: 2,475.70

## การแก้ไขปัญหาที่เกิดขึ้น

### 1. ปัญหา Routes ผิด
**อาการ**: Vue components ใช้ routes ของ Report อื่น
**การแก้ไข**: 
- แก้ไข router.get() ใน Vue components ให้ใช้ routes ที่ถูกต้อง
- Report1 ใช้ `/admin/reports1/*`
- Report2 ใช้ `/admin/reports2/*`

### 2. ปัญหา Stats Mapping ผิด
**อาการ**: แสดงข้อมูลเป็น 0 หรือไม่ถูกต้อง
**การแก้ไข**:
- แก้ไข computed properties ใน Vue components
- ใช้ field names ที่ตรงกับ Controller response
- `unique_clients`, `total_volume_lots`, `total_volume_usd`, `total_reward_usd`

### 3. ปัญหา API Authentication
**อาการ**: ไม่สามารถเชื่อมต่อ API ได้
**การแก้ไข**:
- ตรวจสอบ credentials ใน Service classes
- ตรวจสอบ API URLs
- ใช้ `/admin/reports*/test-connection*` เพื่อทดสอบ

### 4. ปัญหาข้อมูลไม่ตรงกับ Exness
**อาการ**: ข้อมูลในเว็บไซต์ไม่ตรงกับ Exness dashboard
**การแก้ไข**:
- ใช้เฉพาะ V1 API แทนการรวม V1+V2
- คำนวณสถิติจากข้อมูลดิบแทนการ group ก่อน
- ตรวจสอบการ filter และ mapping ข้อมูล

## การ Monitor และ Debug

### 1. การตรวจสอบการเชื่อมต่อ
```
GET /admin/reports1/test-connection
GET /admin/reports2/test-connection2
```

### 2. การดู Logs
```bash
tail -f storage/logs/laravel.log
```

### 3. การ Debug ใน Browser
- เปิด Developer Tools
- ดู Console logs จาก Vue components
- ตรวจสอบ Network requests

## การบำรุงรักษา

### 1. การอัพเดต Credentials
หากต้องเปลี่ยน email/password:
1. แก้ไขใน Service classes
2. ทดสอบการเชื่อมต่อผ่าน test-connection endpoints

### 2. การเพิ่ม Report ใหม่
1. สร้าง Service class ใหม่ (คล้าย HamExnessAuthService)
2. สร้าง Controller ใหม่ (คล้าย Report1Controller)
3. เพิ่ม routes ใน admin.php
4. สร้าง Vue components
5. อัพเดต menu configuration

### 3. การอัพเดต Vue Components
หลังจากแก้ไข Vue files:
```bash
npm run build
```

## ข้อควรระวัง

1. **API Rate Limiting**: Exness API อาจมีการจำกัดจำนวน requests
2. **Token Expiration**: JWT tokens มีอายุจำกัด ระบบจะ refresh อัตโนมัติ
3. **Data Consistency**: ข้อมูลจาก API อาจไม่ตรงกับ Exness dashboard 100%
4. **Network Timeout**: การเชื่อมต่อ API อาจ timeout ในบางครั้ง
5. **Browser Caching**: อาจต้อง hard refresh เพื่อดูการเปลี่ยนแปลง

## การพัฒนาต่อ

### 1. ฟีเจอร์ที่แนะนำ
- Export ข้อมูลเป็น Excel/PDF
- Dashboard แสดงกราฟสถิติ
- Email notifications สำหรับข้อมูลสำคัญ
- การกำหนดสิทธิ์ผู้ใช้แบบละเอียด

### 2. การปรับปรุงประสิทธิภาพ
- Caching ข้อมูล API
- Pagination สำหรับข้อมูลจำนวนมาก
- Background jobs สำหรับการประมวลผลข้อมูล
- Real-time updates ด้วย WebSockets

### 3. การปรับปรุง UI/UX
- Responsive design สำหรับ mobile
- Dark mode support
- การกรองข้อมูลแบบ advanced
- Dashboard แสดงภาพรวม

## สรุป

ระบบ Report แบบ Multi-User นี้ให้ความสามารถในการแสดงข้อมูลลูกค้าจาก Exness API สำหรับผู้ใช้หลายคนแยกกัน พร้อมด้วยข้อมูล real-time และระบบ fallback ที่เชื่อถือได้ การแก้ไขปัญหาต่างๆ ทำให้ระบบแสดงข้อมูลที่ถูกต้องและตรงกับ Exness dashboard

สำหรับการใช้งาน ผู้ใช้สามารถเข้าถึงรายงานของตนเองผ่าน menu ที่เกี่ยวข้อง และดูข้อมูลแบบ real-time พร้อมกับสถิติที่แม่นยำ 