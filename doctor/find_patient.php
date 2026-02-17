<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';
if ($_SESSION['user_role'] !== 'doctor') { header("location: ../login.php"); exit; }

 $patient = null;
 $reports = [];
 $prescriptions = [];

if (isset($_GET['phone'])) {
    $phone = trim($_GET['phone']);
    $patient_stmt = $pdo->prepare("SELECT * FROM patients WHERE phone = ?");
    $patient_stmt->execute([$phone]);
    $patient = $patient_stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        $patient_id = $patient['patient_id'];
        
        $reports_stmt = $pdo->prepare("SELECT r.*, h.name AS hospital_name FROM medical_reports r JOIN hospitals h ON r.hospital_id = h.hospital_id WHERE r.patient_id = ? ORDER BY r.upload_date DESC");
        $reports_stmt->execute([$patient_id]);
        $reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);

        $prescriptions_stmt = $pdo->prepare("SELECT p.*, d.name AS doctor_name, h.name AS hospital_name FROM prescriptions p JOIN doctors d ON p.doctor_id = d.doctor_id JOIN hospitals h ON d.hospital_id = h.hospital_id WHERE p.patient_id = ? ORDER BY p.issue_date DESC");
        $prescriptions_stmt->execute([$patient_id]);
        $prescriptions = $prescriptions_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Find Patient</h2>
        <div class="form-container" style="max-width: 600px;">
            <form action="find_patient.php" method="get">
                <div class="form-group">
                    <label for="phone">Enter Patient's Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?php echo isset($_GET['phone']) ? htmlspecialchars($_GET['phone']) : ''; ?>" required>
                </div>
                <button type="submit" class="btn">Search</button>
            </form>
        </div>

        <?php if ($patient): ?>
            <div class="card" style="margin-top: 2rem;">
                <h3>Patient Profile: <?php echo htmlspecialchars($patient['name']); ?></h3>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                <p><strong>DOB:</strong> <?php echo htmlspecialchars($patient['dob']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($patient['address']); ?></p>
                <hr>
                <a href="prescribe.php?patient_id=<?php echo $patient['patient_id']; ?>" class="btn btn-success">+ Add New Prescription</a>
            </div>

            <div class="dashboard-grid" style="margin-top: 2rem;">
                <div class="card">
                    <h3>Medical Reports</h3>
                    <?php if (count($reports) > 0): ?>
                        <ul>
                            <?php foreach ($reports as $report): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($report['report_type']); ?></strong> (<?php echo htmlspecialchars($report['hospital_name']); ?>)<br>
                                    <small><?php echo date('F j, Y', strtotime($report['upload_date'])); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No reports found for this patient.</p>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3>Prescription History</h3>
                     <?php if (count($prescriptions) > 0): ?>
                        <ul>
                            <?php foreach ($prescriptions as $pres): ?>
                                <li>
                                    <strong>Dr. <?php echo htmlspecialchars($pres['doctor_name']); ?></strong> (<?php echo htmlspecialchars($pres['hospital_name']); ?>)<br>
                                    <small><?php echo date('F j, Y', strtotime($pres['issue_date'])); ?></small>
                                    <p><?php echo nl2br(htmlspecialchars($pres['prescription_details'])); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No prescriptions found for this patient.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif (isset($_GET['phone'])): ?>
            <div class="alert alert-error" style="margin-top: 2rem;">No patient found with that phone number.</div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>