
<?php
session_start();
require_once "../includes/db_config.php";
require_once "../includes/functions.php";

// Check if user is logged in and is an admin
requireAdmin();

// Get database connection
$conn = getDbConnection();

// Get all classes, teachers, and attendance records for summary
$classes = getAllClasses($conn);
$teachers = getAllTeachers($conn);
$attendance_records = getAllAttendanceRecords($conn);

// Get classes and dates for filtering
$filter_classes = $classes;
$filter_dates = [];
foreach ($attendance_records as $record) {
    $filter_dates[$record['date']] = $record['date'];
}

// Filter attendance records if filter is applied
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';

// Get attendance summary
$attendance_summary = getAttendanceSummary($conn, $selected_class, $selected_date);

// Calculate percentages
$total_records = $attendance_summary['total'] ?? 0;
$present_percentage = $total_records > 0 ? round(($attendance_summary['present'] / $total_records) * 100) : 0;
$absent_percentage = $total_records > 0 ? round(($attendance_summary['absent'] / $total_records) * 100) : 0;
$leave_percentage = $total_records > 0 ? round(($attendance_summary['on_leave'] / $total_records) * 100) : 0;

// Filtered attendance records for display
$filtered_records = [];
foreach ($attendance_records as $record) {
    if ((!empty($selected_class) && $record['class_id'] != $selected_class)) {
        continue;
    }
    if ((!empty($selected_date) && $record['date'] != $selected_date)) {
        continue;
    }
    $filtered_records[] = $record;
}

// Take only the latest 5 records for display
$display_records = array_slice($filtered_records, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Attend-It-All</title>
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
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="teachers.php" class="nav-link">
                    <i class="fas fa-chalkboard-teacher"></i> Teachers
                </a>
                <a href="classes.php" class="nav-link">
                    <i class="fas fa-book"></i> Classes
                </a>
                <a href="attendance.php" class="nav-link">
                    <i class="fas fa-clipboard-check"></i> Attendance
                </a>
                <a href="reports.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i> Reports
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
                        <a href="../logout.php" class="user-menu-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="content">
                <div class="content-header">
                    <h1>Admin Dashboard</h1>
                    <p>Monitor all attendance records across classes</p>
                </div>
                
                <div class="content-actions">
                    <button class="btn btn-primary" onclick="exportTableToCSV('attendance-table', 'attendance-data')">
                        <i class="fas fa-download"></i> Export All Data
                    </button>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Classes</div>
                        <div class="stat-value">
                            <i class="fas fa-book"></i>
                            <?php echo count($classes); ?>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Total Teachers</div>
                        <div class="stat-value">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <?php echo count($teachers); ?>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Attendance Records</div>
                        <div class="stat-value">
                            <i class="fas fa-clipboard-check"></i>
                            <?php echo count($attendance_records); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Overview -->
                <div class="card">
                    <div class="card-header">
                        <h2>Attendance Overview</h2>
                        
                        <div class="filters">
                            <form method="GET" action="" class="filters">
                                <div class="filter-group">
                                    <label for="class_filter" class="filter-label">Class:</label>
                                    <select name="class_id" id="class_filter" onchange="this.form.submit()">
                                        <option value="">All Classes</option>
                                        <?php foreach ($filter_classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($selected_class == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo $class['subject'] . ' - ' . $class['branch']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="filter-group">
                                    <label for="date_filter" class="filter-label">Date:</label>
                                    <select name="date" id="date_filter" onchange="this.form.submit()">
                                        <option value="">All Dates</option>
                                        <?php foreach ($filter_dates as $date): ?>
                                            <option value="<?php echo $date; ?>" <?php echo ($selected_date == $date) ? 'selected' : ''; ?>>
                                                <?php echo formatDate($date); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Attendance Summary Cards -->
                        <div class="stats-grid">
                            <div class="stat-card" style="background-color: #D1FAE5; border-color: #A7F3D0;">
                                <div class="stat-title" style="color: #065F46;">Present</div>
                                <div class="stat-value" style="color: #065F46;">
                                    <?php echo $attendance_summary['present'] ?? 0; ?>
                                </div>
                                <div style="color: #047857; font-size: 0.875rem; margin-top: 0.5rem;">
                                    <?php echo $present_percentage; ?>% of total
                                </div>
                            </div>
                            
                            <div class="stat-card" style="background-color: #FEE2E2; border-color: #FECACA;">
                                <div class="stat-title" style="color: #B91C1C;">Absent</div>
                                <div class="stat-value" style="color: #B91C1C;">
                                    <?php echo $attendance_summary['absent'] ?? 0; ?>
                                </div>
                                <div style="color: #B91C1C; font-size: 0.875rem; margin-top: 0.5rem;">
                                    <?php echo $absent_percentage; ?>% of total
                                </div>
                            </div>
                            
                            <div class="stat-card" style="background-color: #FEF3C7; border-color: #FDE68A;">
                                <div class="stat-title" style="color: #92400E;">On Leave</div>
                                <div class="stat-value" style="color: #92400E;">
                                    <?php echo $attendance_summary['on_leave'] ?? 0; ?>
                                </div>
                                <div style="color: #92400E; font-size: 0.875rem; margin-top: 0.5rem;">
                                    <?php echo $leave_percentage; ?>% of total
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Attendance Table -->
                        <h3 style="margin: 1.5rem 0 1rem;">Recent Attendance</h3>
                        
                        <div class="table-container">
                            <table id="attendance-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Class</th>
                                        <th>Teacher</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>On Leave</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($display_records)): ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center;">No attendance records found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($display_records as $record): ?>
                                            <?php 
                                                $present_count = 0;
                                                $absent_count = 0;
                                                $leave_count = 0;
                                                
                                                foreach ($record['student_records'] as $student_record) {
                                                    if ($student_record['status'] == 'present') {
                                                        $present_count++;
                                                    } else if ($student_record['status'] == 'absent') {
                                                        $absent_count++;
                                                    } else if ($student_record['status'] == 'leave') {
                                                        $leave_count++;
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo formatDate($record['date']); ?></td>
                                                <td><?php echo $record['subject'] . ' - ' . $record['branch']; ?></td>
                                                <td><?php echo $record['teacher_name']; ?></td>
                                                <td style="color: #065F46;"><?php echo $present_count; ?></td>
                                                <td style="color: #B91C1C;"><?php echo $absent_count; ?></td>
                                                <td style="color: #92400E;"><?php echo $leave_count; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($filtered_records) > 5): ?>
                            <div style="text-align: center; margin-top: 1rem;">
                                <a href="attendance.php" class="btn btn-sm btn-outline">View All Attendance Records</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
