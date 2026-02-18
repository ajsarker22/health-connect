<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

if ($_SESSION['user_role'] !== 'doctor') {
    header("location: ../login.php");
    exit;
}

 $doctor_id = $_SESSION['user_id'];

// Fetch the doctor's details to display
 $stmt = $pdo->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
 $stmt->execute([$doctor_id]);
 $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    die("Doctor not found.");
}

 $update_success = '';
 $update_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Handle profile update
    if (isset($_POST['update_profile'])) {
        if (empty($name) || empty($email) || empty($phone)) {
            $update_error = "Name, Email, and Phone are required.";
        } else {
            $sql = "UPDATE doctors SET name = ?, email = ?, phone = ?, specialization = ? WHERE doctor_id = ?";
            $stmt= $pdo->prepare($sql);
            if($stmt->execute([$name, $email, $phone, $specialization, $doctor_id])){
                $update_success = "Profile updated successfully!";
                // Refresh the data to display updated values
                $stmt = $pdo->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
                $stmt->execute([$doctor_id]);
                $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $update_error = "Failed to update profile. Please try again.";
            }
        }
    }

    // Handle password change
    if (isset($_POST['change_password'])) {
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $update_error = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $update_error = "New password and confirmation do not match.";
        } else {
            // Verify current password
            if (password_verify($current_password, $doctor['password_hash'])) {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE doctors SET password_hash = ? WHERE doctor_id = ?";
                $stmt= $pdo->prepare($sql);
                if($stmt->execute([$new_hashed_password, $doctor_id])){
                    $update_success = "Password changed successfully!";
                } else {
                    $update_error = "Failed to change password. Please try again.";
                }
            } else {
                $update_error = "The current password you entered is incorrect.";
            }
        }
    }
}

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>My Profile (Doctor)</h2>

        <?php if ($update_success): echo '<div class="alert alert-success">'.$update_success.'</div>'; endif; ?>
        <?php if ($update_error): echo '<div class="alert alert-error">'.$update_error.'</div>'; endif; ?>

        <div class="form-container">
            <h3>Update Personal Information</h3>
            <form action="my_profile.php" method="post">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
                </div>
                 <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input type="text" name="specialization" id="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>">
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