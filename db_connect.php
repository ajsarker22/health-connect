<?php
// This file connects to either a local or a cloud database.

 $dbUrl = getenv('DATABASE_URL');

// If on Render (cloud)
if ($dbUrl !== false) {
    $dbParts = parse_url($dbUrl);
    // Added a check to ensure the password key exists
    $password = isset($dbParts['pass']) ? $dbParts['pass'] : '';
    
    $dsn = "pgsql:host=" . $dbParts['host'] . ";dbname=" . ltrim($dbParts['path'], '/');
    $user = $dbParts['user'];
} 
// If on local XAMPP
else {
    $dsn = "mysql:host=localhost;dbname=health_connect;charset=utf8mb4";
    $user = "root";
    $password = "";
}

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database. " . $e->getMessage());
}