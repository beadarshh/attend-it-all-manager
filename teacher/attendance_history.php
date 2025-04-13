
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
$attendance_records = [];

// Get teacher's classes for dropdown
$classes = getClassesByTeacherId($_SESSION['user_id'], $conn);

// Handle class selection
if (isset($_GET['class_id'])) {
    $selected_class = $_GET['class_id'];
    $class_data = getClassById($selected_class, $conn);
    
    if ($class_data) {
        // Get attendance records for this class
        $attendance_records = getAttendanceByClassId($selected_class, $conn);
    }
}

// Handle attendance record deletion
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_SANITIZE_NUMBER_INT);
    
    $delete_query = "DELETE FROM attendance WHERE id = ? AND class_id IN (SELECT id FROM classes WHERE teacher_id = ?)";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $delete_id, $_SESSION['user_id']);
    
    if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
        $success = "Attendance record deleted successfully";
        
        // Refresh attendance records
        if (!empty($selected_class)) {
            $attendance_records = getAttendanceByClassId($selected_class, $conn);
        }
    } else {
        $error = "Error deleting attendance record";
    }
    
    $delete_stmt->close();
}

// Filters
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$enrollment_filter = isset($_GET['enrollment']) ? $_GET['enrollment'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History | Attend-It-All</title>
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
                    <h1>Attendance History</h1>
                    <p>View and manage attendance records</p>
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
                        <p class="empty-description">You need to add a class before you can view attendance history.</p>
                        <a href="add_class.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add New Class
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Class selection form -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="form-group">
                                    <label for="class_id">Select Class:</label>
                                    <select id="class_id" name="class_id" onchange="this.form.submit()">
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($selected_class == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo $class['subject'] . ' - ' . $class['branch']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($class_data): ?>
                        <!-- Attendance records table -->
                        <div class="card">
                            <div class="card-header">
                                <h2><?php echo $class_data['subject'] . ' - ' . $class_data['branch']; ?></h2>
                                
                                <div class="filters">
                                    <form method="GET" action="" class="filters">
                                        <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                                        
                                        <div class="filter-group">
                                            <label for="date" class="filter-label">Date:</label>
                                            <select name="date" id="date" onchange="this.form.submit()">
                                                <option value="">All Dates</option>
                                                <?php 
                                                $dates = [];
                                                foreach ($attendance_records as $record) {
                                                    $dates[$record['date']] = $record['date'];
                                                }
                                                foreach ($dates as $date): 
                                                ?>
                                                    <option value="<?php echo $date; ?>" <?php echo ($date_filter == $date) ? 'selected' : ''; ?>>
                                                        <?php echo formatDate($date); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="filter-group">
                                            <label for="status" class="filter-label">Status:</label>
                                            <select name="status" id="status" onchange="this.form.submit()">
                                                <option value="">All Statuses</option>
                                                <option value="present" <?php echo ($status_filter == 'present') ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent" <?php echo ($status_filter == 'absent') ? 'selected' : ''; ?>>Absent</option>
                                                <option value="leave" <?php echo ($status_filter == 'leave') ? 'selected' : ''; ?>>On Leave</option>
                                            </select>
                                        </div>
                                        
                                        <div class="filter-group">
                                            <label for="enrollment" class="filter-label">Enrollment:</label>
                                            <input type="text" name="enrollment" id="enrollment" value="<?php echo $enrollment_filter; ?>" placeholder="Search by enrollment number">
                                            <button type="submit" class="btn btn-sm btn-outline" style="margin-left: 0.5rem;">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($attendance_records)): ?>
                                    <div class="empty-state" style="padding: 2rem 0;">
                                        <h3 class="empty-title">No attendance records found</h3>
                                        <p class="empty-description">Start by marking attendance for this class.</p>
                                        <a href="mark_attendance.php?class_id=<?php echo $selected_class; ?>" class="btn btn-primary">
                                            <i class="fas fa-clipboard-check"></i> Mark Attendance
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- Export button -->
                                    <div style="text-align: right; margin-bottom: 1rem;">
                                        <button onclick="exportTableToCSV('attendance-table', 'attendance-data')" class="btn btn-outline">
                                            <i class="fas fa-download"></i> Export CSV
                                        </button>
                                    </div>
                                    
                                    <div class="table-container">
                                        <table id="attendance-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Enrollment No.</th>
                                                    <th>Student Name</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $displayed_records = [];
                                                
                                                foreach ($attendance_records as $record) {
                                                    // Apply date filter
                                                    if (!empty($date_filter) && $record['date'] != $date_filter) {
                                                        continue;
                                                    }
                                                    
                                                    foreach ($record['student_records'] as $student_record) {
                                                        // Apply status filter
                                                        if (!empty($status_filter) && $student_record['status'] != $status_filter) {
                                                            continue;
                                                        }
                                                        
                                                        // Apply enrollment filter
                                                        if (!empty($enrollment_filter) && stripos($student_record['enrollment_number'], $enrollment_filter) === false) {
                                                            continue;
                                                        }
                                                        
                                                        $displayed_records[] = true;
                                                ?>
                                                    <tr>
                                                        <td><?php echo formatDate($record['date']); ?></td>
                                                        <td><?php echo $student_record['enrollment_number']; ?></td>
                                                        <td><?php echo $student_record['name']; ?></td>
                                                        <td>
                                                            <span class="status-badge <?php echo 'status-' . $student_record['status']; ?>">
                                                                <?php echo ucfirst($student_record['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="table-actions">
                                                                <a href="edit_attendance.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="attendance_history.php?class_id=<?php echo $selected_class; ?>&delete_id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline" onclick="return confirm('Are you sure you want to delete this attendance record?');">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php
                                                    }
                                                }
                                                
                                                if (empty($displayed_records)):
                                                ?>
                                                    <tr>
                                                        <td colspan="5" style="text-align: center;">No attendance records match your filters</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
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
