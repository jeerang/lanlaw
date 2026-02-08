# 📋 แผนการออกแบบระบบงานเอกสารสำนักงานทนายความ

## 🎯 ภาพรวมของระบบ
ระบบจัดการงานเอกสารสำนักงานทนายความ พัฒนาด้วย PHP และ MySQL สำหรับจัดการคดีความ การเบิกจ่าย และออกรายงาน

---

## 📁 โครงสร้างฐานข้อมูล (MySQL)

### 1. ตารางผู้ใช้งาน (users)
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. ตารางประเภทงาน (work_types)
```sql
CREATE TABLE work_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. ตารางศาล (courts)
```sql
CREATE TABLE courts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    province VARCHAR(100),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 4. ตารางประเภทการเบิก (disbursement_types)
```sql
CREATE TABLE disbursement_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5. ตารางทนายความ (lawyers)
```sql
CREATE TABLE lawyers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    prefix VARCHAR(20),
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    license_number VARCHAR(50),
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 6. ตาราง A/O ที่รับผิดชอบงาน (account_officers)
```sql
CREATE TABLE account_officers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 7. ตารางสำนักงานเจ้าของงาน (offices)
```sql
CREATE TABLE offices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    contact_person VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 8. ตารางรับงาน/คดี (cases)
```sql
CREATE TABLE cases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    debtor_code VARCHAR(50) NOT NULL,
    debtor_name VARCHAR(200) NOT NULL,
    port VARCHAR(20),
    work_type_id INT,
    office_id INT,
    due_date DATE,
    received_date DATE,
    filing_date DATE,
    judgment_date DATE,
    court_id INT,
    black_case VARCHAR(50),
    red_case VARCHAR(50),
    current_action_date DATE,
    current_action TEXT,
    next_action_date DATE,
    next_action TEXT,
    lawyer_id INT,
    ao_id INT,
    problems_remarks TEXT,
    status ENUM('active', 'closed', 'pending') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (work_type_id) REFERENCES work_types(id),
    FOREIGN KEY (office_id) REFERENCES offices(id),
    FOREIGN KEY (court_id) REFERENCES courts(id),
    FOREIGN KEY (lawyer_id) REFERENCES lawyers(id),
    FOREIGN KEY (ao_id) REFERENCES account_officers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### 9. ตารางใบเบิก (disbursements) - Header
```sql
CREATE TABLE disbursements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    disbursement_date DATE NOT NULL,
    disbursement_number VARCHAR(50) UNIQUE NOT NULL,
    case_id INT,
    debtor_code VARCHAR(50),
    debtor_name VARCHAR(200),
    black_case VARCHAR(50),
    red_case VARCHAR(50),
    court_id INT,
    ao_id INT,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'processing', 'paid') DEFAULT 'pending',
    status_updated_at TIMESTAMP,
    remarks TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id),
    FOREIGN KEY (court_id) REFERENCES courts(id),
    FOREIGN KEY (ao_id) REFERENCES account_officers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### 10. ตารางรายการเบิกย่อย (disbursement_items)
```sql
CREATE TABLE disbursement_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    disbursement_id INT NOT NULL,
    item_no INT NOT NULL,
    disbursement_type_id INT,
    description TEXT,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disbursement_id) REFERENCES disbursements(id) ON DELETE CASCADE,
    FOREIGN KEY (disbursement_type_id) REFERENCES disbursement_types(id)
);
```

---

## 🏗️ โครงสร้างโฟลเดอร์

