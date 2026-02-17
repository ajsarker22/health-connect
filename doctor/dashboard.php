<?php
require_once '../includes/auth_check.php';
require_once '../db_connect.php';

if ($_SESSION['user_role'] !== 'doctor') {
    header("location: ../login.php");
    exit;
}

 $doctor_id = $_SESSION['user_id'];

 // Get all upcoming appointments
 $upcoming_appointments_stmt = $pdo->prepare("SELECT a.*, p.name AS patient_name, p.phone AS patient_phone FROM appointments a JOIN patients p ON a.patient_id = p.patient_id WHERE a.doctor_id = ? AND a.appointment_datetime >= NOW() AND a.status = 'scheduled' ORDER BY a.appointment_datetime ASC");
 $upcoming_appointments_stmt->execute([$doctor_id]);
 $upcoming_appointments = $upcoming_appointments_stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Doctor Dashboard</h2>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>Today's Appointments</h3>
                <?php if (count($upcoming_appointments) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Patient Name</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcoming_appointments as $apt): ?>
                                <tr>
                                    <td><?php echo date('g:i a', strtotime($apt['appointment_datetime'])); ?></td>
                                    <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($apt['patient_phone']); ?></td>
                                    <td>
                                        <a href="find_patient.php?phone=<?php echo urlencode($apt['patient_phone']); ?>" class="btn btn-success">View Profile</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No appointments scheduled for today.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Quick Actions</h3>
                <p>Access patient records using their unique phone number.</p>
                <a href="find_patient.php" class="btn" style="margin-top: 1rem;">Find a Patient</a>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>