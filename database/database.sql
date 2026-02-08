-- =====================================================
-- ระบบงานเอกสารสำนักงานทนายความ
-- Database Schema
-- Created: 2026-01-24
-- =====================================================

-- สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS lanlaw_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lanlaw_db;

-- =====================================================
-- 1. ตารางผู้ใช้งาน (users)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. ตารางประเภทงาน (work_types)
-- =====================================================
CREATE TABLE IF NOT EXISTS work_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. ตารางศาล (courts)
-- =====================================================
CREATE TABLE IF NOT EXISTS courts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    province VARCHAR(100),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. ตารางประเภทการเบิก (disbursement_types)
-- =====================================================
CREATE TABLE IF NOT EXISTS disbursement_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. ตารางทนายความ (lawyers)
-- =====================================================
CREATE TABLE IF NOT EXISTS lawyers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    prefix VARCHAR(20),
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    license_number VARCHAR(50),
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. ตาราง A/O ที่รับผิดชอบงาน (account_officers)
-- =====================================================
CREATE TABLE IF NOT EXISTS account_officers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. ตารางสำนักงานเจ้าของงาน (offices)
-- =====================================================
CREATE TABLE IF NOT EXISTS offices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    contact_person VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. ตารางรับงาน/คดี (cases)
-- =====================================================
CREATE TABLE IF NOT EXISTS cases (
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
    INDEX idx_debtor_code (debtor_code),
    INDEX idx_debtor_name (debtor_name),
    INDEX idx_black_case (black_case),
    INDEX idx_red_case (red_case),
    INDEX idx_status (status),
    FOREIGN KEY (work_type_id) REFERENCES work_types(id) ON DELETE SET NULL,
    FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE SET NULL,
    FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE SET NULL,
    FOREIGN KEY (lawyer_id) REFERENCES lawyers(id) ON DELETE SET NULL,
    FOREIGN KEY (ao_id) REFERENCES account_officers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. ตารางใบเบิก (disbursements) - Header
-- =====================================================
CREATE TABLE IF NOT EXISTS disbursements (
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
    status_updated_at TIMESTAMP NULL,
    remarks TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_disbursement_number (disbursement_number),
    INDEX idx_debtor_code (debtor_code),
    INDEX idx_status (status),
    INDEX idx_disbursement_date (disbursement_date),
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE SET NULL,
    FOREIGN KEY (ao_id) REFERENCES account_officers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. ตารางรายการเบิกย่อย (disbursement_items)
-- =====================================================
CREATE TABLE IF NOT EXISTS disbursement_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    disbursement_id INT NOT NULL,
    item_no INT NOT NULL,
    disbursement_type_id INT,
    description TEXT,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_disbursement_id (disbursement_id),
    FOREIGN KEY (disbursement_id) REFERENCES disbursements(id) ON DELETE CASCADE,
    FOREIGN KEY (disbursement_type_id) REFERENCES disbursement_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. ตาราง Log การใช้งาน (activity_logs)
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ข้อมูลเริ่มต้น (Initial Data)
-- =====================================================

-- เพิ่ม Admin User (password: admin123)
INSERT INTO users (username, password, fullname, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin@lanlaw.com', 'admin', 'active'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ใช้งานทั่วไป', 'user@lanlaw.com', 'user', 'active');

-- เพิ่มประเภทงาน
INSERT INTO work_types (code, name, description) VALUES
('SCB01', 'คัดรับรองเอกสาร', 'งานคัดรับรองเอกสารจากศาล'),
('SCB56', 'ยึดทรัพย์จำนอง', 'งานยึดทรัพย์จำนอง'),
('KBAN08', 'สืบทายาท', 'งานสืบทายาท'),
('WORK01', 'ฟ้องคดี', 'งานฟ้องคดีแพ่ง'),
('WORK02', 'บังคับคดี', 'งานบังคับคดี');

-- เพิ่มศาล
INSERT INTO courts (code, name, province) VALUES
('CT01', 'ศาลจังหวัดเชียงใหม่', 'เชียงใหม่'),
('CT02', 'ศาลจังหวัดลำพูน', 'ลำพูน'),
('CT03', 'ศาลแพ่ง', 'กรุงเทพมหานคร'),
('CT04', 'ศาลอาญา', 'กรุงเทพมหานคร'),
('CT05', 'ศาลแรงงานกลาง', 'กรุงเทพมหานคร');

-- เพิ่มประเภทการเบิก
INSERT INTO disbursement_types (code, name, description) VALUES
('DB01', 'ค่าขึ้นศาล', 'ค่าธรรมเนียมขึ้นศาล'),
('DB02', 'ค่าเดินทาง', 'ค่าเดินทางไปศาล'),
('DB03', 'ค่าถ่ายเอกสาร', 'ค่าถ่ายเอกสารและสำเนา'),
('DB04', 'ค่าส่งหมาย', 'ค่าส่งหมายเรียก'),
('DB05', 'ค่าธรรมเนียมอื่นๆ', 'ค่าธรรมเนียมอื่นๆ');

-- เพิ่มทนายความ
INSERT INTO lawyers (code, prefix, firstname, lastname, license_number, phone) VALUES
('LAW01', 'นาย', 'อัฉฎา', 'ข้อสุวินท์', 'ท.12345', '081-234-5678'),
('LAW02', 'นาย', 'พลิน', 'ศรีสุข', 'ท.12346', '081-234-5679'),
('LAW03', 'นางสาว', 'ศิริพัฒน์', 'มงคล', 'ท.12347', '081-234-5680');

-- เพิ่ม A/O
INSERT INTO account_officers (code, name, department, phone) VALUES
('AO01', 'อัฉฎา / พลิน', 'ฝ่ายคดี', '081-111-1111'),
('AO02', 'อัฉฎา / พิชาส', 'ฝ่ายบังคับคดี', '081-222-2222'),
('AO03', 'อัฉฎา / ศิริพัฒน์', 'ฝ่ายสืบทรัพย์', '081-333-3333');

-- เพิ่มสำนักงานเจ้าของงาน
INSERT INTO offices (code, name, address, phone, contact_person) VALUES
('OF01', 'ธนาเจริญเมือง', '123 ถ.ราชดำเนิน อ.เมือง จ.เชียงใหม่', '053-123-456', 'คุณสมชาย'),
('OF02', 'สำนักงานใหญ่', '456 ถ.สีลม กรุงเทพฯ', '02-123-4567', 'คุณสมหญิง'),
('OF03', 'สาขาลำพูน', '789 ถ.ลำพูน อ.เมือง จ.ลำพูน', '053-987-654', 'คุณสมศักดิ์');

-- เพิ่มข้อมูลคดีตัวอย่าง
INSERT INTO cases (debtor_code, debtor_name, port, work_type_id, office_id, due_date, received_date, filing_date, court_id, black_case, red_case, current_action_date, current_action, next_action_date, next_action, lawyer_id, ao_id, problems_remarks, created_by) VALUES
('809133759519', 'เรือใหญ่วีดี ศรีมงคล', 'SCB01', 1, 1, '2025-12-22', '2025-12-11', '2025-12-11', 1, '481/2540', '3919/2540', '2026-01-24', 'ยื่นใบแต่งหมายความ/คำร้องขอคัดรับรองเอกสาร', NULL, NULL, 1, 1, NULL, 1),
('131737', 'นายกมล เขียวธง, นายพีระพัฒน์ เขียวธง', 'SCB56', 2, 1, '2026-01-30', '2025-12-11', '2025-12-11', 1, 'ผบE599/68', 'ผบE133/68', '2026-01-24', NULL, NULL, NULL, 2, 2, NULL, 1),
('20929', 'นายมรวัตน์ ถาริญ ที่1, นายทศไบณ ถาริญ ที่2', 'KBAN08', 3, 1, '2026-01-02', '2025-12-15', '2025-12-15', 1, 'ผบE529/65', 'ผบE863/65', '2026-01-24', NULL, NULL, NULL, 3, 3, NULL, 1);

-- =====================================================
-- สิ้นสุดการสร้างฐานข้อมูล
-- =====================================================
