<?php
require_once '../includes/auth_check.php';
require_once '../db_connect.php';

if ($_SESSION['user_role'] !== 'patient') {
    header("location: ../login.php");
    exit;
}

 $patient_id = $_SESSION['user_id'];
 $success_message = '';
 $error_message = '';

// Fetch the current patient data
 $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
 $stmt->execute([$patient_id]);
 $patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['dob']);
    $address = trim($_POST['address']);

    // Basic validation
    if (empty($name) || empty($email) || empty($phone)) {
        $error_message = "Name, Email, and Phone are required.";
    } else {
        $sql = "UPDATE patients SET name = ?, email = ?, phone = ?, dob = ?, address = ? WHERE patient_id = ?";
        $stmt= $pdo->prepare($sql);
        if($stmt->execute([$name, $email, $phone, $dob, $address, $patient_id])){
            $success_message = "Profile updated successfully!";
            // Refresh the data to display updated values
            $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = "Failed to update profile. Please try again.";
        }
    }
}

// Handle password change form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirmation do not match.";
    } else {
        // Verify current password
        if (password_verify($current_password, $patient['password_hash'])) {
            // Hash the new password
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE patients SET password_hash = ? WHERE patient_id = ?";
            $stmt= $pdo->prepare($sql);
            if($stmt->execute([$new_hashed_password, $patient_id])){
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Failed to change password. Please try again.";
            }
        } else {
            $error_message = "The current password you entered is incorrect.";
        }
    }
}

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>My Profile</h2>

        <?php if ($success_message): echo '<div class="alert alert-success">'.$success_message.'</div>'; endif; ?>
        <?php if ($error_message): echo '<div class="alert alert-error">'.$error_message.'</div>'; endif; ?>

        <div class="form-container">
            <h3>Update Personal Information</h3>
            <form action="my_profile.php" method="post">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($patient['dob']); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" rows="3"><?php echo htmlspecialchars($patient['address']); ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>
        </div>

        <div class="form-container" style="margin-top: 2rem;">
            <h3>Change Password</h3>
            <form action="my_profile.php" method="post">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>