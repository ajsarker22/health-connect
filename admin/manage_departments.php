<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

// Only hospital admins can access
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$hospital_id = $_SESSION['user_id']; // hospital admin's hospital ID

// Handle adding a new department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['department_name'])) {
    $dept_name = trim($_POST['department_name']);

    // Check if department already exists
    $check = $pdo->prepare("SELECT * FROM departments WHERE hospital_id = ? AND department_name = ?");
    $check->execute([$hospital_id, $dept_name]);

    if ($check->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO departments (hospital_id, department_name) VALUES (?, ?)");
        $stmt->execute([$hospital_id, $dept_name]);
        // Redirect to clear POST data and show success
        header("Location: manage_departments.php?success=Department+added+successfully");
        exit;
    } else {
        header("Location: manage_departments.php?error=Department+already+exists");
        exit;
    }
}

// Handle deleting a department
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $check = $pdo->prepare("SELECT * FROM departments WHERE department_id = ? AND hospital_id = ?");
    $check->execute([$delete_id, $hospital_id]);
    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("DELETE FROM departments WHERE department_id = ?");
        $stmt->execute([$delete_id]);
        header("Location: manage_departments.php?success=Department+deleted+successfully");
        exit;
    } else {
        header("Location: manage_departments.php?error=Cannot+delete:+Department+not+found+or+not+yours");
        exit;
    }
}

// Show success/error messages from GET parameters
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Get all departments for this hospital
$departments = $pdo->prepare("SELECT * FROM departments WHERE hospital_id = ?");
$departments->execute([$hospital_id]);
$departments = $departments->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<main>
<div class="container">
    <h2>Manage Departments</h2>

    <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <!-- Add Department Form -->
    <form method="POST" style="margin-bottom:30px;">
        <div class="form-group">
            <label>New Department Name</label>
            <input type="text" name="department_name" placeholder="Enter department name" required>
        </div>
        <button type="submit" class="btn">Add Department</button>
    </form>

    <h3>Existing Departments</h3>
    <?php if(count($departments) > 0): ?>
    <div class="dashboard-grid">
        <?php foreach($departments as $dept): ?>
        <div class="card" style="border-left: 5px solid var(--secondary-color); display:flex; flex-direction:column; justify-content:space-between;">
            <h3><?= htmlspecialchars($dept['department_name']); ?></h3>
            <div class="card-actions" style="margin-top:15px;">
                <!-- Delete button -->
                <a href="?delete_id=<?= $dept['department_id']; ?>" 
                   onclick="return confirm('Are you sure you want to delete this department?');"
                   class="btn btn-danger">
                   Delete
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p>No departments found. Add one above!</p>
    <?php endif; ?>
</div>
</main>

<?php include '../includes/footer.php'; ?>