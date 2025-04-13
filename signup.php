
<?php
session_start();
require_once "includes/db_config.php";
require_once "includes/functions.php";

$error = "";
$success = "";

// Handle signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $admin_code = isset($_POST['admin_code']) ? $_POST['admin_code'] : '';
    
    // Validate input based on role
    if ($role === 'admin') {
        // For admin, only name and correct admin code required
        if (empty($name)) {
            $error = "Name is required";
        } else if (!verifyAdminCode($admin_code)) {
            $error = "Invalid admin code";
        } else {
            // Generate a random password for admin
            $random_password = generateRandomString(10);
            $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
            
            // Admin email is auto-generated based on name
            $admin_email = strtolower(str_replace(' ', '.', $name)) . "@admin.attenditall.com";
            
            // Connect to database
            $conn = getDbConnection();
            
            if ($conn) {
                // Check if admin name already exists
                $check_query = "SELECT * FROM admin WHERE name = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("s", $name);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $error = "Admin with this name already exists";
                } else {
                    // Insert new admin
                    $insert_query = "INSERT INTO admin (name, email, password) VALUES (?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("sss", $name, $admin_email, $hashed_password);
                    
                    if ($insert_stmt->execute()) {
                        // Set session variables to log in admin immediately
                        $_SESSION['user_id'] = $insert_stmt->insert_id;
                        $_SESSION['name'] = $name;
                        $_SESSION['email'] = $admin_email;
                        $_SESSION['role'] = 'admin';
                        
                        // Redirect to admin dashboard
                        header("Location: admin/dashboard.php");
                        exit;
                    } else {
                        $error = "Error: " . $insert_stmt->error;
                    }
                    
                    $insert_stmt->close();
                }
                
                $check_stmt->close();
                $conn->close();
            } else {
                $error = "Database connection failed";
            }
        }
    } else {
        // For teachers, validate all fields
        if (empty($name) || empty($email) || empty($password)) {
            $error = "Name, email and password are required";
        } else if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters";
        } else if (empty($phone)) {
            $error = "Phone number is required for teacher accounts";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Connect to database
            $conn = getDbConnection();
            
            if ($conn) {
                // Check if email already exists in teachers table
                $check_query = "SELECT * FROM teachers WHERE email = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $error = "Email already exists";
                } else {
                    // Insert new teacher
                    $insert_query = "INSERT INTO teachers (name, email, password, phone) VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $phone);
                    
                    if ($insert_stmt->execute()) {
                        // Set session variables for immediate login
                        $_SESSION['user_id'] = $insert_stmt->insert_id;
                        $_SESSION['name'] = $name;
                        $_SESSION['email'] = $email;
                        $_SESSION['phone'] = $phone;
                        $_SESSION['role'] = 'teacher';
                        
                        // Redirect to teacher dashboard
                        header("Location: teacher/dashboard.php");
                        exit;
                    } else {
                        $error = "Error: " . $insert_stmt->error;
                    }
                    
                    $insert_stmt->close();
                }
                
                $check_stmt->close();
                $conn->close();
            } else {
                $error = "Database connection failed";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup | Attend-It-All</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="signup-container">
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <p><?php echo $success; ?></p>
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php else: ?>
                <h1>Create Account</h1>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="role-toggle">
                            <label>
                                <input type="radio" name="role_toggle" value="teacher" checked onclick="toggleSignupForm('teacher')"> Teacher
                            </label>
                            <label>
                                <input type="radio" name="role_toggle" value="admin" onclick="toggleSignupForm('admin')"> Admin
                            </label>
                        </div>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="signup-form">
                            <input type="hidden" id="role" name="role" value="teacher">
                            
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter your name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group" id="email-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group" id="password-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Create a password" required>
                                <small>Password must be at least 6 characters</small>
                            </div>
                            
                            <div class="form-group" id="phone-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group" id="admin-code-group" style="display: none;">
                                <label for="admin_code">Admin Code</label>
                                <input type="password" id="admin_code" name="admin_code" placeholder="Enter admin authorization code">
                                <small>Required for admin account creation (Code: 232774)</small>
                            </div>
                            
                            <div class="form-buttons">
                                <a href="login.php" class="btn btn-secondary">Back to Login</a>
                                <button type="submit" name="signup" class="btn btn-primary">Create Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleSignupForm(role) {
            document.getElementById('role').value = role;
            
            if (role === 'admin') {
                // For admin, hide email, password, and phone fields, only show name and admin code
                document.getElementById('email-group').style.display = 'none';
                document.getElementById('password-group').style.display = 'none';
                document.getElementById('phone-group').style.display = 'none';
                document.getElementById('admin-code-group').style.display = 'block';
                document.getElementById('email').required = false;
                document.getElementById('password').required = false;
                document.getElementById('phone').required = false;
                document.getElementById('admin_code').required = true;
            } else {
                // For teacher, show all fields except admin code
                document.getElementById('email-group').style.display = 'block';
                document.getElementById('password-group').style.display = 'block';
                document.getElementById('phone-group').style.display = 'block';
                document.getElementById('admin-code-group').style.display = 'none';
                document.getElementById('email').required = true;
                document.getElementById('password').required = true;
                document.getElementById('phone').required = true;
                document.getElementById('admin_code').required = false;
            }
        }
    </script>
</body>
</html>