```
lanlaw/
├── index.php                    # หน้า Login
├── config/
│   ├── database.php             # การเชื่อมต่อฐานข้อมูล
│   └── config.php               # การตั้งค่าระบบ
├── includes/
│   ├── header.php               # ส่วนหัว
│   ├── footer.php               # ส่วนท้าย
│   ├── sidebar.php              # เมนูด้านข้าง
│   ├── auth.php                 # ตรวจสอบสิทธิ์
│   └── functions.php            # ฟังก์ชันทั่วไป
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   ├── images/
│   └── libs/                    # Bootstrap, jQuery, etc.
├── admin/                       # ส่วน Admin
│   ├── index.php                # Dashboard Admin
│   ├── users/                   # จัดการผู้ใช้งาน
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── work_types/              # จัดการประเภทงาน
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── courts/                  # จัดการศาล
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── disbursement_types/      # จัดการประเภทการเบิก
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── lawyers/                 # จัดการข้อมูลทนายความ
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── account_officers/        # จัดการข้อมูล A/O
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   └── offices/                 # จัดการสำนักงานเจ้าของงาน
│       ├── index.php
│       ├── add.php
│       ├── edit.php
│       └── delete.php
├── cases/                       # ส่วนรับงาน
│   ├── index.php                # รายการคดีทั้งหมด
│   ├── add.php                  # เพิ่มรายการคดี
│   ├── edit.php                 # แก้ไขรายการคดี
│   ├── view.php                 # ดูรายละเอียดคดี
│   ├── delete.php               # ลบรายการคดี
│   └── ajax/
│       └── get_data.php         # ดึงข้อมูลสำหรับ Dropdown
├── disbursements/               # ส่วนใบเบิก
│   ├── index.php                # รายการใบเบิกทั้งหมด
│   ├── add.php                  # เพิ่มใบเบิก
│   ├── edit.php                 # แก้ไขใบเบิก
│   ├── view.php                 # ดูรายละเอียดใบเบิก
│   ├── delete.php               # ลบใบเบิก
│   ├── update_status.php        # อัพเดทสถานะใบเบิก
│   └── ajax/
│       └── get_case_data.php    # ดึงข้อมูลคดีสำหรับใบเบิก
├── reports/                     # ส่วนรายงาน
│   ├── index.php                # หน้าหลักรายงาน
│   ├── case_report.php          # รายงานคดี
│   ├── disbursement_report.php  # รายงานสรุปการเบิก
│   └── pdf/
│       ├── generate_case_pdf.php        # สร้าง PDF รายงานคดี
│       └── generate_disbursement_pdf.php # สร้าง PDF รายงานเบิก
├── libs/
│   └── tcpdf/                   # Library สำหรับสร้าง PDF
└── uploads/                     # ไฟล์ที่อัพโหลด
```

---

## 👥 ระบบสิทธิ์การเข้าถึง

### Admin (ผู้ดูแลระบบ)
| ส่วนงาน | สิทธิ์ |
|---------|--------|
| จัดการผู้ใช้งาน | ✅ เต็มรูปแบบ |
| จัดการประเภทงาน | ✅ เต็มรูปแบบ |
| จัดการศาล | ✅ เต็มรูปแบบ |
| จัดการประเภทการเบิก | ✅ เต็มรูปแบบ |
| จัดการทนายความ | ✅ เต็มรูปแบบ |
| จัดการ A/O | ✅ เต็มรูปแบบ |
| จัดการสำนักงาน | ✅ เต็มรูปแบบ |
| ส่วนรับงาน | ✅ เต็มรูปแบบ |
| ส่วนใบเบิก | ✅ เต็มรูปแบบ |
| ส่วนรายงาน | ✅ เต็มรูปแบบ |

### User (ผู้ใช้งานทั่วไป)
| ส่วนงาน | สิทธิ์ |
|---------|--------|
| จัดการผู้ใช้งาน | ❌ ไม่มีสิทธิ์ |
| จัดการประเภทงาน | ❌ ไม่มีสิทธิ์ |
| จัดการศาล | ❌ ไม่มีสิทธิ์ |
| จัดการประเภทการเบิก | ❌ ไม่มีสิทธิ์ |
| จัดการทนายความ | ❌ ไม่มีสิทธิ์ |
| จัดการ A/O | ❌ ไม่มีสิทธิ์ |
| จัดการสำนักงาน | ❌ ไม่มีสิทธิ์ |
| ส่วนรับงาน | ✅ เต็มรูปแบบ |
| ส่วนใบเบิก | ✅ เต็มรูปแบบ |
| ส่วนรายงาน | ✅ ดูและออกรายงาน |

---

## 📝 รายละเอียดฟังก์ชันการทำงาน

### 1. ส่วน Admin (การจัดการข้อมูลพื้นฐาน)

#### 1.1 จัดการผู้ใช้งาน
- เพิ่ม/แก้ไข/ลบ ผู้ใช้งาน
- กำหนดสิทธิ์ (admin/user)
- เปิด/ปิดใช้งานบัญชี
- รีเซ็ตรหัสผ่าน

