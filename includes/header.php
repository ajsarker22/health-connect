<?php
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if BASE_URL is defined, if not, define it (as a fallback)
if (!defined('BASE_URL')) {
    // IMPORTANT: Change 'health_connect' if your project folder has a different name!
    define('BASE_URL', '/health_connect/');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If not logged in, show a simple header
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Connect BD</title>
    <link rel="stylesheet" href="' . BASE_URL . 'css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="' . BASE_URL . 'index.php" class="logo">HealthConnect BD</a>
            <nav>
                <a href="' . BASE_URL . 'login.php">Login</a>
                <a href="' . BASE_URL . 'register.php" class="btn">Register</a>
            </nav>
        </div>
    </header>';
} else {
    // If logged in, show role-specific navigation
    $role = $_SESSION['user_role'];
    $name = $_SESSION['user_name'];
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Connect BD</title>
    <link rel="stylesheet" href="' . BASE_URL . 'css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="' . BASE_URL . 'index.php" class="logo">HealthConnect BD</a>
            <nav>
                <span>Welcome, ' . htmlspecialchars($name) . ' (' . ucfirst($role) . ')</span>';
                
    // --- Dynamic Dashboard Link (This was already correct) ---
    if ($role == 'patient') {
        echo '<a href="' . BASE_URL . 'patient/dashboard.php">Dashboard</a>';
    } elseif ($role == 'doctor') {
        echo '<a href="' . BASE_URL . 'doctor/dashboard.php">Dashboard</a>';
    } elseif ($role == 'admin') {
        echo '<a href="' . BASE_URL . 'admin/dashboard.php">Dashboard</a>';
    }

    // --- Dynamic Profile Link (This is the fix) ---
    if ($role == 'patient') {
        echo '<a href="' . BASE_URL . 'patient/my_profile.php">My Profile</a>';
    } elseif ($role == 'doctor') {
        // Assuming you might create a doctor profile page later
        echo '<a href="' . BASE_URL . 'doctor/my_profile.php">My Profile</a>';
    } elseif ($role == 'admin') {
        // Assuming you might create an admin profile page later
        echo '<a href="' . BASE_URL . 'admin/my_profile.php">My Profile</a>';
    }

    echo '<a href="' . BASE_URL . 'logout.php">Logout</a>
            </nav>
        </div>
    </header>';
}
?>