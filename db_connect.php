<?php
// This file connects to either a local or a cloud database.

// --- Check for a cloud database connection string ---
 $dbUrl = getenv('DATABASE_URL');

// --- If on Render (cloud) ---
if ($dbUrl !== false) {
    // Parse the connection string from the environment variable
    $dbParts = parse_url($dbUrl);
    $dsn = "pgsql:host=" . $dbParts['host'] . ";dbname=" . ltrim($dbParts['path'], '/');
    $user = $dbParts['user'];
    $password = $dbParts['password'];
} 
// --- If on local XAMPP ---
else {
    // Your original local settings
    $dsn = "mysql:host=localhost;dbname=health_connect;charset=utf8mb4";
    $user = "root";
    $password = "";
}

// --- Connect to the correct database ---
try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In a live app, you'd log this error instead of displaying it
    die("Could not connect to the database. " . $e->getMessage());
}