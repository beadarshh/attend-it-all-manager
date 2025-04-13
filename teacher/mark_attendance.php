
<?php
session_start();
require_once "../includes/db_config.php";
require_once "../includes/functions.php";

// Check if user is logged in and is a teacher
requireTeacher();

$conn = getDbConnection();
$success = '';
$error = '';
$selected_class = '';
$class_data = null;
$today = date('Y-m-d');

// Get teacher's classes for dropdown
$classes = getClassesByTeacherId($_SESSION['user_id'], $conn);

// Handle class selection
if (isset($_GET['class_id'])) {
    $selected_class = $_GET['class_id'];
    $class_data = getClassById($selected_class, $conn);
}

// Handle attendance submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_attendance'])) {
    $class_id = filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_NUMBER_INT);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    
    // Validate input
    if (empty($class_id) || empty($date) || !isValidDate($date)) {
        $error = "Invalid class or date";
    } else {
        // Check if attendance for this class and date already exists
        $check_query = "SELECT * FROM attendance WHERE class_id = ? AND date = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("is", $class_id, $date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Attendance already exists, ask user if they want to update
            $error = "Attendance for this date already exists. <a href='attendance_history.php?class_id=" . $class_id . "'>View or edit it here</a>.";
        } else {
            // Insert attendance record
            $insert_query = "INSERT INTO attendance (class_id, date) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("is", $class_id, $date);
            
            if ($insert_stmt->execute()) {
                $attendance_id = $insert_stmt->insert_id;
                
                // Get students for this class
                $student_query = "SELECT * FROM students WHERE class_id = ?";
                $student_stmt = $conn->prepare($student_query);
                $student_stmt->bind_param("i", $class_id);
                $student_stmt->execute();
                $student_result = $student_stmt->get_result();
                
                // Insert attendance records for each student
                $record_stmt = $conn->prepare("INSERT INTO attendance_records (attendance_id, student_id, status) VALUES (?, ?, ?)");
                $record_stmt->bind_param("iis", $attendance_id, $student_id, $status);
                
                $successful_records = 0;
                while ($student = $student_result->fetch_assoc()) {
                    $student_id = $student['id'];
                    $status_key = "status_" . $student_id;
                    
                    if (isset($_POST[$status_key])) {
                        $status = $_POST[$status_key];
                        
                        if ($record_stmt->execute()) {
                            $successful_records++;
                        }
                    }
                }
                
                $record_stmt->close();
                $student_stmt->close();
                
                $success = "Attendance marked successfully for " . $successful_records . " students!";
                $selected_class = '';
                $class_data = null;
            } else {
                $error = "Error: " . $insert_stmt->error;
            }
            
            $insert_stmt->close();
        }
        
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance | Attend-It-All</title>
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
                <a href="add_class.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i> Add New Class
                </a>
                <a href="mark_attendance.php" class="nav-link active">
                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                </a>
                <a href="attendance_history.php" class="nav-link">
                    <i class="fas fa-history"></i> Attendance History
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
                    <h1>Mark Attendance</h1>
                    <p>Record daily student attendance</p>
                </div>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (empty($classes)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="empty-title">No classes available</h3>
                        <p class="empty-description">You need to add a class before you can mark attendance.</p>
                        <a href="add_class.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add New Class
                        </a>
                    </div>
                <?php else: ?>
                    <?php if (empty($selected_class)): ?>
                        <!-- Class selection form -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Select Class</h2>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="form-group">
                                        <label for="class_id">Select a class to mark attendance for:</label>
                                        <select id="class_id" name="class_id" required>
                                            <option value="">-- Select Class --</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>">
                                                    <?php echo $class['subject'] . ' - ' . $class['branch']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div style="text-align: right; margin-top: 1.5rem;">
                                        <button type="submit" class="btn btn-primary">Continue</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($class_data): ?>
                        <!-- Attendance marking form -->
                        <div class="card">
                            <div class="card-header">
                                <h2><?php echo $class_data['subject'] . ' - ' . $class_data['branch']; ?></h2>
                                <p>Mark attendance for this class</p>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="class_id" value="<?php echo $class_data['id']; ?>">
                                    
                                    <div class="form-group">
                                        <label for="date">Date:</label>
                                        <input type="date" id="date" name="date" value="<?php echo $today; ?>" required>
                                    </div>
                                    
                                    <div class="bulk-actions" style="margin-top: 1rem; margin-bottom: 1rem;">
                                        <button type="button" class="btn btn-sm btn-outline" onclick="markAllPresent()">
                                            <i class="fas fa-user-check"></i> Mark All Present
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline" onclick="markAllAbsent()">
                                            <i class="fas fa-user-times"></i> Mark All Absent
                                        </button>
                                    </div>
                                    
                                    <div class="table-container" style="margin-top: 1.5rem;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Enrollment No.</th>
                                                    <th>Student Name</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($class_data['students'])): ?>
                                                    <tr>
                                                        <td colspan="3" style="text-align: center;">No students in this class</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($class_data['students'] as $student): ?>
                                                        <tr>
                                                            <td><?php echo $student['enrollment_number']; ?></td>
                                                            <td><?php echo $student['name']; ?></td>
                                                            <td>
                                                                <div class="radio-group">
                                                                    <label class="radio-option">
                                                                        <input type="radio" name="status_<?php echo $student['id']; ?>" value="present" class="attendance-radio" checked> Present
                                                                    </label>
                                                                    <label class="radio-option">
                                                                        <input type="radio" name="status_<?php echo $student['id']; ?>" value="absent" class="attendance-radio"> Absent
                                                                    </label>
                                                                    <label class="radio-option">
                                                                        <input type="radio" name="status_<?php echo $student['id']; ?>" value="leave" class="attendance-radio"> Leave
                                                                    </label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="form-buttons" style="margin-top: 1.5rem;">
                                        <a href="mark_attendance.php" class="btn btn-secondary">Back</a>
                                        <button type="submit" name="submit_attendance" class="btn btn-primary">Submit Attendance</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <script>
                            function markAllPresent() {
                                document.querySelectorAll('.attendance-radio').forEach(radio => {
                                    if (radio.value === 'present') {
                                        radio.checked = true;
                                    }
                                });
                            }
                            
                            function markAllAbsent() {
                                document.querySelectorAll('.attendance-radio').forEach(radio => {
                                    if (radio.value === 'absent') {
                                        radio.checked = true;
                                    }
                                });
                            }
                        </script>
                    <?php else: ?>
                        <div class="error-message">Class not found. <a href="mark_attendance.php">Go back</a></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
