
<?php
session_start();
// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect based on user role
if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
} else {
    header("Location: teacher/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attend-It-All</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Attend-It-All</h1>
        <p>Redirecting to your dashboard...</p>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
