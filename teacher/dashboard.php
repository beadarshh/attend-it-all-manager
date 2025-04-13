
<?php
session_start();
require_once "../includes/db_config.php";
require_once "../includes/functions.php";

// Check if user is logged in and is a teacher
requireTeacher();

// Get teacher's classes
$conn = getDbConnection();
$classes = getClassesByTeacherId($_SESSION['user_id'], $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | Attend-It-All</title>
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
                <a href="add_class.php" class="nav-link">
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
                    <h1>Teacher Dashboard</h1>
                    <p>Manage your classes and attendance records</p>
                </div>
                
                <div class="content-actions">
                    <a href="add_class.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add New Class
                    </a>
                </div>
                
                <?php if (empty($classes)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="empty-title">No classes yet</h3>
                        <p class="empty-description">Start by adding your first class</p>
                        <a href="add_class.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add New Class
                        </a>
                    </div>
                <?php else: ?>
                    <div class="class-grid">
                        <?php foreach ($classes as $class): ?>
                            <div class="class-card">
                                <div class="class-card-header">
                                    <h3 class="class-title"><?php echo $class['subject']; ?></h3>
                                    <p class="class-subtitle"><?php echo $class['branch']; ?></p>
                                </div>
                                <div class="class-card-body">
                                    <div class="class-detail">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo count($class['students']); ?> Students</span>
                                    </div>
                                    <div class="class-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php echo $class['teaching_days']; ?></span>
                                    </div>
                                    <div class="class-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Year: <?php echo $class['year']; ?></span>
                                    </div>
                                </div>
                                <div class="class-card-footer">
                                    <a href="mark_attendance.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-primary">
                                        Mark Attendance
                                    </a>
                                    <a href="attendance_history.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline">
                                        View History
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
