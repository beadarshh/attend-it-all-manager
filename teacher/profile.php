
<?php
session_start();
require_once "../includes/db_config.php";
require_once "../includes/functions.php";

// Check if user is logged in and is a teacher
requireTeacher();

$conn = getDbConnection();
$success = '';
$error = '';

// Get teacher details
$teacher_id = $_SESSION['user_id'];
$query = "SELECT * FROM teachers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Validate input
    if (empty($name) || empty($email) || empty($phone)) {
        $error = "Name, email and phone are required";
    } else {
        // Check if email exists with another teacher
        $check_query = "SELECT * FROM teachers WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("si", $email, $teacher_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Email already exists with another account";
        } else {
            // Check if password change is requested
            if (!empty($current_password) && !empty($new_password)) {
                // Verify current password
                if (password_verify($current_password, $teacher['password'])) {
                    if (strlen($new_password) < 6) {
                        $error = "New password must be at least 6 characters";
                    } else {
                        // Update with new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_query = "UPDATE teachers SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_query);
                        $update_stmt->bind_param("ssssi", $name, $email, $phone, $hashed_password, $teacher_id);
                        
                        if ($update_stmt->execute()) {
                            $success = "Profile updated successfully with new password!";
                            // Update session variables
                            $_SESSION['name'] = $name;
                            $_SESSION['email'] = $email;
                            
                            // Refresh teacher data
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $teacher_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $teacher = $result->fetch_assoc();
                            $stmt->close();
                        } else {
                            $error = "Error updating profile: " . $update_stmt->error;
                        }
                        $update_stmt->close();
                    }
                } else {
                    $error = "Current password is incorrect";
                }
            } else {
                // Update without changing password
                $update_query = "UPDATE teachers SET name = ?, email = ?, phone = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("sssi", $name, $email, $phone, $teacher_id);
                
                if ($update_stmt->execute()) {
                    $success = "Profile updated successfully!";
                    // Update session variables
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    
                    // Refresh teacher data
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $teacher_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $teacher = $result->fetch_assoc();
                    $stmt->close();
                } else {
                    $error = "Error updating profile: " . $update_stmt->error;
                }
                $update_stmt->close();
            }
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
    <title>Profile | Attend-It-All</title>
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
                <a href="attendance_history.php" class="nav-link">
                    <i class="fas fa-history"></i> Attendance History
                </a>
                <a href="profile.php" class="nav-link active">
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
                    <h1>Profile</h1>
                    <p>Manage your account details</p>
                </div>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Your Profile</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher['phone']); ?>" required>
                            </div>
                            
                            <div class="divider">Change Password</div>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password">
                                <small>Leave blank if you don't want to change your password</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password">
                                <small>Must be at least 6 characters</small>
                            </div>
                            
                            <div style="text-align: right; margin-top: 1.5rem;">
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
