<?php
// Debug error (Uncomment for debugging)
// ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Initialize the session
session_start();

// Check if the user is logged in and has the physiotherapist role, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'physiotherapist') {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];

    // Retrieve physiotherapist id based on username
    $sql_therapistid = "SELECT staff_id FROM staffs WHERE username = ?";

    $stmt_therapist = $conn->prepare($sql_therapistid);
    if (!$stmt_therapist) {
        die("Failed to prepare statement: " . $conn->error);
    }
    $stmt_therapist->bind_param("s", $username);
    $stmt_therapist->execute();
    $result = $stmt_therapist->get_result();
    $row = $result->fetch_assoc();
    $staff_id = $row['staff_id'] ?? null;
    $stmt_therapist->close();

    if ($staff_id) {
        // Prepare to update the database
        $conn->begin_transaction();

        try {
            // Remove all existing slots for the physiotherapist, except where status is "booked"
            $stmt_delete = $conn->prepare("DELETE FROM schedules WHERE staff_id = ? AND status <> 'booked'");
            if (!$stmt_delete) {
                throw new Exception("Failed to prepare delete statement: " . $conn->error);
            }
            $stmt_delete->bind_param("i", $staff_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Iterate through submitted data and update the schedule
            if (isset($_POST['schedule']) && is_array($_POST['schedule'])) {
                foreach ($_POST['schedule'] as $day => $times) {
                    foreach ($times as $time => $status) {
                        if ($status === 'available') {
                            // Insert available time slots
                            $stmt_insert = $conn->prepare("INSERT INTO schedules (staff_id, day_of_week, time_slot, status) VALUES (?, ?, ?, ?)");
                            if (!$stmt_insert) {
                                throw new Exception("Failed to prepare insert statement: " . $conn->error);
                            }
                            $status = 'available';
                            $stmt_insert->bind_param("isss", $staff_id, $day, $time, $status);
                            $stmt_insert->execute();
                            $stmt_insert->close();
                        }
                    }
                }
            }

            // Commit the transaction
            $conn->commit();

            // Redirect to a success page or back to the schedule page
            header("location: schedules.php?success=1");
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            die("Failed to save schedule: " . $e->getMessage());
        }
    }
}

// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>
