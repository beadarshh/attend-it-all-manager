
<?php
session_start();
require_once "../includes/db_config.php";
require_once "../includes/functions.php";

// Check if user is logged in and is a teacher
requireTeacher();

$conn = getDbConnection();
$success = '';
$error = '';
$attendance_id = '';
$attendance_data = null;
$class_data = null;

// Check if attendance ID is provided
if (isset($_GET['id'])) {
    $attendance_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    // Get attendance record
    $query = "SELECT a.*, c.id as class_id 
              FROM attendance a 
              JOIN classes c ON a.class_id = c.id 
              WHERE a.id = ? AND c.teacher_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $attendance_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $attendance_data = $result->fetch_assoc();
        
        // Get class data
        $class_data = getClassById($attendance_data['class_id'], $conn);
        
        // Get attendance records
        $record_query = "SELECT ar.*, s.name, s.enrollment_number 
                        FROM attendance_records ar 
                        JOIN students s ON ar.student_id = s.id 
                        WHERE ar.attendance_id = ?";
        $record_stmt = $conn->prepare($record_query);
        $record_stmt->bind_param("i", $attendance_id);
        $record_stmt->execute();
        $record_result = $record_stmt->get_result();
        
        $student_records = [];
        while ($record = $record_result->fetch_assoc()) {
            $student_records[$record['student_id']] = $record;
        }
        
        $attendance_data['student_records'] = $student_records;
        $record_stmt->close();
    }
    
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_attendance'])) {
    $attendance_id = filter_input(INPUT_POST, 'attendance_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Check if attendance belongs to this teacher
    $check_query = "SELECT a.* FROM attendance a 
                   JOIN classes c ON a.class_id = c.id 
                   WHERE a.id = ? AND c.teacher_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $attendance_id, $_SESSION['user_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update attendance records
        $update_stmt = $conn->prepare("UPDATE attendance_records SET status = ? WHERE attendance_id = ? AND student_id = ?");
        $update_stmt->bind_param("sii", $status, $attendance_id, $student_id);
        
        $success_count = 0;
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'status_') === 0) {
                $student_id = substr($key, 7); // Remove 'status_' prefix
                $status = $value;
                
                $update_stmt->execute();
                if ($update_stmt->affected_rows >= 0) {
                    $success_count++;
                }
            }
        }
        
        $update_stmt->close();
        
        if ($success_count > 0) {
            $success = "Attendance updated successfully for " . $success_count . " students.";
        } else {
            $error = "No changes were made or an error occurred.";
        }
    } else {
        $error = "You don't have permission to update this attendance record.";
    }
    
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance | Attend-It-All</title>
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
                <a href="mark_attendance.php" class="nav-link">
                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                </a>
                <a href="attendance_history.php" class="nav-link active">
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
                    <h1>Edit Attendance</h1>
                    <p>Update attendance record</p>
                </div>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                    <div style="text-align: center; margin: 1.5rem 0;">
                        <a href="attendance_history.php?class_id=<?php echo $attendance_data['class_id']; ?>" class="btn btn-primary">Back to Attendance History</a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($attendance_data && $class_data): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2><?php echo $class_data['subject'] . ' - ' . $class_data['branch']; ?></h2>
                            <p>Date: <?php echo formatDate($attendance_data['date']); ?></p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="attendance_id" value="<?php echo $attendance_id; ?>">
                                
                                <div class="table-container" style="margin-bottom: 1.5rem;">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Enrollment No.</th>
                                                <th>Student Name</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($class_data['students'] as $student): ?>
                                                <?php 
                                                    $current_status = 'present'; // Default status
                                                    if (isset($attendance_data['student_records'][$student['id']])) {
                                                        $current_status = $attendance_data['student_records'][$student['id']]['status'];
                                                    }
                                                ?>
                                                <tr>
                                                    <td><?php echo $student['enrollment_number']; ?></td>
                                                    <td><?php echo $student['name']; ?></td>
                                                    <td>
                                                        <div class="radio-group">
                                                            <label class="radio-option">
                                                                <input type="radio" name="status_<?php echo $student['id']; ?>" value="present" class="attendance-radio" <?php echo ($current_status == 'present') ? 'checked' : ''; ?>> Present
                                                            </label>
                                                            <label class="radio-option">
                                                                <input type="radio" name="status_<?php echo $student['id']; ?>" value="absent" class="attendance-radio" <?php echo ($current_status == 'absent') ? 'checked' : ''; ?>> Absent
                                                            </label>
                                                            <label class="radio-option">
                                                                <input type="radio" name="status_<?php echo $student['id']; ?>" value="leave" class="attendance-radio" <?php echo ($current_status == 'leave') ? 'checked' : ''; ?>> Leave
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="form-buttons">
                                    <a href="attendance_history.php?class_id=<?php echo $attendance_data['class_id']; ?>" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" name="update_attendance" class="btn btn-primary">Update Attendance</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="error-message">Attendance record not found or you don't have permission to edit it.</div>
                    <div style="text-align: center; margin: 1.5rem 0;">
                        <a href="attendance_history.php" class="btn btn-primary">Back to Attendance History</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
