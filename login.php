<?php
require_once 'db_connect.php';
include 'includes/header.php';

 $login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $login_error = "Please enter both email and password.";
    } else {
        // Check patients table
        $sql = "SELECT patient_id as id, name, email, password_hash, 'patient' as role FROM patients WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If not found, check doctors table
        if (!$user) {
            $sql = "SELECT doctor_id as id, name, email, password_hash, 'doctor' as role FROM doctors WHERE email = :email AND is_approved = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // If still not found, check hospitals table
        if (!$user) {
            $sql = "SELECT hospital_id as id, name, email, password_hash, 'admin' as role FROM hospitals WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            // Password is correct, start a new session
            session_regenerate_id();
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect to the appropriate dashboard
            if ($user['role'] == 'patient') {
                header("location: patient/dashboard.php");
            } elseif ($user['role'] == 'doctor') {
                header("location: doctor/dashboard.php");
            } elseif ($user['role'] == 'admin') {
                header("location: admin/dashboard.php");
            }
            exit;
        } else {
            $login_error = "Invalid email or password, or your account is not yet approved.";
        }
    }
}
?>

<main>
    <div class="form-container">
        <h2>Login</h2>
        <?php if (!empty($login_error)): ?>
            <div class="alert alert-error"><?php echo $login_error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="post" autocomplete="off">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" autocomplete="off" required>
    </div>
    
    <!-- THIS IS THE NEW HIDDEN FIELD TO TRICK THE BROWSER -->
    <input type="password" style="display:none;" tabindex="-1" autocomplete="off">

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" autocomplete="new-password" required>
    </div>
    <button type="submit" class="btn">Login</button>
</form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>