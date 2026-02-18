<?php
// --- 1. SECURITY & DATABASE SETUP ---
require_once '../includes/auth_check.php'; // Ensures user is logged in
require_once '../includes/db_connect.php';         // Connects to the database and sets up BASE_URL

// Double-check that the user is a patient, not a doctor or admin.
if ($_SESSION['user_role'] !== 'patient') {
    header("location: ../login.php");
    exit;
}

 $patient_id = $_SESSION['user_id']; // Get the logged-in patient's ID

// --- 2. HANDLE DISMISSED NOTICES ---
// This code runs when a user clicks the 'x' on a notice.
if (isset($_GET['dismiss_notice_id']) && is_numeric($_GET['dismiss_notice_id'])) {
    // Create an array in the session to store dismissed notice IDs if it doesn't exist
    if (!isset($_SESSION['dismissed_notices'])) {
        $_SESSION['dismissed_notices'] = [];
    }
    // Add the ID of the notice to be dismissed to our session array
    $_SESSION['dismissed_notices'][] = $_GET['dismiss_notice_id'];
    
    // Redirect to a clean URL to prevent the notice from showing up again immediately.
    // This also prevents an infinite loop.
    header("Location: " . BASE_URL . "patient/dashboard.php");
    exit; // Stop the script from running further
}

// --- 3. FETCH DATA FROM DATABASE ---

// Fetch all active notices from all hospitals
 $notices_stmt = $pdo->query("SELECT n.notice_id, n.title, n.content, h.name AS hospital_name, n.posted_date FROM notices n JOIN hospitals h ON n.hospital_id = h.hospital_id WHERE n.is_active = TRUE ORDER BY n.posted_date DESC LIMIT 5");
 $all_notices = $notices_stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter out any notices the user has already dismissed in this session
 $notices = [];
if (!isset($_SESSION['dismissed_notices'])) {
    $_SESSION['dismissed_notices'] = []; // Ensure it's an array
}
foreach ($all_notices as $notice) {
    if (!in_array($notice['notice_id'], $_SESSION['dismissed_notices'])) {
        $notices[] = $notice; // Only keep notices that haven't been dismissed
    }
}

// Fetch the patient's upcoming appointments
 $appointments_stmt = $pdo->prepare("SELECT a.*, d.name AS doctor_name, h.name AS hospital_name FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id JOIN hospitals h ON a.hospital_id = h.hospital_id WHERE a.patient_id = ? AND a.status = 'scheduled' ORDER BY a.appointment_datetime ASC");
 $appointments_stmt->execute([$patient_id]);
 $upcoming_appointments = $appointments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the patient's recent medical reports
 $reports_stmt = $pdo->prepare("SELECT r.*, h.name AS hospital_name FROM medical_reports r JOIN hospitals h ON r.hospital_id = h.hospital_id WHERE r.patient_id = ? ORDER BY r.upload_date DESC LIMIT 5");
 $reports_stmt->execute([$patient_id]);
 $recent_reports = $reports_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the patient's recent prescriptions
 $prescriptions_stmt = $pdo->prepare("SELECT p.*, d.name AS doctor_name, h.name AS hospital_name FROM prescriptions p JOIN doctors d ON p.doctor_id = d.doctor_id JOIN hospitals h ON d.hospital_id = h.hospital_id WHERE p.patient_id = ? ORDER BY p.issue_date DESC LIMIT 5");
 $prescriptions_stmt->execute([$patient_id]);
 $recent_prescriptions = $prescriptions_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 4. DISPLAY THE HTML PAGE ---
