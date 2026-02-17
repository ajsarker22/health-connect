<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

 $hospital_id = $_SESSION['user_id'];
 $post_error = '';
 $post_success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $post_error = "Both title and content are required.";
    } else {
        $sql = "INSERT INTO notices (hospital_id, title, content) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$hospital_id, $title, $content])) {
            $post_success = "Notice posted successfully!";
            // Clear form fields on success
            $_POST['title'] = '';
            $_POST['content'] = '';
        } else {
            $post_error = "Failed to post notice. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<main>
    <div class="container">
        <h2>Post a New Notice</h2>
        <div class="form-container">
            <?php if (!empty($post_error)): ?>
                <div class="alert alert-error"><?php echo $post_error; ?></div>
            <?php endif; ?>
            <?php if (!empty($post_success)): ?>
                <div class="alert alert-success"><?php echo $post_success; ?></div>
            <?php endif; ?>
            <form action="post_notice.php" method="post">
                <div class="form-group">
                    <label for="title">Notice Title</label>
                    <input type="text" name="title" id="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="content">Notice Content</label>
                    <textarea name="content" id="content" rows="8" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn">Post Notice</button>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>