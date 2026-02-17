<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

if ($_SESSION['user_role'] !== 'patient') {
    header("location: ../login.php");
    exit;
}

 $patient_id = $_SESSION['user_id'];

// Fetch all reports for the patient
 $reports_stmt = $pdo->prepare("SELECT r.*, h.name AS hospital_name FROM medical_reports r JOIN hospitals h ON r.hospital_id = h.hospital_id WHERE r.patient_id = ? ORDER BY r.upload_date DESC");
 $reports_stmt->execute([$patient_id]);
 $reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Your Medical Reports</h2>
        
        <?php if (count($reports) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Report Type</th>
                            <th>Hospital</th>
                            <th>Date Uploaded</th>
                            <th>Notes</th>
                            <th>File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['report_type']); ?></td>
                            <td><?php echo htmlspecialchars($report['hospital_name']); ?></td>
                            <td><?php echo date('F j, Y', strtotime($report['upload_date'])); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($report['notes'])); ?></td>
                            <td>
                                <?php if (!empty($report['file_path']) && file_exists($report['file_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($report['file_path']); ?>" target="_blank" class="btn btn-success">View/Download</a>
                                <?php else: ?>
                                    <span>N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card">
                <p>You have no medical reports in the system yet.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>