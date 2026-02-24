<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

if ($_SESSION['user_role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$hospitals = $pdo->query("SELECT hospital_id, name FROM hospitals")
                 ->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $hospital_id = $_POST['hospital_id'];
    $department_id = $_POST['department_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_datetime = $_POST['appointment_datetime'];
    $patient_id = $_SESSION['user_id'];

    // Security check: verify doctor belongs to hospital & department
    $check = $pdo->prepare("
        SELECT hospital_id, department_id 
        FROM doctors 
        WHERE doctor_id = ? AND is_approved = TRUE
    ");
    $check->execute([$doctor_id]);
    $doctor = $check->fetch(PDO::FETCH_ASSOC);

    if (!$doctor ||
        $doctor['hospital_id'] != $hospital_id ||
        $doctor['department_id'] != $department_id) {

        $error = "Invalid doctor selection.";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO appointments 
            (patient_id, doctor_id, hospital_id, appointment_datetime)
            VALUES (?, ?, ?, ?)
        ");

        if ($stmt->execute([$patient_id, $doctor_id, $hospital_id, $appointment_datetime])) {
            header("Location: dashboard.php?appointment_success=1");
            exit;
        } else {
            $error = "Failed to book appointment.";
        }
    }
}

include '../includes/header.php';
?>

<main>
<div class="form-container">
    <h2>Book Appointment</h2>

    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <!-- Hospital -->
        <div class="form-group">
            <label>Select Hospital</label>
            <select name="hospital_id" id="hospital_id" required>
                <option value="">--Select Hospital--</option>
                <?php foreach ($hospitals as $hospital): ?>
                    <option value="<?= $hospital['hospital_id']; ?>">
                        <?= htmlspecialchars($hospital['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Department -->
        <div class="form-group">
            <label>Select Department</label>
            <select name="department_id" id="department_id" required disabled>
                <option value="">--Select Department--</option>
            </select>
        </div>

        <!-- Doctor -->
        <div class="form-group">
            <label>Select Doctor</label>
            <select name="doctor_id" id="doctor_id" required disabled>
                <option value="">--Select Doctor--</option>
            </select>
        </div>

        <!-- Date & Time -->
        <div class="form-group">
            <label>Appointment Date & Time</label>
            <input type="datetime-local" name="appointment_datetime" required>
        </div>

        <button type="submit" class="btn">Book Appointment</button>

    </form>
</div>
</main>

<script>
const hospitalSelect = document.getElementById('hospital_id');
const departmentSelect = document.getElementById('department_id');
const doctorSelect = document.getElementById('doctor_id');

hospitalSelect.addEventListener('change', function() {

    const hospitalId = this.value;

    departmentSelect.innerHTML = '<option>Loading...</option>';
    departmentSelect.disabled = true;
    doctorSelect.innerHTML = '<option>--Select Doctor--</option>';
    doctorSelect.disabled = true;

    if (!hospitalId) {
        departmentSelect.innerHTML = '<option>--Select Department--</option>';
        return;
    }

    fetch('get_departments.php?hospital_id=' + hospitalId)
        .then(res => res.json())
        .then(data => {

            departmentSelect.innerHTML = '<option value="">--Select Department--</option>';

            data.forEach(dept => {
                departmentSelect.innerHTML +=
                    `<option value="${dept.department_id}">
                        ${dept.department_name}
                    </option>`;
            });

            departmentSelect.disabled = false;
        });
});

departmentSelect.addEventListener('change', function() {

    const hospitalId = hospitalSelect.value;
    const departmentId = this.value;

    doctorSelect.innerHTML = '<option>Loading...</option>';
    doctorSelect.disabled = true;

    if (!departmentId) {
        doctorSelect.innerHTML = '<option>--Select Doctor--</option>';
        return;
    }

    fetch(`get_doctors.php?hospital_id=${hospitalId}&department_id=${departmentId}`)
        .then(res => res.json())
        .then(data => {

            doctorSelect.innerHTML = '<option value="">--Select Doctor--</option>';

            data.forEach(doc => {
                doctorSelect.innerHTML +=
                    `<option value="${doc.doctor_id}">
                        ${doc.name} - ${doc.specialization}
                    </option>`;
            });

            doctorSelect.disabled = false;
        });
});
</script>

<?php include '../includes/footer.php'; ?>