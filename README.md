# E-commerce Back Office (PHP + MySQL + Docker)

โปรเจกต์ตัวอย่างระบบจัดการร้านค้าออนไลน์ (Back Office) 

## เทคโนโลยี
- PHP 8.3 + Apache
- MySQL 8.4
- Docker Compose
- phpMyAdmin
- HTML / CSS

## วิธีเปิดโปรเจกต์
1. ติดตั้ง Docker Desktop และเปิดโปรแกรมให้ทำงาน
2. แตกไฟล์ ZIP นี้ แล้วเปิดโฟลเดอร์ `ecommerce_backoffice` ด้วย VS Code หรือ Antigravity
3. คัดลอกไฟล์ `.env.example` แล้วตั้งชื่อสำเนาว่า `.env` จากนั้นเปิด `.env` และกำหนดรหัสผ่านของเครื่องตัวเอง
4. เปิด Terminal ในโฟลเดอร์เดียวกับ `docker-compose.yml`
5. รันคำสั่ง

```bash
docker compose up -d --build
```

5. เปิดเว็บไซต์: http://localhost:8080
6. เปิด phpMyAdmin: http://localhost:8081

## การเข้าสู่ phpMyAdmin
- Server: `db`
- Username: ค่า `MYSQL_USER` ในไฟล์ `.env`
- Password: ค่า `MYSQL_PASSWORD` ในไฟล์ `.env`

หรือใช้ root:
- Username: `root`
- Password: ค่า `MYSQL_ROOT_PASSWORD` ในไฟล์ `.env`

## ฐานข้อมูล
- Database: `ecommerce_db`
- SQL สำหรับสร้างตารางและนำเข้าข้อมูลอยู่ที่ `database/init.sql`
- MySQL จะรันไฟล์ init.sql อัตโนมัติเมื่อสร้าง data volume ครั้งแรก

## หากแก้ init.sql แล้วต้องการนำเข้าข้อมูลใหม่
คำเตือน: คำสั่งต่อไปนี้จะลบข้อมูลใน MySQL volume เดิมทั้งหมด แล้วสร้างฐานข้อมูลใหม่จาก init.sql

```bash
docker compose down -v
docker compose up -d --build
```

## การใช้งาน
- Dashboard: ดูจำนวนข้อมูลในแต่ละตาราง
- สินค้า / หมวดหมู่ / ลูกค้า / คำสั่งซื้อ / รายการสินค้าในออเดอร์ / ขนาดสินค้า: แสดง เพิ่ม แก้ไข ลบ
- ใช้ Prepared Statements ผ่าน PDO ในการบันทึกข้อมูล

## โครงสร้างไฟล์
- `index.php` หน้า Dashboard และเมนู
- `crud.php` การทำงาน CRUD
- `config.php` กำหนดฟิลด์ของแต่ละตาราง
- `db.php` เชื่อมต่อ MySQL
- `style.css` รูปแบบหน้าจอ
- `database/init.sql` สร้างตารางและนำเข้าข้อมูล
- `docker-compose.yml` ตั้งค่า Container
- `docker/php/Dockerfile` สร้าง PHP + Apache image

## หมายเหตุเกี่ยวกับข้อมูลและ Normalization
- ใช้ข้อมูลจากไฟล์ Excel ที่ให้มา
- ย้ายราคา (เดิมอยู่ใน Product_Variants) ไปเก็บใน Products เพราะข้อมูลต้นฉบับใช้ราคาเดียวกันทุกขนาด
- Product_Variants จึงมี Primary Key แบบ Composite คือ `(pd_id, size_id)` และมี `pd_id` เป็น Foreign Key
- ตาราง Order ใน MySQL ใช้ชื่อ `orders` เพื่อหลีกเลี่ยงคำสงวนของ SQL
- ตาราง order_items เพิ่ม `order_item_id` เป็น Primary Key สำหรับจัดการรายการแต่ละบรรทัด

## จำนวนข้อมูลจากไฟล์ต้นฉบับ
- products: 60
- product_variants: 137
- categories: 4
- customers: 50
- orders: 50
- order_items: 51
 

## ก่อนส่ง GitHub
- อย่าอัปโหลดไฟล์ `.env` ขึ้น GitHub (มีอยู่ใน `.gitignore`) และอย่า commit ข้อมูลลับ
- ตรวจสอบข้อมูลส่วนบุคคลในชุดข้อมูลตัวอย่างก่อนเผยแพร่ Repository
- ทดสอบ CRUD และ Foreign Key ทุกตาราง
- Export ฐานข้อมูลเป็น `.sql` เพื่อแนบส่งงาน
- ระบบนี้เป็นต้นแบบสำหรับรันในเครื่อง (local development) ยังไม่ได้เพิ่มระบบ Login/Role สำหรับนำขึ้นใช้งานจริง
