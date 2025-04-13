
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Change this to your database username
define('DB_PASS', '');         // Change this to your database password
define('DB_NAME', 'attendance_system');

// Create database connection
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    // Check connection
    if ($conn->connect_error) {
        return false;
    }
    
    // Check if database exists, if not create it
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql) === FALSE) {
        return false;
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    // Create necessary tables if they don't exist
    createTables($conn);
    
    return $conn;
}

// Create necessary tables
function createTables($conn) {
    // Admin table
    $admin_table = "CREATE TABLE IF NOT EXISTS admin (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Teachers table
    $teachers_table = "CREATE TABLE IF NOT EXISTS teachers (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Classes table
    $classes_table = "CREATE TABLE IF NOT EXISTS classes (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT(11) NOT NULL,
        subject VARCHAR(100) NOT NULL,
        branch VARCHAR(100) NOT NULL,
        year VARCHAR(20) NOT NULL,
        teaching_days VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    )";
    
    // Students table
    $students_table = "CREATE TABLE IF NOT EXISTS students (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        class_id INT(11) NOT NULL,
        name VARCHAR(100) NOT NULL,
        enrollment_number VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )";
    
    // Attendance table
    $attendance_table = "CREATE TABLE IF NOT EXISTS attendance (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        class_id INT(11) NOT NULL,
        date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )";
    
    // Attendance records table
    $attendance_records_table = "CREATE TABLE IF NOT EXISTS attendance_records (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        attendance_id INT(11) NOT NULL,
        student_id INT(11) NOT NULL,
        status ENUM('present', 'absent', 'leave') NOT NULL,
        FOREIGN KEY (attendance_id) REFERENCES attendance(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )";
    
    // Execute table creation queries
    $conn->query($admin_table);
    $conn->query($teachers_table);
    $conn->query($classes_table);
    $conn->query($students_table);
    $conn->query($attendance_table);
    $conn->query($attendance_records_table);
    
    // Check if default admin account exists, if not create it
    $admin_check = "SELECT * FROM admin WHERE email = 'admin@example.com'";
    $result = $conn->query($admin_check);
    
    if ($result->num_rows == 0) {
        $admin_password = password_hash('password', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO admin (name, email, password) VALUES ('Admin', 'admin@example.com', '$admin_password')";
        $conn->query($insert_admin);
    }
    
    // Check if default teacher account exists, if not create it
    $teacher_check = "SELECT * FROM teachers WHERE email = 'teacher@example.com'";
    $result = $conn->query($teacher_check);
    
    if ($result->num_rows == 0) {
        $teacher_password = password_hash('password', PASSWORD_DEFAULT);
        $insert_teacher = "INSERT INTO teachers (name, email, password, phone) VALUES ('Teacher', 'teacher@example.com', '$teacher_password', '1234567890')";
        $conn->query($insert_teacher);
    }
}
?>
