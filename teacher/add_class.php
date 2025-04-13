
<?php
session_start();
require_once "../includes/db_config.php";
require_once "../includes/functions.php";

// Check if user is logged in and is a teacher
requireTeacher();

$conn = getDbConnection();
$success = '';
$error = '';
$students = [];

// Handle class form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_class'])) {
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $branch = filter_input(INPUT_POST, 'branch', FILTER_SANITIZE_STRING);
    $year = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_STRING);
    $teaching_days = isset($_POST['days']) ? implode(',', $_POST['days']) : '';
    $teacher_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($subject) || empty($branch) || empty($year) || empty($teaching_days)) {
        $error = "All fields are required";
    } else {
        // Insert class into database
        $query = "INSERT INTO classes (teacher_id, subject, branch, year, teaching_days) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issss", $teacher_id, $subject, $branch, $year, $teaching_days);
        
        if ($stmt->execute()) {
            $class_id = $stmt->insert_id;
            
            // Add students from the session
            if (isset($_SESSION['uploaded_students']) && !empty($_SESSION['uploaded_students'])) {
                $student_count = 0;
                $student_stmt = $conn->prepare("INSERT INTO students (class_id, name, enrollment_number) VALUES (?, ?, ?)");
                $student_stmt->bind_param("iss", $class_id, $name, $enrollment);
                
                foreach ($_SESSION['uploaded_students'] as $student) {
                    $name = $student['name'];
                    $enrollment = $student['enrollment_number'];
                    
                    if ($student_stmt->execute()) {
                        $student_count++;
                    }
                }
                
                $student_stmt->close();
                unset($_SESSION['uploaded_students']);
                
                $success = "Class created successfully with $student_count students!";
            } else {
                $success = "Class created successfully!";
            }
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Handle CSV upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_csv'])) {
    if (isset($_FILES['student_file']) && $_FILES['student_file']['error'] == 0) {
        // Process the CSV file to get student data
        $result = processStudentCSV($_FILES['student_file'], 0, $conn); // 0 for temporary class_id
        
        if ($result['success']) {
            $_SESSION['uploaded_students'] = $result['students'];
            $students = $result['students'];
        } else {
            $error = $result['error'];
        }
    } else {
        $error = "Error uploading file: " . $_FILES['student_file']['error'];
    }
}

// Get students from session if already uploaded
if (isset($_SESSION['uploaded_students'])) {
    $students = $_SESSION['uploaded_students'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Class | Attend-It-All</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-heading">Attend-It-All</h2>
                <button class="sidebar-close"><i class="fas fa-times"></i></button>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="add_class.php" class="nav-link active">
                    <i class="fas fa-plus-circle"></i> Add New Class
                </a>
                <a href="mark_attendance.php" class="nav-link">
                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                </a>
                <a href="attendance_history.php" class="nav-link">
                    <i class="fas fa-history"></i> Attendance History
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profile
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <button class="menu-button">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="user-dropdown">
                    <button class="btn btn-outline user-button">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['name']; ?> <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-menu">
                        <a href="profile.php" class="user-menu-item">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="../logout.php" class="user-menu-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="content">
                <div class="content-header">
                    <h1>Add New Class</h1>
                    <p>Upload a student list and create a new class</p>
                </div>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($error)): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <!-- Upload student list first -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h2>Step 1: Upload Student List</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data" class="file-upload-form">
                                <div class="file-upload">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <p>Drag and drop your CSV file or</p>
                                    <label for="student_file" class="file-label">Browse Files</label>
                                    <input type="file" id="student_file" name="student_file" class="file-input" accept=".csv">
                                    <p class="file-hint">CSV file should have columns: Name, Enrollment Number</p>
                                    <?php if (isset($_FILES['student_file']) && $_FILES['student_file']['error'] == 0): ?>
                                        <p class="file-name"><?php echo $_FILES['student_file']['name']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: center; margin-top: 1rem;">
                                    <button type="submit" name="upload_csv" class="btn btn-primary">Upload Student List</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Show uploaded students and class form -->
                    <?php if (!empty($students)): ?>
                        <div class="card" style="margin-bottom: 2rem;">
                            <div class="card-header">
                                <h2>Uploaded Student List</h2>
                            </div>
                            <div class="card-body">
                                <p><?php echo count($students); ?> students loaded successfully</p>
                                
                                <div class="table-container" style="margin-top: 1rem;">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Enrollment Number</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo $student['name']; ?></td>
                                                    <td><?php echo $student['enrollment_number']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Class form -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Step 2: Create Class</h2>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="subject">Subject Name</label>
                                        <input type="text" id="subject" name="subject" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="branch">Branch/Section</label>
                                        <input type="text" id="branch" name="branch" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="year">Year/Semester</label>
                                        <input type="text" id="year" name="year" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Teaching Days</label>
                                        <div class="checkbox-group" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.5rem;">
                                            <label style="display: flex; align-items: center;">
                                                <input type="checkbox" name="days[]" value="Monday"> Monday
                                            </label>
                                            <label style="display: flex; align-items: center;">
                                                <input type="checkbox" name="days[]" value="Tuesday"> Tuesday
                                            </label>
                                            <label style="display: flex; align-items: center;">
                                                <input type="checkbox" name="days[]" value="Wednesday"> Wednesday
                                            </label>
                                            <label style="display: flex; align-items: center;">
                                                <input type="checkbox" name="days[]" value="Thursday"> Thursday
                                            </label>
                                            <label style="display: flex; align-items: center;">
                                                <input type="checkbox" name="days[]" value="Friday"> Friday
                                            </label>
                                            <label style="display: flex; align-items: center;">
                                                <input type="checkbox" name="days[]" value="Saturday"> Saturday
                                            </label>
                                            <label style="display: flex; align-items: center;">
                                                <input type="checkbox" name="days[]" value="Sunday"> Sunday
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div style="text-align: right; margin-top: 1.5rem;">
                                        <button type="submit" name="submit_class" class="btn btn-primary">Create Class</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
