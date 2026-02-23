<?php
require_once '../includes/db_connect.php';

if (
    isset($_GET['hospital_id']) &&
    isset($_GET['department_id']) &&
    !empty($_GET['hospital_id']) &&
    !empty($_GET['department_id'])
) {

    $hospital_id = $_GET['hospital_id'];
    $department_id = $_GET['department_id'];

    $stmt = $pdo->prepare("
        SELECT doctor_id, name, specialization
        FROM doctors
        WHERE hospital_id = ?
        AND department_id = ?
        AND is_approved = 1
    ");

    $stmt->execute([$hospital_id, $department_id]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}