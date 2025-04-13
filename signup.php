
<?php
session_start();
require_once "includes/db_config.php";
require_once "includes/functions.php";

$error = "";
$success = "";

// Handle signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required";
    } else if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Connect to database
        $conn = getDbConnection();
        
        if ($conn) {
            // Check if email already exists
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
                    $success = "Account created successfully! Please login.";
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
                <h1>Create Teacher Account</h1>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter your name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Create a password" required>
                                <small>Password must be at least 6 characters</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
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
</body>
</html>
