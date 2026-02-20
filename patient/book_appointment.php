<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

// --- AJAX Endpoint to get all hospitals ---
if (isset($_GET['get_hospitals'])) {
    $hospitals = $pdo->query("SELECT hospital_id, name FROM hospitals")->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($hospitals);
}

// --- AJAX Endpoint to get departments based on hospital_id ---
if (isset($_GET['h_id'])) {
    $h_id = $_GET['h_id'];
    $stmt = $pdo->prepare("SELECT department_id, department_name FROM departments WHERE hospital_id = ?");
    $stmt->execute([$h_id]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($departments);
}

// --- AJAX Endpoint to get doctors based on department_id ---
if (isset($_GET['d_id'])) {
    $d_id = $_GET['d_id'];
    $stmt = $pdo->prepare("SELECT doctor_id, name, specialization FROM doctors WHERE department_id = ? AND is_approved = 1");
    $stmt->execute([$d_id]);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($doctors);
}

// --- Main Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $h_id = $_POST['hospital_id'];
    $d_id = $_POST['department_id'];
    $doc_id = $_POST['doctor_id'];
    $a_datetime = $_POST['appointment_datetime'];
    $p_id = $_SESSION['patient_id'];

    // --- Validation ---
    if (empty($h_id) || empty($d_id) || empty($doc_id) || empty($a_datetime)) {
        $error = "Please fill in all required fields.";
    } else {
        // --- Insert into Database ---
        $sql = "INSERT INTO appointments (patient_id, doctor_id, hospital_id, department_id, appointment_datetime) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$p_id, $doc_id, $h_id, $d_id, $a_datetime]);
        header("location: dashboard.php?appointment_success=1");
    }
}

// --- Page Content ---
include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        .form-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .form-control {
            display: block;
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: #f9fafb;
            font-size: 1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            color: #ffffff;
            background-color: #3b82f6;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Book an Appointment</h2>
            <form action="book_appointment.php" method="POST">
                <div class="form-group">
                    <label for="hospital_id" class="form-label">Select Hospital</label>
                    <select name="hospital_id" id="hospital_id" class="form-control" required>
                        <option value="">-- Select a hospital --</option>
                        <?php
                        $hospitals = $pdo->query("SELECT hospital_id, name FROM hospitals")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($hospitals as $hospital) {
                            echo '<option value="' . $hospital['hospital_id'] . '">' . htmlspecialchars($hospital['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department_id" class="form-label">Select Department</label>
                    <select name="department_id" id="department_id" class="form-control" required>
                        <option value="">-- Select a department --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="doctor_id" class="form-label">Select Doctor</label>
                    <select name="doctor_id" id="doctor_id" class="form-control" required>
                        <option value="">-- Select a doctor --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="appointment_datetime" class="form-label">Appointment Date & Time</label>
                    <input type="datetime-local" name="appointment_datetime" id="appointment_datetime" class="form-control" required>
                </div>
                <button type="submit" class="btn">Book Appointment</button>
            </div>
        </div>
    </div>

    <script>
        // --- JavaScript for Dynamic Dropdowns ---
        document.addEventListener('DOMContentLoaded', function() {
            const hospitalSelect = document.getElementById('hospital_id');
            const departmentSelect = document.getElementById('department_id');
            const doctorSelect = document.getElementById('doctor_id');
            const datetimeInput = document.getElementById('appointment_datetime');

            // --- Fetch Hospitals ---
            fetch('book_appointment.php?get_hospitals=true')
                .then(response => response.json())
                .then(data => {
                    data.forEach(hospital => {
                        const option = document.createElement('option');
                        option.value = hospital.hospital_id;
                        option.textContent = hospital.name;
                        hospitalSelect.appendChild(option);
                    });
                });

            // --- Handle Hospital Change ---
            hospitalSelect.addEventListener('change', function() {
                // Clear other dropdowns
                departmentSelect.innerHTML = '<option value="">-- Select a department --</option>';
                doctorSelect.innerHTML = '<option value="">-- Select a doctor --</option>';

                const selectedHospitalId = hospitalSelect.value;
                if (selectedHospitalId) {
                    // --- Fetch Departments ---
                    fetch(`book_appointment.php?h_id=${selectedHospitalId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(department => {
                                const option = document.createElement('option');
                                option.value = department.department_id;
                                option.textContent = department.department_name;
                                departmentSelect.appendChild(option);
                            });
                        });
                }
            });

            // --- Handle Department Change ---
            departmentSelect.addEventListener('change', function() {
                const selectedDepartmentId = departmentSelect.value;
                if (selectedDepartmentId) {
                    // --- Fetch Doctors ---
                    fetch(`book_appointment.php?d_id=${selectedDepartmentId}`)
                        .then(response => response.json())
                        .then(data => {
                            doctorSelect.innerHTML = '<option value="">-- Select a doctor --</option>';
                            data.forEach(doctor => {
                                const option = document.createElement('option');
                                option.value = doctor.doctor_id;
                                option.textContent = `${doctor.name} - ${doctor.specialization}`;
                                doctorSelect.appendChild(option);
                            });
                        });
                }
            });
        });
    </script>
</body>
</html>