<?php 
require_once 'includes/db_connect.php';
include 'includes/header.php'; ?>

<main>
    <div class="container" style="text-align: center; padding-top: 4rem;">
        <h1>Welcome to HealthConnect BD</h1>
        <p style="font-size: 1.2rem; margin: 1rem 0;">Your unified platform for managing health records, appointments, and care.</p>
        <div style="margin-top: 2rem;">
            <a href="login.php" class="btn" style="margin-right: 10px;">Login</a>
            <a href="register.php" class="btn">Register Now</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>