<?php
require_once '../includes/auth_check.php';
require_once '../db_connect.php';
// Add this at the top of admin/dashboard.php
 $status_message = '';
if (isset($_GET['status'])) {
    $status_message = htmlspecialchars($_GET['status']);
    echo '<div class="container" style="margin-top: 1rem;"><div class="alert alert-success">' . $status_message . '</div></div>';
}

if ($_SESSION['user_role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

 $hospital_id = $_SESSION['user_id'];

// Get pending doctors for this hospital
 $pending_doctors_stmt = $pdo->prepare("SELECT doctor_id, name, email, phone, specialization FROM doctors WHERE hospital_id = ? AND is_approved = 0");
 $pending_doctors_stmt->execute([$hospital_id]);
 $pending_doctors = $pending_doctors_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get system stats (simplified)
 $total_patients_stmt = $pdo->query("SELECT COUNT(*) FROM patients");
 $total_patients = $total_patients_stmt->fetchColumn();

 $total_doctors_stmt = $pdo->query("SELECT COUNT(*) FROM doctors WHERE is_approved = 1");
 $total_doctors = $total_doctors_stmt->fetchColumn();

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Hospital Admin Dashboard</h2>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>Pending Doctor Approvals</h3>
                <?php if (count($pending_doctors) > 0): ?>
                     <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Specialization</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_doctors as $doc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['name']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['specialization']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['email']); ?></td>
                                    <td>
                                        <a href="approve_doctors.php?approve_id=<?php echo $doc['doctor_id']; ?>" class="btn btn-success">Approve</a>
                                        <a href="approve_doctors.php?reject_id=<?php echo $doc['doctor_id']; ?>" class="btn btn-danger">Reject</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No pending doctor registrations.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>System Overview</h3>
                <p><strong>Total Registered Patients:</strong> <?php echo $total_patients; ?></p>
                <p><strong>Total Approved Doctors (System-wide):</strong> <?php echo $total_doctors; ?></p>
            </div>

            <div class="card">
                <h3>Management Tools</h3>
                <a href="add_report.php" class="btn" style="margin-bottom: 10px; display:block;">Add Patient Report</a>
                <a href="post_notice.php" class="btn" style="display:block;">Post Hospital Notice</a>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>