<?php
require_once '../includes/auth_check.php';
require_once '../db_connect.php';

if ($_SESSION['user_role'] !== 'doctor') {
    header("location: ../login.php");
    exit;
}

if (!isset($_GET['patient_id']) || !is_numeric($_GET['patient_id'])) {
    header("location: dashboard.php");
    exit;
}

 $patient_id = $_GET['patient_id'];
 $doctor_id = $_SESSION['user_id'];

// Fetch patient details to display
 $patient_stmt = $pdo->prepare("SELECT name, phone FROM patients WHERE patient_id = ?");
 $patient_stmt->execute([$patient_id]);
 $patient = $patient_stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}

 $prescribe_error = '';
 $prescribe_success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prescription_details = trim($_POST['prescription_details']);

    if (empty($prescription_details)) {
        $prescribe_error = "Prescription details cannot be empty.";
    } else {
        $sql = "INSERT INTO prescriptions (patient_id, doctor_id, prescription_details) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$patient_id, $doctor_id, $prescription_details])) {
            $prescribe_success = "Prescription added successfully!";
            // Redirect back to the patient's view page after a short delay
            header("refresh:2;url=find_patient.php?phone=" . urlencode($patient['phone']));
        } else {
            $prescribe_error = "Failed to add prescription. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Prescribe for Patient: <?php echo htmlspecialchars($patient['name']); ?></h2>
        <p><strong>Patient Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
        
        <div class="form-container">
            <?php if (!empty($prescribe_error)): ?>
                <div class="alert alert-error"><?php echo $prescribe_error; ?></div>
            <?php endif; ?>
            <?php if (!empty($prescribe_success)): ?>
                <div class="alert alert-success"><?php echo $prescribe_success; ?></div>
            <?php endif; ?>
            <form action="prescribe.php?patient_id=<?php echo $patient_id; ?>" method="post">
                <div class="form-group">
                    <label for="prescription_details">Prescription Details</label>
                    <textarea name="prescription_details" id="prescription_details" rows="12" placeholder="e.g.,&#10;1. Medicine A - 1 tablet after meal, twice a day.&#10;2. Medicine B - 5ml syrup before sleeping.&#10;3. Advice: Rest and drink plenty of fluids." required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Submit Prescription</button>
                <a href="find_patient.php?phone=<?php echo urlencode($patient['phone']); ?>" class="btn btn-danger" style="text-decoration: none; margin-left: 10px;">Cancel</a>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>