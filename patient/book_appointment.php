<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';
if ($_SESSION['user_role'] !== 'patient') { header("location: ../login.php"); exit; }

 $hospitals = $pdo->query("SELECT hospital_id, name FROM hospitals")->fetchAll(PDO::FETCH_ASSOC);
 $departments = [];
 $doctors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hospital_id = $_POST['hospital_id'];
    $department_id = $_POST['department_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_datetime = $_POST['appointment_datetime'];
    $patient_id = $_SESSION['user_id'];

    // Get hospital_id from selected doctor to ensure consistency
    $doc_hosp_stmt = $pdo->prepare("SELECT hospital_id FROM doctors WHERE doctor_id = ?");
    $doc_hosp_stmt->execute([$doctor_id]);
    $doc_hospital = $doc_hosp_stmt->fetch(PDO::FETCH_ASSOC);
    $final_hospital_id = $doc_hospital['hospital_id'];

    $sql = "INSERT INTO appointments (patient_id, doctor_id, hospital_id, appointment_datetime) VALUES (?, ?, ?, ?)";
    $stmt= $pdo->prepare($sql);
    if($stmt->execute([$patient_id, $doctor_id, $final_hospital_id, $appointment_datetime])){
        header("location: dashboard.php?appointment_success=1");
        exit;
    } else {
        $error = "Failed to book appointment. Please try again.";
    }
}

include '../includes/header.php';
?>

<main>
    <div class="form-container">
        <h2>Book an Appointment</h2>
        <?php if(isset($error)): echo '<div class="alert alert-error">'.$error.'</div>'; endif; ?>
        <form action="book_appointment.php" method="post">
            <div class="form-group">
                <label for="hospital_id">Select Hospital</label>
                <select name="hospital_id" id="hospital_id" required>
                    <option value="">--Select Hospital--</option>
                    <?php foreach($hospitals as $hospital): ?>
                        <option value="<?php echo $hospital['hospital_id']; ?>"><?php echo htmlspecialchars($hospital['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- In a real app, you'd use JS to populate the next two dropdowns based on the above selection -->
            <div class="form-group">
                <label for="department_id">Select Department</label>
                <select name="department_id" id="department_id" required>
                     <option value="">--Select Department--</option>
                     <?php 
                        // For simplicity, we'll just show all departments. In a real app, filter by hospital.
                        $all_depts = $pdo->query("SELECT department_id, department_name FROM departments")->fetchAll(PDO::FETCH_ASSOC);
                        foreach($all_depts as $dept): ?>
                        <option value="<?php echo $dept['department_id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="doctor_id">Select Doctor</label>
                <select name="doctor_id" id="doctor_id" required>
                     <option value="">--Select Doctor--</option>
                     <?php 
                        // For simplicity, show all approved doctors. In a real app, filter by hospital/department.
                        $all_docs = $pdo->query("SELECT doctor_id, name, specialization FROM doctors WHERE is_approved = TRUE")->fetchAll(PDO::FETCH_ASSOC);
                        foreach($all_docs as $doc): ?>
                        <option value="<?php echo $doc['doctor_id']; ?>"><?php echo htmlspecialchars($doc['name'] . ' - ' . $doc['specialization']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="appointment_datetime">Appointment Date & Time</label>
                <input type="datetime-local" name="appointment_datetime" id="appointment_datetime" required>
            </div>
            <button type="submit" class="btn">Book Appointment</button>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>