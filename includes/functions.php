
<?php
// Generate a random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is teacher
function isTeacher() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
}

// Redirect to login page if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

// Redirect to appropriate dashboard based on role
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: ../teacher/dashboard.php");
        exit();
    }
}

// Redirect to appropriate dashboard based on role
function requireTeacher() {
    requireLogin();
    if (!isTeacher()) {
        header("Location: ../admin/dashboard.php");
        exit();
    }
}

// Format date to readable format
function formatDate($date) {
    return date("F j, Y", strtotime($date));
}

// Check if date is valid
function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Process CSV file for student data
function processStudentCSV($file, $classId, $conn) {
    $students = [];
    $error = null;
    
    // Check if file is a CSV
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext != 'csv') {
        return ['success' => false, 'error' => 'Only CSV files are allowed'];
    }
    
    // Open the file
    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
        // Skip the header row
        $header = fgetcsv($handle, 1000, ",");
        
        // Prepare statement for student insertion
        $stmt = $conn->prepare("INSERT INTO students (class_id, name, enrollment_number) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $classId, $name, $enrollment);
        
        // Process each row
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 2) {
                $name = trim($data[0]);
                $enrollment = trim($data[1]);
                
                if (!empty($name) && !empty($enrollment)) {
                    $stmt->execute();
                    
                    if ($stmt->error) {
                        $error = "Error adding student: " . $stmt->error;
                        break;
                    }
                    
                    $students[] = [
                        'id' => $stmt->insert_id,
                        'name' => $name,
                        'enrollment_number' => $enrollment
                    ];
                }
            }
        }
        
        fclose($handle);
        $stmt->close();
        
        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        
        return ['success' => true, 'students' => $students];
    } else {
        return ['success' => false, 'error' => 'Failed to open file'];
    }
}

// Get classes by teacher ID
function getClassesByTeacherId($teacherId, $conn) {
    $classes = [];
    
    $query = "SELECT * FROM classes WHERE teacher_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Get students for this class
        $students = [];
        $student_query = "SELECT * FROM students WHERE class_id = ?";
        $student_stmt = $conn->prepare($student_query);
        $student_stmt->bind_param("i", $row['id']);
        $student_stmt->execute();
        $student_result = $student_stmt->get_result();
        
        while ($student = $student_result->fetch_assoc()) {
            $students[] = $student;
        }
        
        $student_stmt->close();
        
        // Add students to class data
        $row['students'] = $students;
        $classes[] = $row;
    }
    
    $stmt->close();
    
    return $classes;
}

// Get class by ID
function getClassById($classId, $conn) {
    $query = "SELECT c.*, t.name as teacher_name FROM classes c 
              JOIN teachers t ON c.teacher_id = t.id 
              WHERE c.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $class = $result->fetch_assoc();
        
        // Get students for this class
        $students = [];
        $student_query = "SELECT * FROM students WHERE class_id = ?";
        $student_stmt = $conn->prepare($student_query);
        $student_stmt->bind_param("i", $classId);
        $student_stmt->execute();
        $student_result = $student_stmt->get_result();
        
        while ($student = $student_result->fetch_assoc()) {
            $students[] = $student;
        }
        
        $student_stmt->close();
        
        // Add students to class data
        $class['students'] = $students;
        
        return $class;
    }
    
    $stmt->close();
    
    return null;
}

// Get attendance records by class ID
function getAttendanceByClassId($classId, $conn) {
    $records = [];
    
    $query = "SELECT * FROM attendance WHERE class_id = ? ORDER BY date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Get student attendance records
        $detail_query = "SELECT ar.*, s.name, s.enrollment_number FROM attendance_records ar 
                        JOIN students s ON ar.student_id = s.id 
                        WHERE ar.attendance_id = ?";
        $detail_stmt = $conn->prepare($detail_query);
        $detail_stmt->bind_param("i", $row['id']);
        $detail_stmt->execute();
        $detail_result = $detail_stmt->get_result();
        
        $student_records = [];
        while ($record = $detail_result->fetch_assoc()) {
            $student_records[] = $record;
        }
        
        $detail_stmt->close();
        
        // Add student records to attendance data
        $row['student_records'] = $student_records;
        $records[] = $row;
    }
    
    $stmt->close();
    
    return $records;
}

// Get all attendance records for admin
function getAllAttendanceRecords($conn) {
    $records = [];
    
    $query = "SELECT a.*, c.subject, c.branch, t.name as teacher_name 
              FROM attendance a 
              JOIN classes c ON a.class_id = c.id 
              JOIN teachers t ON c.teacher_id = t.id 
              ORDER BY a.date DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        // Get student attendance records
        $detail_query = "SELECT ar.*, s.name, s.enrollment_number FROM attendance_records ar 
                        JOIN students s ON ar.student_id = s.id 
                        WHERE ar.attendance_id = ?";
        $detail_stmt = $conn->prepare($detail_query);
        $detail_stmt->bind_param("i", $row['id']);
        $detail_stmt->execute();
        $detail_result = $detail_stmt->get_result();
        
        $student_records = [];
        while ($record = $detail_result->fetch_assoc()) {
            $student_records[] = $record;
        }
        
        $detail_stmt->close();
        
        // Add student records to attendance data
        $row['student_records'] = $student_records;
        $records[] = $row;
    }
    
    return $records;
}

// Get all classes for admin
function getAllClasses($conn) {
    $classes = [];
    
    $query = "SELECT c.*, t.name as teacher_name, COUNT(s.id) as student_count 
              FROM classes c 
              JOIN teachers t ON c.teacher_id = t.id 
              LEFT JOIN students s ON c.id = s.class_id 
              GROUP BY c.id 
              ORDER BY c.created_at DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    
    return $classes;
}

// Get all teachers for admin
function getAllTeachers($conn) {
    $teachers = [];
    
    $query = "SELECT t.*, COUNT(c.id) as class_count 
              FROM teachers t 
              LEFT JOIN classes c ON t.id = c.teacher_id 
              GROUP BY t.id 
              ORDER BY t.name";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    
    return $teachers;
}

// Get attendance summary
function getAttendanceSummary($conn, $classId = null, $date = null) {
    $where = [];
    $params = [];
    $types = "";
    
    $query = "SELECT COUNT(*) as total, 
              SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present,
              SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent,
              SUM(CASE WHEN ar.status = 'leave' THEN 1 ELSE 0 END) as on_leave
              FROM attendance_records ar
              JOIN attendance a ON ar.attendance_id = a.id";
    
    if ($classId) {
        $where[] = "a.class_id = ?";
        $params[] = $classId;
        $types .= "i";
    }
    
    if ($date) {
        $where[] = "a.date = ?";
        $params[] = $date;
        $types .= "s";
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_assoc();
    
    $stmt->close();
    
    return $summary;
}
?>