#### 1.2 จัดการประเภทงาน
- เพิ่ม/แก้ไข/ลบ ประเภทงาน
- กำหนดรหัสประเภทงาน (เช่น SCB01, KBAN08)

#### 1.3 จัดการศาล
- เพิ่ม/แก้ไข/ลบ ข้อมูลศาล
- บันทึกชื่อศาล จังหวัด ที่อยู่

#### 1.4 จัดการประเภทการเบิก
- เพิ่ม/แก้ไข/ลบ ประเภทการเบิก
- เช่น ค่าขึ้นศาล, ค่าเดินทาง, ค่าธรรมเนียม

#### 1.5 จัดการข้อมูลทนายความ
- เพิ่ม/แก้ไข/ลบ ข้อมูลทนายความ
- บันทึกเลขที่ใบอนุญาต

#### 1.6 จัดการข้อมูล A/O
- เพิ่ม/แก้ไข/ลบ ข้อมูล Account Officer

#### 1.7 จัดการสำนักงานเจ้าของงาน
- เพิ่ม/แก้ไข/ลบ สำนักงาน
- บันทึกข้อมูลติดต่อ

---

### 2. ส่วนรับงาน (Cases)

#### 2.1 รายการคดี
- แสดงรายการคดีทั้งหมดในรูปแบบตาราง
- ค้นหาและกรองข้อมูล
- แสดงสถานะคดี

#### 2.2 เพิ่มรายการคดี
**ฟิลด์ข้อมูล:**
| ฟิลด์ | ประเภท | คำอธิบาย |
|-------|--------|----------|
| รหัสลูกหนี้ | Text | รหัสประจำตัวลูกหนี้ |
| ชื่อลูกหนี้ | Text | ชื่อ-นามสกุลลูกหนี้ |
| PORT | Text | รหัส PORT |
| ประเภทงาน | Dropdown | ดึงจากตาราง work_types |
| สำนักงานเจ้าของงาน | Dropdown | ดึงจากตาราง offices |
| ให้ดำเนินการภายในวันที่ | Date | วันครบกำหนด |
| วันรับเรื่อง | Date | วันที่รับงาน |
| วันฟ้อง | Date | วันที่ยื่นฟ้อง |
| วันพิพากษา | Date | วันที่ศาลพิพากษา |
| ศาล | Dropdown | ดึงจากตาราง courts |
| คดีดำ | Text | เลขคดีดำ |
| คดีแดง | Text | เลขคดีแดง |
| วันที่ดำเนินการปัจจุบัน | Date | |
| การดำเนินการปัจจุบัน | Textarea | |
| วันที่ดำเนินการต่อไป | Date | |
| การดำเนินการต่อไป | Textarea | |
| ทนายความ | Dropdown | ดึงจากตาราง lawyers |
| A/O | Dropdown | ดึงจากตาราง account_officers |
| ปัญหาอุปสรรค/หมายเหตุ | Textarea | |

#### 2.3 ฟังก์ชัน AJAX
- ดึงข้อมูล Dropdown อัตโนมัติ
- ค้นหาข้อมูลลูกหนี้

---

### 3. ส่วนใบเบิก (Disbursements)

#### 3.1 รายการใบเบิก
- แสดงรายการใบเบิกทั้งหมด
- กรองตามสถานะ (ระหว่างเบิก/ค้างจ่าย/รับเงินแล้ว)
- ค้นหาข้อมูลตามเลขที่ใบเบิก, รหัสลูกหนี้, ชื่อลูกหนี้
- แสดงยอดรวมของแต่ละใบเบิก

#### 3.2 เพิ่มใบเบิก (รองรับหลายรายการ)

