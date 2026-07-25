<?php
// Auto-migration checks
$resLending = $db->query("SHOW COLUMNS FROM lendings LIKE 'fine_status'");
if ($resLending && $resLending->num_rows == 0) {
    $db->query("ALTER TABLE lendings ADD COLUMN fine_amount DECIMAL(10,2) DEFAULT 0.00");
    $db->query("ALTER TABLE lendings ADD COLUMN fine_status ENUM('None', 'Outstanding', 'Paid', 'Waived') DEFAULT 'None'");
    $db->query("ALTER TABLE lendings ADD COLUMN fine_payment_id VARCHAR(150) NULL");
}
$resMember = $db->query("SHOW COLUMNS FROM members LIKE 'is_active'");
if ($resMember && $resMember->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN is_active TINYINT(1) DEFAULT 1");
}
$db->query("ALTER TABLE print_requests MODIFY COLUMN status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Pending'");

// Auto-migration check: membership plans table
$db->query("CREATE TABLE IF NOT EXISTS membership_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    duration VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL
)");

// Prepopulate default plans if empty
$resPlanCountQuery = $db->query("SELECT COUNT(*) c FROM membership_plans");
if ($resPlanCountQuery) {
    $resPlanCount = $resPlanCountQuery->fetch_assoc()['c'];
    if ($resPlanCount == 0) {
        $db->query("INSERT INTO membership_plans (name, duration, amount) VALUES 
            ('Gold Yearly', 'Yearly', 1200.00),
            ('Standard Half Yearly', 'Half Yearly', 700.00),
            ('Quarterly Study Pass', 'Quarterly', 400.00),
            ('Monthly Reader', 'Monthly', 150.00),
            ('Daily Tourist', 'Daily', 20.00)");
    }
}

// Add membership_plan_id to members table
$resMemberPlan = $db->query("SHOW COLUMNS FROM members LIKE 'membership_plan_id'");
if ($resMemberPlan && $resMemberPlan->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN membership_plan_id INT NULL");
}

// Add membership_fee to members table
$resMemberFee = $db->query("SHOW COLUMNS FROM members LIKE 'membership_fee'");
if ($resMemberFee && $resMemberFee->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN membership_fee VARCHAR(50) DEFAULT ''");
}

// Add shelf_number to physical_books table
$resShelf = $db->query("SHOW COLUMNS FROM physical_books LIKE 'shelf_number'");
if ($resShelf && $resShelf->num_rows == 0) {
    $db->query("ALTER TABLE physical_books ADD COLUMN shelf_number VARCHAR(100) NULL AFTER book_code");
}

// Add approved column to members table (0 = Pending Self-Service Application, 1 = Approved Active Member)
$resMemberApproved = $db->query("SHOW COLUMNS FROM members LIKE 'approved'");
if ($resMemberApproved && $resMemberApproved->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN approved TINYINT(1) DEFAULT 1");
}

// Auto-migration check: Unique index on Aadhar number in members table
$resAadharIdx = $db->query("SHOW INDEX FROM members WHERE Key_name = 'idx_aadhar_no'");
if ($resAadharIdx && $resAadharIdx->num_rows == 0) {
    @$db->query("ALTER TABLE members ADD UNIQUE INDEX idx_aadhar_no (aadhar_no)");
}

// Auto-migration check: Unique index on Payment Transaction ID in members table
$resPayIdx = $db->query("SHOW INDEX FROM members WHERE Key_name = 'idx_payment_id'");
if ($resPayIdx && $resPayIdx->num_rows == 0) {
    @$db->query("ALTER TABLE members ADD UNIQUE INDEX idx_payment_id (payment_id)");
}

// Auto-migration: Ensure unique index on mobile in members table
$resMobileIdx = $db->query("SHOW INDEX FROM members WHERE Key_name = 'idx_mobile'");
if ($resMobileIdx && $resMobileIdx->num_rows == 0) {
    @$db->query("ALTER TABLE members ADD UNIQUE INDEX idx_mobile (mobile)");
}

// Auto-migration check: Unique index on category_id + title in ebooks table
$resEbookIdx = $db->query("SHOW INDEX FROM ebooks WHERE Key_name = 'idx_category_title'");
if ($resEbookIdx && $resEbookIdx->num_rows == 0) {
    @$db->query("ALTER TABLE ebooks ADD UNIQUE INDEX idx_category_title (category_id, title)");
}

// Auto-migration check: duration_minutes and started_reading_at in reading_requests
$resRRDuration = $db->query("SHOW COLUMNS FROM reading_requests LIKE 'duration_minutes'");
if ($resRRDuration && $resRRDuration->num_rows == 0) {
    @$db->query("ALTER TABLE reading_requests ADD COLUMN duration_minutes INT DEFAULT 15");
}
$resRRStarted = $db->query("SHOW COLUMNS FROM reading_requests LIKE 'started_reading_at'");
if ($resRRStarted && $resRRStarted->num_rows == 0) {
    @$db->query("ALTER TABLE reading_requests ADD COLUMN started_reading_at DATETIME NULL");
}

// Add / Update shift column in members table to VARCHAR(100) so dynamic shifts are supported
$resShift = $db->query("SHOW COLUMNS FROM members LIKE 'shift'");
if ($resShift && $resShift->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN shift VARCHAR(100) DEFAULT 'Full Day' AFTER duration");
} else {
    // Ensure column is VARCHAR(100) instead of restricted ENUM
    @$db->query("ALTER TABLE members MODIFY COLUMN shift VARCHAR(100) DEFAULT 'Full Day'");
}
$db->query("UPDATE members SET shift = 'Full Day' WHERE shift = 'Both' OR shift = 'both' OR shift = '' OR shift IS NULL");
$db->query("UPDATE membership_history SET shift = 'Full Day' WHERE shift = 'Both' OR shift = 'both' OR shift = '' OR shift IS NULL");

// Add gender column to members table (Male, Female, Other)
$resGender = $db->query("SHOW COLUMNS FROM members LIKE 'gender'");
if ($resGender && $resGender->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN gender ENUM('Male', 'Female', 'Other') DEFAULT 'Male' AFTER name");
}

// Auto-migration check: admins table recovery columns
$resAdminEmail = $db->query("SHOW COLUMNS FROM admins LIKE 'email'");
if ($resAdminEmail && $resAdminEmail->num_rows == 0) {
    @$db->query("ALTER TABLE admins ADD COLUMN email VARCHAR(150) NULL DEFAULT 'admin@cantonment.gov.in'");
}
$resAdminPin = $db->query("SHOW COLUMNS FROM admins LIKE 'recovery_pin'");
if ($resAdminPin && $resAdminPin->num_rows == 0) {
    @$db->query("ALTER TABLE admins ADD COLUMN recovery_pin VARCHAR(255) NULL DEFAULT '1953'");
}
$resAdminQ = $db->query("SHOW COLUMNS FROM admins LIKE 'security_question'");
if ($resAdminQ && $resAdminQ->num_rows == 0) {
    @$db->query("ALTER TABLE admins ADD COLUMN security_question VARCHAR(255) NULL DEFAULT 'What is the Cantonment Library establishment year?'");
}
$resAdminAns = $db->query("SHOW COLUMNS FROM admins LIKE 'security_answer'");
if ($resAdminAns && $resAdminAns->num_rows == 0) {
    @$db->query("ALTER TABLE admins ADD COLUMN security_answer VARCHAR(255) NULL DEFAULT '1953'");
}

// Auto-migration check: membership_history table
$db->query("CREATE TABLE IF NOT EXISTS membership_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    membership_id VARCHAR(30) NOT NULL,
    membership_plan_id INT NULL,
    plan_name VARCHAR(100) NULL,
    duration VARCHAR(50) NOT NULL,
    shift VARCHAR(50) DEFAULT 'Both',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    membership_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_id VARCHAR(150) NULL,
    action_type ENUM('Initial Joining', 'Renewal', 'Plan Switch', 'Manual Adjustment') NOT NULL DEFAULT 'Initial Joining',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Backfill initial membership_history entries for existing approved members if history is empty
$resHistCountQuery = $db->query("SELECT COUNT(*) c FROM membership_history");
if ($resHistCountQuery) {
    $resHistCount = $resHistCountQuery->fetch_assoc()['c'];
    if ($resHistCount == 0) {
        $db->query("INSERT INTO membership_history (member_id, membership_id, membership_plan_id, plan_name, duration, shift, start_date, end_date, membership_fee, payment_id, action_type, created_at)
            SELECT 
                m.id as member_id,
                m.membership_id,
                m.membership_plan_id,
                p.name as plan_name,
                IF(m.duration != '', m.duration, 'Yearly') as duration,
                IF(m.shift != '', m.shift, 'Both') as shift,
                m.start_date,
                m.end_date,
                CAST(m.membership_fee AS DECIMAL(10,2)) as membership_fee,
                m.payment_id,
                'Initial Joining' as action_type,
                m.created_at
            FROM members m
            LEFT JOIN membership_plans p ON m.membership_plan_id = p.id
            WHERE m.approved = 1 AND m.membership_id != ''");
    }
}

// Auto-migration check: work_shifts table for dynamic shift time definitions
$db->query("CREATE TABLE IF NOT EXISTS work_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL
)");

// Insert default market standard shifts if empty
$shiftCountRes = $db->query("SELECT COUNT(*) c FROM work_shifts");
if ($shiftCountRes && (int)($shiftCountRes->fetch_assoc()['c'] ?? 0) === 0) {
    $db->query("INSERT INTO work_shifts (name, start_time, end_time) VALUES 
        ('Morning', '08:00:00', '14:00:00'),
        ('Evening', '14:00:00', '20:00:00'),
        ('Both', '08:00:00', '20:00:00')");
}

// Auto-migration check: login log tables for admin and member login audits
$db->query("CREATE TABLE IF NOT EXISTS admin_login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Success',
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS member_login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NULL,
    mobile VARCHAR(20) NOT NULL,
    member_name VARCHAR(100) DEFAULT NULL,
    shift VARCHAR(50) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Success',
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Update existing 'Both' log entries to 'Full Day'
$db->query("UPDATE member_login_logs SET shift = 'Full Day' WHERE shift = 'Both' OR shift = 'both'");
// Fix historical failed logs for unregistered users so shift is NULL
$db->query("UPDATE member_login_logs SET shift = NULL WHERE member_id IS NULL AND (member_name IS NULL OR member_name = '')");

// Auto-migration check: hold requests table for physical book reservations
$db->query("CREATE TABLE IF NOT EXISTS hold_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    physical_book_id INT NOT NULL,
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Active', 'Fulfilled', 'Cancelled') DEFAULT 'Active',
    FOREIGN KEY(member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY(physical_book_id) REFERENCES physical_books(id) ON DELETE CASCADE
)");

// Auto-migration check: renewal requests table for online member pass renewals
$db->query("CREATE TABLE IF NOT EXISTS renewal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    membership_plan_id INT NOT NULL,
    shift VARCHAR(50) DEFAULT 'Morning',
    payment_id VARCHAR(150) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_plan_id) REFERENCES membership_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
