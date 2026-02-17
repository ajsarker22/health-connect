<?php
session_start();

// Unset all of the session variables
 $_SESSION = array();

// Destroy the session
session_destroy();

// We will now use JavaScript to clear browser storage and redirect.
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging Out...</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
    </style>
</head>
<body>

    <p>Logging out, please wait...</p>

    <script>
        // This is the key part: Clear the browser's session storage
        sessionStorage.clear();

        // Also clear local storage just in case you use it in the future
        localStorage.clear();

        // Now, redirect to the login page using JavaScript
        window.location.href = 'login.php';
    </script>

</body>
</html>