**ขั้นตอนการเพิ่มใบเบิก:**
```
┌─────────────────────────────────────────────────────────────────────┐
│  ขั้นตอนที่ 1: ค้นหาข้อมูลลูกหนี้                                      │
│  ┌─────────────────────────────────────────────────────────────────┐│
│  │  🔍 ค้นหาจากรหัสลูกหนี้: [________________] [ค้นหา]            ││
│  │                                                                 ││
│  │  ผลการค้นหา:                                                    ││
│  │  ┌─────────────────────────────────────────────────────────────┐││
│  │  │ รหัส: 809133759519  ชื่อ: เรือไหญ่ว็ดี ศรีมงคล              │││
│  │  │ คดีดำ: 481/2540    คดีแดง: 3919/2540                        │││
│  │  │ ศาล: ศาลจังหวัดเชียงใหม่                                     │││
│  │  │                                          [เลือก]            │││
│  │  └─────────────────────────────────────────────────────────────┘││
│  └─────────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│  ขั้นตอนที่ 2: กรอกข้อมูลใบเบิก (Header)                             │
│  ┌─────────────────────────────────────────────────────────────────┐│
│  │  วันที่เบิก: [24/01/2569]     เลขที่ใบเบิก: [DB-202601-0001]   ││
│  │  รหัสลูกหนี้: [809133759519]  ชื่อลูกหนี้: [เรือไหญ่ว็ดี...]    ││
│  │  คดีดำ: [481/2540]           คดีแดง: [3919/2540]               ││
│  │  ศาล: [ศาลจังหวัดเชียงใหม่ ▼] A/O: [อัฉฎา / พลิน ▼]            ││
│  └─────────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│  ขั้นตอนที่ 3: เพิ่มรายการเบิก (สามารถเพิ่มได้หลายรายการ)            │
│  ┌─────────────────────────────────────────────────────────────────┐│
│  │ ลำดับ │ ประเภทการเบิก      │ รายละเอียด          │ จำนวนเงิน   ││
│  │───────┼────────────────────┼─────────────────────┼─────────────││
│  │   1   │ [ค่าขึ้นศาล ▼]     │ [ค่าธรรมเนียม...]   │ [1,500.00]  ││
│  │   2   │ [ค่าเดินทาง ▼]     │ [ค่าเดินทางไป...]   │ [500.00]    ││
│  │   3   │ [ค่าถ่ายเอกสาร ▼]  │ [ค่าถ่ายเอกสาร...]  │ [200.00]    ││
│  │                                                   [+ เพิ่มรายการ]│
│  │─────────────────────────────────────────────────────────────────││
│  │                                        รวมทั้งสิ้น: 2,200.00 บาท││
│  └─────────────────────────────────────────────────────────────────┘│
│                                                                     │
│  หมายเหตุ: [____________________________________]                   │
│                                                                     │
│                              [บันทึก]  [ยกเลิก]                      │
└─────────────────────────────────────────────────────────────────────┘
```

**ฟิลด์ข้อมูลหลัก (Header):**
| ฟิลด์ | ประเภท | คำอธิบาย |
|-------|--------|----------|
| วันที่ | Date | วันที่เบิก (Default: วันปัจจุบัน) |
| เลขที่ใบเบิก | Text | Auto-generate (รูปแบบ: DB-YYYYMM-XXXX) |
| รหัสลูกหนี้ | Text + Search | **ค้นหาจากรหัสลูกหนี้** |
| รายชื่อลูกหนี้ | Text | ดึงอัตโนมัติจากการค้นหา |
| คดีดำ | Text | ดึงอัตโนมัติจากการค้นหา |
| คดีแดง | Text | ดึงอัตโนมัติจากการค้นหา |
| ศาล | Dropdown | ดึงอัตโนมัติ + แก้ไขได้ |
| A/O | Dropdown | ดึงอัตโนมัติ + แก้ไขได้ |
| หมายเหตุ | Textarea | บันทึกเพิ่มเติม |

**ฟิลด์รายการเบิกย่อย (Items):**
| ฟิลด์ | ประเภท | คำอธิบาย |
|-------|--------|----------|
| ลำดับ | Number | Auto-generate |
| ประเภทการเบิก | Dropdown | ดึงจากตาราง disbursement_types |
| รายละเอียด | Text | รายละเอียดการเบิก |
| จำนวนเงิน | Number | จำนวนเงินของรายการนี้ |

#### 3.3 ฟังก์ชันค้นหาลูกหนี้ (AJAX)
- ค้นหาจากรหัสลูกหนี้ (debtor_code)
- ค้นหาจากชื่อลูกหนี้ (debtor_name)
- แสดงผลการค้นหาแบบ Autocomplete
- เมื่อเลือก จะดึงข้อมูลมาแสดง:
  - รหัสลูกหนี้
  - ชื่อลูกหนี้
  - คดีดำ/คดีแดง
  - ศาล
  - A/O
