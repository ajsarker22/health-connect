<?php
require_once 'db_connect.php';
include 'includes/header.php';

 $registration_error = '';

// Fetch data needed for dropdowns
 $hospitals = $pdo->query("SELECT hospital_id, name FROM hospitals")->fetchAll(PDO::FETCH_ASSOC);
 $all_departments = $pdo->query("SELECT department_id, department_name, d.hospital_id, h.name as hospital_name FROM departments d JOIN hospitals h ON d.hospital_id = h.hospital_id ORDER BY h.name, d.department_name")->fetchAll(PDO::FETCH_ASSOC);

// --- PROCESS THE FORM ---
if (!empty($_GET['name'])) {
    $name = trim($_GET['name']);
    $email = trim($_GET['email']);
    $password = trim($_GET['password']);
    $role = $_GET['role'];
    $phone = trim($_GET['phone']);
    
    if (empty($name) || empty($email) || empty($password) || empty($role) || empty($phone)) {
        $registration_error = "Please fill out all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_error = "Invalid email format.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            switch ($role) {
                case 'patient':
                    $dob = !empty($_GET['dob']) ? $_GET['dob'] : null;
                    $address = trim($_GET['address']);
                    $sql = "INSERT INTO patients (name, email, password_hash, phone, dob, address) VALUES (:name, :email, :password_hash, :phone, :dob, :address)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password_hash', $password_hash);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':dob', $dob);
                    $stmt->bindParam(':address', $address);
                    $stmt->execute();
                    
                    show_success_and_exit("Registration successful! You will be redirected to the login page.");
                    break;

                case 'doctor':
                    $specialization = trim($_GET['specialization']);
                    $department_id = $_GET['department_id'];

                    $hospital_lookup_stmt = $pdo->prepare("SELECT hospital_id FROM departments WHERE department_id = ?");
                    $hospital_lookup_stmt->execute([$department_id]);
                    $dept_info = $hospital_lookup_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if($dept_info) {
                        $hospital_id = $dept_info['hospital_id'];
                    } else {
                        throw new Exception("Selected department is not valid.");
                    }

                    $sql = "INSERT INTO doctors (name, email, password_hash, phone, specialization, department_id, hospital_id) VALUES (:name, :email, :password_hash, :phone, :specialization, :department_id, :hospital_id)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password_hash', $password_hash);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':specialization', $specialization);
                    $stmt->bindParam(':department_id', $department_id);
                    $stmt->bindParam(':hospital_id', $hospital_id);
                    $stmt->execute();

                    show_success_and_exit("Registration successful! Your account is now pending admin approval.");
                    break;

                case 'admin':
                    $address = trim($_GET['address']);
                    $sql = "INSERT INTO hospitals (name, email, password_hash, phone, address) VALUES (:name, :email, :password_hash, :phone, :address)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password_hash', $password_hash);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':address', $address);
                    $stmt->execute();
                    
                    show_success_and_exit("Registration successful! You will be redirected to the login page.");
                    break;
                
                default:
                    throw new Exception("Invalid role selected.");
            }
            
        } catch (Exception $e) {
            $registration_error = "Error: " . $e->getMessage();
        }
    }
}

// --- DEFINE A FUNCTION TO HANDLE SUCCESS ---
function show_success_and_exit($message) {
    ?>
    <main>
        <div class="form-container">
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
            <meta http-equiv="refresh" content="3;url=<?php echo BASE_URL; ?>login.php" />
            <p><a href="<?php echo BASE_URL; ?>login.php">Click here if you are not redirected automatically.</a></p>
        </div>
    </main>
    <?php
    include 'includes/footer.php';
    exit();
}
?>

<!-- --- DISPLAY THE FORM IF NO SUCCESS WAS SHOWN --- -->
<main>
    <div class="form-container">
        <h2>Register</h2>
        <?php if (!empty($registration_error)): ?>
            <div class="alert alert-error"><?php echo $registration_error; ?></div>
        <?php endif; ?>
        <form action="register.php" method="get" id="registrationForm">
            <div class="form-group">
                <label for="role">Register As</label>
                <select name="role" id="role" required>
                    <option value="">--Select Role--</option>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="admin">Hospital Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <!-- Patient Specific Fields -->
            <div id="patientFields" style="display:none;">
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" name="dob" id="dob">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" rows="3"></textarea>
                </div>
            </div>

            <!-- Doctor Specific Fields -->
            <div id="doctorFields" style="display:none;">
                 <div class="form-group">
                    <label for="department_id">Select Department</label>
                    <select name="department_id" id="department_id">
                        <option value="">--Select Department--</option>
                        <?php foreach($all_departments as $dept): ?>
                            <option value="<?php echo $dept['department_id']; ?>"><?php echo htmlspecialchars($dept['department_name'] . " (" . $dept['hospital_name'] . ")"); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input type="text" name="specialization" id="specialization" placeholder="More specific detail about your expertise">
                </div>
            </div>

            <!-- Admin Specific Fields -->
            <div id="adminFields" style="display:none;">
                 <div class="form-group">
                    <label for="address">Hospital Address</label>
                    <textarea name="address" id="address" rows="3"></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</main>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const patientFields = document.getElementById('patientFields');
    const doctorFields = document.getElementById('doctorFields');
    const adminFields = document.getElementById('adminFields');

    function toggleFields() {
        // Hide all fields first
        patientFields.style.display = 'none';
        doctorFields.style.display = 'none';
        adminFields.style.display = 'none';

        if (roleSelect.value === 'patient') {
            patientFields.style.display = 'block';
        } else if (roleSelect.value === 'doctor') {
            doctorFields.style.display = 'block';
        } else if (roleSelect.value === 'admin') {
            adminFields.style.display = 'block';
        }
    }

    // Add event listener to the dropdown
    roleSelect.addEventListener('change', toggleFields);
});
</script>

<?php include 'includes/footer.php'; ?>