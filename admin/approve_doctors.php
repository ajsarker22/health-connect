<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

// Ensure the user is an admin
if ($_SESSION['user_role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

 $hospital_id = $_SESSION['user_id'];
 $status_message = '';

// Check if an approve_id or reject_id is set in the URL
if (isset($_GET['approve_id']) && is_numeric($_GET['approve_id'])) {
    $doctor_id = $_GET['approve_id'];
    // First, ensure the doctor belongs to this admin's hospital before approving
    $stmt = $pdo->prepare("UPDATE doctors SET is_approved = 1 WHERE doctor_id = ? AND hospital_id = ?");
    if ($stmt->execute([$doctor_id, $hospital_id])) {
        $status_message = "Doctor approved successfully.";
    } else {
        $status_message = "Error approving doctor or doctor not found in your hospital.";
    }
} elseif (isset($_GET['reject_id']) && is_numeric($_GET['reject_id'])) {
    $doctor_id = $_GET['reject_id'];
    // Reject by deleting the registration request
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE doctor_id = ? AND hospital_id = ?");
    if ($stmt->execute([$doctor_id, $hospital_id])) {
        $status_message = "Doctor registration rejected.";
    } else {
        $status_message = "Error rejecting doctor or doctor not found in your hospital.";
    }
}

// Redirect back to the dashboard with a status message
header("location: dashboard.php?status=" . urlencode($status_message));
exit;
?>