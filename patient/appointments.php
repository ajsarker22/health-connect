<?php
require_once '../includes/auth_check.php';
require_once '../db_connect.php';
if ($_SESSION['user_role'] !== 'patient') { header("location: ../login.php"); exit; }

 $patient_id = $_SESSION['user_id'];

// Handle cancellation
if (isset($_GET['cancel_id']) && is_numeric($_GET['cancel_id'])) {
    $appointment_id = $_GET['cancel_id'];
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ? AND patient_id = ? AND status = 'scheduled'");
    if ($stmt->execute([$appointment_id, $patient_id])) {
        $cancel_message = "Appointment cancelled successfully.";
    } else {
        $cancel_message = "Could not cancel appointment.";
    }
}

// Fetch all appointments
 $appointments_stmt = $pdo->prepare("SELECT a.*, d.name AS doctor_name, h.name AS hospital_name FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id JOIN hospitals h ON a.hospital_id = h.hospital_id WHERE a.patient_id = ? ORDER BY a.appointment_datetime DESC");
 $appointments_stmt->execute([$patient_id]);
 $appointments = $appointments_stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Your Appointments</h2>
        <?php if(isset($cancel_message)): echo '<div class="alert alert-success">'.$cancel_message.'</div>'; endif; ?>
        <a href="<?php echo BASE_URL; ?>patient/dashboard.php" class="btn" style="margin-bottom: 1rem;">← Back to Dashboard</a>
        <a href="<?php echo BASE_URL; ?>patient/book_appointment.php" class="btn" style="margin-bottom: 1rem;">+ Book New Appointment</a>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Doctor</th>
                        <th>Hospital</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td><?php echo date('F j, Y, g:i a', strtotime($apt['appointment_datetime'])); ?></td>
                            <td><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($apt['hospital_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $apt['status']; ?>">
                                    <?php echo ucfirst($apt['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($apt['status'] == 'scheduled'): ?>
                                    <a href="?cancel_id=<?php echo $apt['appointment_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?');">Cancel</a>
                                <?php else: ?>
                                    <span>N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">You have no appointments.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>