include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Patient Dashboard</h2>
        
        <!-- Display Hospital Notices (if any) -->
        <?php if (count($notices) > 0): ?>
            <div class="card" style="border-left-color: var(--danger-color);">
                <h3 style="color: var(--danger-color);">📢 Latest Hospital Notices</h3>
                <?php foreach ($notices as $notice): ?>
                    <div style="border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex-grow: 1;">
                                <h4><?php echo htmlspecialchars($notice['title']); ?> - <small><?php echo htmlspecialchars($notice['hospital_name']); ?></small></h4>
                                <p><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
                                <small><em>Posted on: <?php echo date('F j, Y', strtotime($notice['posted_date'])); ?></em></small>
                            </div>
                            <div style="margin-left: 15px;">
                                <a href="?dismiss_notice_id=<?php echo $notice['notice_id']; ?>" style="text-decoration: none; color: #999; font-size: 1.2em;" title="Dismiss this notice">&times;</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <!-- Upcoming Appointments Card -->
            <div class="card">
                <h3>Upcoming Appointments</h3>
                <?php if (count($upcoming_appointments) > 0): ?>
                    <ul style="list-style-type: none; padding-left: 0;">
                        <?php foreach ($upcoming_appointments as $apt): ?>
                            <li style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                                <div>
                                    <strong><?php echo htmlspecialchars($apt['doctor_name']); ?></strong> at <?php echo htmlspecialchars($apt['hospital_name']); ?><br>
                                    <small><?php echo date('F j, Y, g:i a', strtotime($apt['appointment_datetime'])); ?></small>
                                </div>
                                <div>
                                    <a href="<?php echo BASE_URL; ?>patient/appointments.php?cancel_id=<?php echo $apt['appointment_id']; ?>" class="btn btn-danger" style="text-decoration: none; font-size: 0.9em; padding: 5px 10px;" onclick="return confirm('Are you sure you want to cancel this appointment?');">Cancel</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No upcoming appointments.</p>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>patient/book_appointment.php" class="btn" style="margin-top: 1rem;">Book New Appointment</a>
            </div>

            <!-- Recent Medical Reports Card -->
            <div class="card">
                <h3>Recent Medical Reports</h3>
                <?php if (count($recent_reports) > 0): ?>
                    <ul>
                        <?php foreach ($recent_reports as $report): ?>
                            <li>
                                <?php echo htmlspecialchars($report['report_type']); ?> from <?php echo htmlspecialchars($report['hospital_name']); ?><br>
                                <small><?php echo date('F j, Y', strtotime($report['upload_date'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No reports found.</p>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>patient/view_reports.php" class="btn" style="margin-top: 1rem;">View All Reports</a>
            </div>

            <!-- Recent Prescriptions Card -->
            <div class="card">
                <h3>Recent Prescriptions</h3>
                 <?php if (count($recent_prescriptions) > 0): ?>
                    <ul>
                        <?php foreach ($recent_prescriptions as $pres): ?>
                            <li>
                                <strong>By Dr. <?php echo htmlspecialchars($pres['doctor_name']); ?> at <?php echo htmlspecialchars($pres['hospital_name']); ?></strong>
                                <small><?php echo date('F j, Y', strtotime($pres['issue_date'])); ?></small>
                                <p style="background-color: #f9f9f9; border-left: 3px solid #007bff; padding: 10px; margin-top: 8px;">
                                    <?php echo nl2br(htmlspecialchars($pres['prescription_details'])); ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No prescriptions found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Chatbot Widget -->
<div id="chatbot" class="chatbot-container hidden">
    <div class="chat-header">
        Health Assistant
        <span class="close-btn" onclick="toggleChat()">&times;</span>
    </div>
    <div class="chat-messages" id="chat-messages">
    <!-- Bot's initial message will be added by JavaScript -->
    </div>

    <!-- NEW: Static Disclaimer -->
    <div class="chat-disclaimer">
        ⚠️ I am an AI assistant and not a medical professional.
    </div>

    <div class="chat-input-area">
        <input type="text" id="chat-input" placeholder="Type a message..." autocomplete="off">
        <button onclick="sendMessage()">&rarr;</button>
    </div>
</div>

<!-- Chat Button to open the widget -->
<div class="chat-button" onclick="toggleChat()">💬</div>

<script>
    const chatContainer = document.getElementById('chatbot');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');

    // Function to open/close the chat window
        function toggleChat() {
        chatContainer.classList.toggle('hidden');
        if (!chatContainer.classList.contains('hidden')) {
            // Check if the greeting has already been shown in this session
            const greetingShown = sessionStorage.getItem('greetingShown');

            if (greetingShown !== 'true') {
                // If not, show the greeting
                addMessage("bot", "Hello! I'm your AI health assistant. How can I help you today?");
                // And remember that we've shown it
                sessionStorage.setItem('greetingShown', 'true');
            }
        }
    }

    function sendMessage() {
        const userText = chatInput.value.trim();
        if (userText === "") return;

        addMessage("user", userText);
        chatInput.value = '';
        addMessage("bot", "..."); // Show "typing..." indicator

        fetch('../chatbot_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(userText)
        })
        .then(response => response.json())
        .then(data => {
            chatMessages.lastElementChild.remove(); // Remove "typing..."
            addMessage("bot", data.response);
        })
        .catch(error => {
            console.error('Error:', error);
            chatMessages.lastElementChild.remove(); // Remove "typing..."
            addMessage("bot", "Oops! Something went wrong. Please try again.");
        });
    }

    function addMessage(sender, text) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
        messageElement.innerHTML = text; // Use innerHTML for the disclaimer's bold tags
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Allow sending messages with the Enter key
    chatInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>