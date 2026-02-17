<?php
require_once 'includes/db_connect.php';
session_start();

// Handle login logic BEFORE any HTML is sent
 $error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        // Check if user is a patient
        $stmt = $pdo->prepare("SELECT patient_id, name, password_hash FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Success! Set session variables and redirect
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['patient_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = 'patient';

            // Regenerate session ID for security
            session_regenerate_id(true);

            header("location: patient/dashboard.php");
            exit; // Stop script execution
        } else {
            // Check if user is a doctor
            $stmt = $pdo->prepare("SELECT doctor_id, name, password_hash, is_approved FROM doctors WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['is_approved'] == 1) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['doctor_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = 'doctor';
                    session_regenerate_id(true);
                    header("location: doctor/dashboard.php");
                    exit;
                } else {
                    $error_message = "Your account is pending approval from an administrator.";
                }
            } else {
                // Check if user is an admin
                $stmt = $pdo->prepare("SELECT hospital_id, name, password_hash FROM hospitals WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['hospital_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = 'admin';
                    session_regenerate_id(true);
                    header("location: admin/dashboard.php");
                    exit;
                } else {
                    $error_message = "Invalid email or password.";
                }
            }
        }
    }
}

// --- NOW, after all logic is done, we can include the header ---
include 'includes/header.php';
?>

<main>
    <div class="form-container">
        <h2>Login</h2>
        <?php if ($error_message): echo '<div class="alert alert-error">'.$error_message.'</div>'; endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p style="margin-top: 1rem;">Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>