<?php
require_once '../includes/auth_check.php';
require_once '../db_connect.php';
if ($_SESSION['user_role'] !== 'admin') { header("location: ../login.php"); exit; }

 $hospital_id = $_SESSION['user_id'];
 $message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_phone = trim($_POST['patient_phone']);
    $report_type = trim($_POST['report_type']);
    $notes = trim($_POST['notes']);
    
    // Find patient by phone
    $patient_stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE phone = ?");
    $patient_stmt->execute([$patient_phone]);
    $patient = $patient_stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        $patient_id = $patient['patient_id'];
        $file_path = ''; // Default path

        // Handle file upload
        if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] == 0) {
            $upload_dir = '../uploads/';
            $file_name = basename($_FILES["report_file"]["name"]);
            $target_file = $upload_dir . time() . '_' . $file_name; // Unique filename
            
            if (move_uploaded_file($_FILES["report_file"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
            } else {
                $message = '<div class="alert alert-error">Sorry, there was an error uploading your file.</div>';
            }
        }
        
        if(empty($message)) {
            $sql = "INSERT INTO medical_reports (patient_id, hospital_id, report_type, file_path, notes) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$patient_id, $hospital_id, $report_type, $file_path, $notes])) {
                $message = '<div class="alert alert-success">Report added successfully!</div>';
            } else {
                $message = '<div class="alert alert-error">Error adding report to database.</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-error">Patient with that phone number not found.</div>';
    }
}

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Add Patient Medical Report</h2>
        <?php echo $message; ?>
        <div class="form-container">
            <form action="add_report.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="patient_phone">Patient Phone Number</label>
                    <input type="tel" name="patient_phone" id="patient_phone" required>
                </div>
                <div class="form-group">
                    <label for="report_type">Report Type</label>
                    <input type="text" name="report_type" id="report_type" placeholder="e.g., Blood Test, X-Ray" required>
                </div>
                <div class="form-group">
                    <label for="report_file">Upload Report File (Optional)</label>
                    <input type="file" name="report_file" id="report_file">
                </div>
                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea name="notes" id="notes" rows="4"></textarea>
                </div>
                <button type="submit" class="btn">Add Report</button>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>