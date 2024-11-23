<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update the last activity timestamp
$_SESSION['last_activity'] = time();
?>