- ข้อมูลที่ดึงมาสามารถแก้ไขได้ภายหลัง

#### 3.4 การจัดการรายการเบิก
- **เพิ่มรายการ**: คลิกปุ่ม "+ เพิ่มรายการ" เพื่อเพิ่มแถวใหม่
- **ลบรายการ**: คลิกปุ่ม "ลบ" ที่แถวนั้น
- **คำนวณยอดรวม**: อัตโนมัติเมื่อกรอกหรือแก้ไขจำนวนเงิน
- **ไม่จำกัดจำนวนรายการ**: เพิ่มได้ไม่จำกัด

#### 3.5 สถานะใบเบิก
| สถานะ | คำอธิบาย | สี |
|--------|----------|-----|
| pending | ระหว่างเบิก | 🟡 เหลือง |
| processing | ค้างจ่าย | 🟠 ส้ม |
| paid | รับเงินแล้ว | 🟢 เขียว |

#### 3.6 อัพเดทสถานะ
- เปลี่ยนสถานะใบเบิกได้
- บันทึกวันที่เปลี่ยนสถานะ
- สามารถเปลี่ยนสถานะหลายใบพร้อมกัน (Bulk Update)

#### 3.7 แก้ไขใบเบิก
- แก้ไขข้อมูล Header ได้
- เพิ่ม/ลบ/แก้ไข รายการเบิกย่อยได้
- ยอดรวมอัพเดทอัตโนมัติ

---

### 4. ส่วนรายงาน (Reports)

#### 4.1 รายงานผลการดำเนินคดีเฉพาะ
**ตัวกรอง:**
- เลือกสำนักงาน
- เลือกช่วงวันที่
- เลือกประเภทงาน
- เลือกศาล

**รูปแบบรายงาน (ตามภาพที่แนบ):**
```
┌─────────────────────────────────────────────────────────────────────┐
│           สำนักงานทนายความ อัฉฏกุฎมายและธุรกิจ                        │
│    รายงานผลการดำเนินคดีเฉพาะ ณ วันที่ XX มกราคม 2026                  │
│                  สำนักงาน: ธนาเจริญเมือง                               │
├─────┬──────────┬──────────┬──────┬─────────┬────────┬───────┬───────┤
│ลำดับ│รหัสลูกหนี้│ชื่อลูกหนี้│ PORT │ประเภทงาน│วันครบกำหนด│วันรับเรื่อง│วันฟ้อง│...
├─────┼──────────┼──────────┼──────┼─────────┼────────┼───────┼───────┤
│  1  │809,133...│เรือใหญ่...│SCB01│คัดรับรอง│22 ธ.ค.68│11 ธ.ค.68│ศาลจว.│...
├─────┼──────────┼──────────┼──────┼─────────┼────────┼───────┼───────┤
│  2  │131737   │นายกมล...  │SCB56│ยึดทรัพย์│30 ม.ค.69│11 ธ.ค.68│ศาลจว.│...
└─────┴──────────┴──────────┴──────┴─────────┴────────┴───────┴───────┘
                                              ลงชื่อ .......................
                                              (นางสาวอัฉฎา ข้อสุวินท์)
                                                 หัวหน้าสำนักงาน
```

#### 4.2 รายงานสรุปการเบิก
**ตัวกรอง:**
- เลือกช่วงวันที่
- เลือกสถานะ
- เลือก A/O
- เลือกประเภทการเบิก

**ข้อมูลในรายงาน:**
- สรุปยอดเงินทั้งหมด
- สรุปตามสถานะ
- สรุปตามประเภทการเบิก

#### 4.3 การสร้าง PDF
- ใช้ TCPDF Library
- รองรับภาษาไทย (Font: THSarabun)
- รูปแบบกระดาษ A4 แนวนอน

---

## 🎨 User Interface (UI)

### หน้า Login
- ฟอร์มเข้าสู่ระบบ (username/password)
- จดจำการเข้าสู่ระบบ

