
<?php
session_start();
require_once "includes/db_config.php";
require_once "includes/functions.php";

$error = "";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: teacher/dashboard.php");
    }
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $login_identifier = filter_input(INPUT_POST, 'login_identifier', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($login_identifier) || (empty($password) && $role !== 'admin') || empty($role)) {
        $error = "All fields are required";
    } else {
        // Connect to database
        $conn = getDbConnection();
        
        if ($conn) {
            // Prepare query based on role
            $table = ($role === 'admin') ? 'admin' : 'teachers';
            
            if ($role === 'admin') {
                // For admin, try to find by name
                $query = "SELECT * FROM $table WHERE name = ?";
            } else {
                // For teachers, look up by email
                $query = "SELECT * FROM $table WHERE email = ?";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $login_identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // For admin, update login time and verify admin code
                if ($role === 'admin') {
                    $admin_code = isset($_POST['admin_code']) ? $_POST['admin_code'] : '';
                    
                    if (verifyAdminCode($admin_code)) {
                        // Update login time for admin
                        $update_query = "UPDATE admin SET login_time = NOW() WHERE id = ?";
                        $update_stmt = $conn->prepare($update_query);
                        $update_stmt->bind_param("i", $user['id']);
                        $update_stmt->execute();
                        $update_stmt->close();
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $role;
                        
                        // Redirect to admin dashboard
                        header("Location: admin/dashboard.php");
                        exit();
                    } else {
                        $error = "Invalid admin code";
                    }
                } else {
                    // For teachers, verify password
                    if (password_verify($password, $user['password'])) {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $role;
                        
                        // Redirect to teacher dashboard
                        header("Location: teacher/dashboard.php");
                        exit();
                    } else {
                        $error = "Invalid email or password";
                    }
                }
            } else {
                if ($role === 'admin') {
                    $error = "Admin not found";
                } else {
                    $error = "Invalid email or password";
                }
            }
            
            $stmt->close();
            $conn->close();
        } else {
            $error = "Database connection failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Attend-It-All</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h1>Attend-It-All</h1>
            <p class="subtitle">Attendance Management System</p>
            
            <div class="card">
                <div class="card-header">
                    <h2>Welcome back</h2>
                    <p>Sign in to your account to continue</p>
                </div>
                
                <div class="card-body">
                    <div class="tabs">
                        <button class="tab active" data-tab="login">Login</button>
                        <button class="tab" data-tab="signup">Signup</button>
                    </div>
                    
                    <div class="tab-content">
                        <div class="role-selector">
                            <p>Select Role</p>
                            <div class="role-buttons">
                                <button type="button" class="role-btn active" data-role="admin">Admin</button>
                                <button type="button" class="role-btn" data-role="teacher">Teacher</button>
                            </div>
                        </div>
                        
                        <?php if (!empty($error)): ?>
                            <div class="error-message"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div id="login-form" class="form-section active">
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="form-group">
                                    <label id="login-label" for="login_identifier">Admin Name</label>
                                    <input type="text" id="login_identifier" name="login_identifier" placeholder="Enter admin name" required>
                                </div>
                                
                                <div class="form-group" id="password-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                                </div>
                                
                                <div class="form-group" id="admin-code-group" style="display: block;">
                                    <label for="admin_code">Admin Code</label>
                                    <input type="password" id="admin_code" name="admin_code" placeholder="Enter admin code (232774)" required>
                                </div>
                                
                                <input type="hidden" id="role" name="role" value="admin">
                                
                                <button type="submit" name="login" class="btn btn-primary">Sign In</button>
                            </form>
                        </div>
                        
                        <div id="signup-form" class="form-section">
                            <form method="POST" action="signup.php">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" id="name" name="name" placeholder="Enter your name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="signup-email">Email</label>
                                    <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="signup-password">Password</label>
                                    <input type="password" id="signup-password" name="password" placeholder="Create a password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>
                                </div>
                                
                                <button type="submit" name="signup" class="btn btn-primary">Create Account</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <p class="help-text">
                        For testing, use: <span>admin@example.com / teacher@example.com</span> with password: <span>password</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching logic
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all form sections
                document.querySelectorAll('.form-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                // Show the corresponding form section
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-form').classList.add('active');
                
                // If signup tab is clicked, set role to teacher only
                if (tabId === 'signup') {
                    document.querySelectorAll('.role-btn').forEach(btn => {
                        btn.classList.remove('active');
                        if (btn.getAttribute('data-role') === 'teacher') {
                            btn.classList.add('active');
                        }
                    });
                    document.getElementById('role').value = 'teacher';
                    
                    // Disable admin role button
                    document.querySelector('.role-btn[data-role="admin"]').setAttribute('disabled', 'disabled');
                }
                else {
                    // Enable admin role button
                    document.querySelector('.role-btn[data-role="admin"]').removeAttribute('disabled');
                }
            });
        });
        
        // Role selection logic
        document.querySelectorAll('.role-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (document.querySelector('.tab[data-tab="signup"]').classList.contains('active') && 
                    this.getAttribute('data-role') === 'admin') {
                    return; // Prevent admin role selection in signup tab
                }
                
                // Remove active class from all role buttons
                document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update hidden role input
                const role = this.getAttribute('data-role');
                document.getElementById('role').value = role;
                
                // Update form fields based on role
                if (role === 'admin') {
                    document.getElementById('login-label').textContent = 'Admin Name';
                    document.getElementById('login_identifier').placeholder = 'Enter admin name';
                    document.getElementById('admin-code-group').style.display = 'block';
                    document.getElementById('admin_code').required = true;
                    document.getElementById('password-group').style.display = 'none';
                    document.getElementById('password').required = false;
                } else {
                    document.getElementById('login-label').textContent = 'Email';
                    document.getElementById('login_identifier').placeholder = 'Enter your email';
                    document.getElementById('admin-code-group').style.display = 'none';
                    document.getElementById('admin_code').required = false;
                    document.getElementById('password-group').style.display = 'block';
                    document.getElementById('password').required = true;
                }
            });
        });
        
        // Initialize form based on default role (admin)
        document.getElementById('password-group').style.display = 'none';
        document.getElementById('password').required = false;
    </script>
</body>
</html>
