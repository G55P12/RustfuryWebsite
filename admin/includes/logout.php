<?php
session_start();

// Unset the 'loggedin' session variable and destroy the session
unset($_SESSION['authorized']);
session_destroy();

// Redirect to the login page
header('Location: login.php');
exit;
?>