### Layout หลัก
```
┌────────────────────────────────────────────────────┐
│  Header: Logo + ชื่อระบบ + ข้อมูลผู้ใช้ + Logout   │
├──────────┬─────────────────────────────────────────┤
│          │                                         │
│  Sidebar │           Content Area                  │
│  (Menu)  │                                         │
│          │                                         │
│          │                                         │
├──────────┴─────────────────────────────────────────┤
│  Footer: Copyright                                 │
└────────────────────────────────────────────────────┘
```

### เมนู Admin
- 🏠 หน้าหลัก
- 👥 จัดการผู้ใช้งาน
- 📋 ประเภทงาน
- ⚖️ ศาล
- 💰 ประเภทการเบิก
- 👔 ทนายความ
- 👤 A/O
- 🏢 สำนักงาน
- 📁 รับงาน
- 📝 ใบเบิก
- 📊 รายงาน

### เมนู User
- 🏠 หน้าหลัก
- 📁 รับงาน
- 📝 ใบเบิก
- 📊 รายงาน

---

## 🛠️ เทคโนโลยีที่ใช้

| ส่วน | เทคโนโลยี |
|------|-----------|
| Backend | PHP 8.x |
| Database | MySQL 8.x |
| Frontend | HTML5, CSS3, JavaScript |
| CSS Framework | Bootstrap 5 |
| JavaScript Library | jQuery 3.x |
| PDF Generation | TCPDF |
| Icons | Font Awesome 6 |
| DataTables | DataTables.js |
| Date Picker | Bootstrap Datepicker |

---

## 📅 แผนการพัฒนา

### Phase 1: พื้นฐาน (สัปดาห์ที่ 1-2)
- [x] ออกแบบฐานข้อมูล
- [ ] สร้างโครงสร้างโฟลเดอร์
- [ ] สร้างไฟล์ config และ includes
- [ ] ระบบ Login/Logout
- [ ] Layout หลัก (Header, Sidebar, Footer)

### Phase 2: ส่วน Admin (สัปดาห์ที่ 3-4)
- [ ] CRUD ผู้ใช้งาน
- [ ] CRUD ประเภทงาน
- [ ] CRUD ศาล
- [ ] CRUD ประเภทการเบิก
- [ ] CRUD ทนายความ
- [ ] CRUD A/O
- [ ] CRUD สำนักงาน

### Phase 3: ส่วนรับงาน (สัปดาห์ที่ 5-6)
- [ ] รายการคดี
- [ ] เพิ่ม/แก้ไข/ลบ คดี
- [ ] ระบบค้นหาและกรอง
- [ ] AJAX สำหรับ Dropdown

### Phase 4: ส่วนใบเบิก (สัปดาห์ที่ 7-8)
- [ ] รายการใบเบิก
- [ ] เพิ่ม/แก้ไข/ลบ ใบเบิก
- [ ] ระบบอัพเดทสถานะ
- [ ] เชื่อมโยงกับข้อมูลคดี

### Phase 5: ส่วนรายงาน (สัปดาห์ที่ 9-10)
- [ ] รายงานผลการดำเนินคดี
- [ ] รายงานสรุปการเบิก
- [ ] สร้าง PDF รายงาน
- [ ] เลือกช่วงเวลา

### Phase 6: ทดสอบและปรับปรุง (สัปดาห์ที่ 11-12)
- [ ] ทดสอบระบบ
- [ ] แก้ไข Bug
- [ ] ปรับปรุง UI/UX
- [ ] จัดทำเอกสาร

---

## 📌 หมายเหตุ

1. **ความปลอดภัย**
   - ใช้ Prepared Statements ป้องกัน SQL Injection
   - Hash Password ด้วย `password_hash()`
   - ตรวจสอบ Session ทุกหน้า
   - กรองข้อมูล Input ทุกครั้ง

2. **การ Backup**
   - Backup ฐานข้อมูลเป็นประจำ
   - เก็บ Log การใช้งาน

3. **Font สำหรับ PDF**
   - ใช้ Font THSarabun สำหรับภาษาไทย
   - ต้องติดตั้ง Font ใน TCPDF

---

## 📞 ข้อมูลติดต่อ
**สำนักงานทนายความ อัฉฏกุฎมายและธุรกิจ**
- หัวหน้าสำนักงาน: นางสาวอัฉฎา ข้อสุวินท์
