<?php
require_once '../includes/db_connect.php';

if (isset($_GET['hospital_id']) && !empty($_GET['hospital_id'])) {

    $hospital_id = $_GET['hospital_id'];

    $stmt = $pdo->prepare("
        SELECT department_id, department_name 
        FROM departments 
        WHERE hospital_id = ?
    ");

    $stmt->execute([$hospital_id]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}