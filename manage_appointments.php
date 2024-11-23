<?php
include 'includes/header_admin.php';

// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Ensure lock_status is set for current user session
$lock_status = $_SESSION['lock_status'] ?? 'No';

if ($lock_status === 'Yes') {
    echo "<script>alert('You are restricted from approving or remove appointment due to your lock status.');</script>";
}

// Handle the approval of an appointment with physiotherapist assignment and remarks
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
    $therapist_id = $_POST['therapist_id'];
    $staff_comments = $_POST['staff_comments'];

    // Assuming the username is stored in the session upon login
    $username = $_SESSION['username'];

    // Query to get the staff_id based on the username
    $sql_get_staff_id = "SELECT staff_id FROM staffs WHERE username = ?";
    if ($stmt_get_staff_id = $conn->prepare($sql_get_staff_id)) {
        $stmt_get_staff_id->bind_param("s", $username);
        $stmt_get_staff_id->execute();
        $stmt_get_staff_id->bind_result($staff_id);
        $stmt_get_staff_id->fetch();
        $stmt_get_staff_id->close();

        if ($staff_id) {
            // Prepare SQL to update the appointment
            $sql_update = "UPDATE appointments 
            SET status = 'Scheduled', therapist_id = ?, staff_comments = ?, staff_id = ?, managed_at = NOW() 
            WHERE appointment_id = ? AND status IN ('Pending', 'Scheduled')";

            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("isii", $therapist_id, $staff_comments, $staff_id, $appointment_id);
                if ($stmt_update->execute()) {
                    // Insert into schedules table
                    $sql_appointment = "SELECT appointment_datetime, patient_id FROM appointments WHERE appointment_id = ?";
                    if ($stmt_appointment = $conn->prepare($sql_appointment)) {
                        $stmt_appointment->bind_param("i", $appointment_id);
                        $stmt_appointment->execute();
                        $stmt_appointment->bind_result($appointment_datetime, $patient_id);
                        $stmt_appointment->fetch();
                        $stmt_appointment->close();

                        if ($appointment_datetime) {
                            // Convert appointment_datetime to DateTime object to calculate end_time
                            $start_time = new DateTime($appointment_datetime);
                            $end_time = clone $start_time;
                            $end_time->modify('+1 hour'); // Add 1 hour to the start time for the end time

                            // Convert DateTime objects to string format for SQL
                            $start_time_str = $start_time->format('Y-m-d H:i:s');
                            $end_time_str = $end_time->format('Y-m-d H:i:s');

                            $sql_insert_schedule = "INSERT INTO schedules (staff_id, start_time, end_time, type) 
                            VALUES (?, ?, ?, 'booked')";
                            if ($stmt_schedule = $conn->prepare($sql_insert_schedule)) {
                                $stmt_schedule->bind_param("iss", $therapist_id, $start_time_str, $end_time_str);

                                if ($stmt_schedule->execute()) {
                                    echo "Appointment approved and schedule updated successfully.";

                                    // Update notification status to 'Scheduled' and set read_status to 'unread'
                                    $sql_update_notification = "UPDATE notifications 
                                    SET status = 'Scheduled', read_status = 'unread', message='Your Booking has been Schedulled!' 
                                    WHERE patient_id = ? AND appointdatetime = ? AND status = 'Pending'";

                                    if ($stmt_notification = $conn->prepare($sql_update_notification)) {
                                        // Debugging: Output values
                                        echo "Patient ID: " . htmlspecialchars($patient_id) . "<br>";
                                        echo "Appointment Datetime: " . htmlspecialchars($appointment_datetime) . "<br>";

                                        // Bind the patient_id and appointment_datetime
                                        $stmt_notification->bind_param("is", $patient_id, $appointment_datetime);

                                        if ($stmt_notification->execute()) {
                                            echo "Notification status updated to 'scheduled'.";
                                        } else {
                                            echo "Error updating notification status: " . $stmt_notification->error;
                                        }
                                        $stmt_notification->close();
                                    } else {
                                        echo "Error preparing notification update statement: " . $conn->error;
                                    }

                                    // Prepare SQL to insert a new notificationstaff record with status 'Approved'
                                    $sql_insert_notificationstaff = "INSERT INTO notificationstaff (staff_id, appointdatetime, status, read_status, message) 
                                    VALUES (?, ?, 'Approved', 'unread', 'You have a new session with the client!')";

                                    if ($stmt_notificationstaff = $conn->prepare($sql_insert_notificationstaff)) {
                                        // Bind the staff_id and appointment_datetime for the new record
                                        $stmt_notificationstaff->bind_param("is", $therapist_id, $appointment_datetime);
                                        
                                        if ($stmt_notificationstaff->execute()) {
                                            echo "Notification status updated to 'Approved'.";
                                        } else {
                                            echo "Error updating notification status: " . $stmt_notificationstaff->error;
                                        }
                                        $stmt_notificationstaff->close();
                                    } else {
                                        echo "Error preparing notification staff update statement: " . $conn->error;
                                    }
                                } else {
                                    echo "Error inserting schedule: " . $stmt_schedule->error;
                                }
                                $stmt_schedule->close();
                            } else {
                                echo "Error preparing schedule statement: " . $conn->error;
                            }
                        }
                    }
                } else {
                    echo "Error approving appointment: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
            }
        } else {
            echo "Error: No valid staff ID found for the username.";
        }
    } else {
        echo "Error preparing statement to retrieve staff ID: " . $conn->error;
    }
}

// Handle cancellation of appointment with remarks
if (isset($_POST['cancel_appointment_id'])) {
    // Sanitize and validate the appointment_id
    $appointment_id = filter_var($_POST['cancel_appointment_id'], FILTER_VALIDATE_INT);
    $cancel_remarks = filter_var($_POST['cancel_remarks'], FILTER_SANITIZE_STRING);

    if ($appointment_id === false) {
        echo "Invalid appointment ID.<br>";
        exit;
    }

    $sql_appointment = "SELECT appointment_datetime, patient_id, staff_id, therapist_id FROM appointments WHERE appointment_id = ?";
    if ($stmt_appointment = $conn->prepare($sql_appointment)) {
        $stmt_appointment->bind_param("i", $appointment_id);
        $stmt_appointment->execute();
        $stmt_appointment->bind_result($appointment_datetime, $patient_id, $staff_id, $therapist_id);
        $stmt_appointment->fetch();
        $stmt_appointment->close();

        if ($appointment_datetime) {
            $day_of_week = date('l', strtotime($appointment_datetime));
            $time_slot = date('H:i:s', strtotime($appointment_datetime));

            // Prepare SQL to update appointment status to 'Cancelled' and add remarks
            $sql_cancel = "UPDATE appointments SET status = 'Cancelled', staff_comments = ? WHERE appointment_id = ?";
            if ($stmt_cancel = $conn->prepare($sql_cancel)) {
                $stmt_cancel->bind_param("si", $cancel_remarks, $appointment_id);
                if ($stmt_cancel->execute()) {
                    echo "Appointment cancelled successfully with remarks.<br>";

                    // Update the status in notificationstaff to 'Cancelled'
                    $sql_update_notificationstaff = "UPDATE notificationstaff 
                                                     SET status = 'Cancelled', read_status = 'unread', message='Your session has been cancelled!'  
                                                     WHERE staff_id = ? AND appointdatetime = ? AND status = 'Pending'";

                    if ($stmt_notificationstaff = $conn->prepare($sql_update_notificationstaff)) {
                        // Bind the therapist_id and appointdatetime
                        $stmt_notificationstaff->bind_param("is", $therapist_id, $appointment_datetime);

                        if ($stmt_notificationstaff->execute()) {
                            echo "Notification staff status updated to 'Cancelled'.<br>";
                        } else {
                            echo "Error updating notification staff status: " . $stmt_notificationstaff->error . "<br>";
                        }
                        $stmt_notificationstaff->close();
                    } else {
                        echo "Error preparing notification staff update statement: " . $conn->error . "<br>";
                    }

                    // Update notification status to 'Cancelled' in the notifications table
                    $sql_update_notification = "UPDATE notifications 
                                                SET status = 'Cancelled', read_status = 'unread', message='Your booking has been cancelled!'  
                                                WHERE patient_id = ? AND appointdatetime = ? AND status = 'Pending'";

                    if ($stmt_notification = $conn->prepare($sql_update_notification)) {
                        // Bind the patient_id and appointment_datetime
                        $stmt_notification->bind_param("is", $patient_id, $appointment_datetime);

                        if ($stmt_notification->execute()) {
                            echo "Notification status updated to 'Cancelled'.<br>";
                        } else {
                            echo "Error updating notification status: " . $stmt_notification->error . "<br>";
                        }
                        $stmt_notification->close();
                    } else {
                        echo "Error preparing notification update statement: " . $conn->error . "<br>";
                    }
                } else {
                    echo "Error canceling appointment: " . $stmt_cancel->error . "<br>";
                }
                $stmt_cancel->close();
            } else {
                echo "Error preparing statement: " . $conn->error . "<br>";
            }
        }
    }
}

// Handle removal of appointment
if (isset($_GET['remove_appointment_id'])) {
    // Sanitize and validate the appointment_id
    $appointment_id = filter_var($_GET['remove_appointment_id'], FILTER_VALIDATE_INT);

    if ($appointment_id === false) {
        echo "Invalid appointment ID.<br>";
        exit;
    }

    $sql_appointment = "SELECT appointment_datetime, patient_id, staff_id, therapist_id FROM appointments WHERE appointment_id = ?";
    if ($stmt_appointment = $conn->prepare($sql_appointment)) {
        $stmt_appointment->bind_param("i", $appointment_id);
        $stmt_appointment->execute();
        $stmt_appointment->bind_result($appointment_datetime, $patient_id, $staff_id, $therapist_id);
        $stmt_appointment->fetch();
        $stmt_appointment->close();

        if ($appointment_datetime) {
            // Prepare SQL to delete the appointment from the database
            $sql_remove = "DELETE FROM appointments WHERE appointment_id = ?";
            if ($stmt_remove = $conn->prepare($sql_remove)) {
                $stmt_remove->bind_param("i", $appointment_id);
                if ($stmt_remove->execute()) {
                    echo "Appointment removed successfully.<br>";

                    // Update the status in notificationstaff to 'Removed'
                    $sql_update_notificationstaff = "UPDATE notificationstaff 
                                                     SET status = 'Removed', read_status = 'unread' , message='Your session has been Removed!' 
                                                     WHERE staff_id = ? AND appointdatetime = ? AND status = 'Cancelled'";

                    if ($stmt_notificationstaff = $conn->prepare($sql_update_notificationstaff)) {
                        // Bind the therapist_id and appointment_datetime
                        $stmt_notificationstaff->bind_param("is", $therapist_id, $appointment_datetime);

                        if ($stmt_notificationstaff->execute()) {
                            echo "Notification staff status updated to 'Removed'.<br>";
                        } else {
                            echo "Error updating notification staff status: " . $stmt_notificationstaff->error . "<br>";
                        }
                        $stmt_notificationstaff->close();
                    } else {
                        echo "Error preparing notification staff update statement: " . $conn->error . "<br>";
                    }

                    // Delete related notifications from notifications
                    $sql_delete_notification = "DELETE FROM notifications 
                    WHERE patient_id = ? AND appointdatetime = ? AND status = 'Cancelled'";

                    if ($stmt_notification = $conn->prepare($sql_delete_notification)) {
                        // Bind the patient_id and appointment_datetime
                        $stmt_notification->bind_param("is", $patient_id, $appointment_datetime);

                        if ($stmt_notification->execute()) {
                            echo "Notification entries deleted.<br>";
                        } else {
                            echo "Error deleting notification entries: " . $stmt_notification->error . "<br>";
                        }
                        $stmt_notification->close();
                    } else {
                        echo "Error preparing notification delete statement: " . $conn->error . "<br>";
                    }
                } else {
                    echo "Error removing appointment: " . $stmt_remove->error . "<br>";
                }
                $stmt_remove->close();
            } else {
                echo "Error preparing statement: " . $conn->error . "<br>";
            }
        } else {
            echo "Appointment not found.<br>";
        }
    } else {
        echo "Error preparing appointment query: " . $conn->error . "<br>";
    }
}

// Handle removal of appointment
if (isset($_GET['confirm_appointment_id'])) {

    $appointment_id = filter_var($_GET['confirm_appointment_id'], FILTER_VALIDATE_INT);

    if ($appointment_id === false) {
        echo "Invalid appointment ID.<br>";
        exit;
    }

    $sql_appointment = "SELECT appointment_datetime, patient_id, staff_id, therapist_id FROM appointments WHERE appointment_id = ?";
    if ($stmt_appointment = $conn->prepare($sql_appointment)) {
        $stmt_appointment->bind_param("i", $appointment_id);
        $stmt_appointment->execute();
        $stmt_appointment->bind_result($appointment_datetime, $patient_id, $staff_id, $therapist_id);
        $stmt_appointment->fetch();
        $stmt_appointment->close();

        if ($appointment_datetime) {
            $day_of_week = date('l', strtotime($appointment_datetime));
            $time_slot = date('H:i:s', strtotime($appointment_datetime));

            // Prepare SQL to update appointment status to 'Completed'
            $sql_cancel = "UPDATE appointments SET status = 'Completed' WHERE appointment_id = ?";
            if ($stmt_cancel = $conn->prepare($sql_cancel)) {
                $stmt_cancel->bind_param("i", $appointment_id);
                if ($stmt_cancel->execute()) {
                    echo "Appointment Completed!.<br>";

                    // Update the session_count in the patients table
                    $sql_update_session_count = "UPDATE patients SET session_count = session_count + 1 WHERE patient_id = ?";
                    if ($stmt_session_count = $conn->prepare($sql_update_session_count)) {
                        $stmt_session_count->bind_param("i", $patient_id);
                        if ($stmt_session_count->execute()) {
                            echo "Patient session count incremented.<br>";
                        } else {
                            echo "Error updating session count: " . $stmt_session_count->error . "<br>";
                        }
                        $stmt_session_count->close();
                    } else {
                        echo "Error preparing session count update statement: " . $conn->error . "<br>";
                    }

                    // Update the status in notificationstaff to 'Completed'
                    $sql_update_notificationstaff = "UPDATE notificationstaff 
                                                     SET status = 'Completed', read_status = 'unread', message='Your session has with the Client is Completed!'   
                                                     WHERE staff_id = ? AND appointdatetime = ? AND status = 'Pending'";

                    if ($stmt_notificationstaff = $conn->prepare($sql_update_notificationstaff)) {

                        // Bind the therapist_id and appointdatetime
                        $stmt_notificationstaff->bind_param("is", $therapist_id, $appointment_datetime);

                        if ($stmt_notificationstaff->execute()) {
                            echo "Notification staff status updated to 'Completed'.<br>";
                        } else {
                            echo "Error updating notification staff status: " . $stmt_notificationstaff->error . "<br>";
                        }
                        $stmt_notificationstaff->close();
                    } else {
                        echo "Error preparing notification staff update statement: " . $conn->error . "<br>";
                    }

                    // Update notification status to 'Completed' in the notifications table
                    $sql_update_notification = "UPDATE notifications 
                                                SET status = 'Completed', read_status = 'unread', message='Your Session is Completed!'   
                                                WHERE patient_id = ? AND appointdatetime = ? AND status = 'Scheduled'";

                    if ($stmt_notification = $conn->prepare($sql_update_notification)) {
                        // Bind the patient_id and appointment_datetime
                        $stmt_notification->bind_param("is", $patient_id, $appointment_datetime);

                        if ($stmt_notification->execute()) {
                            echo "Notification status updated to 'Completed'.<br>";
                        } else {
                            echo "Error updating notification status: " . $stmt_notification->error . "<br>";
                        }
                        $stmt_notification->close();
                    } else {
                        echo "Error preparing notification update statement: " . $conn->error . "<br>";
                    }
                } else {
                    echo "Error completing appointment: " . $stmt_cancel->error . "<br>";
                }
                $stmt_cancel->close();
            } else {
                echo "Error preparing statement: " . $conn->error . "<br>";
            }
        }
    }
}

// Fetch all appointments for display to staff
$sql_appointments = "SELECT a.appointment_id, a.appointment_datetime, a.managed_at, a.patient_comments, a.staff_comments, a.status, 
                     s.first_name AS staff_first_name, s.last_name AS staff_last_name, 
                     t.first_name AS therapist_first_name, t.last_name AS therapist_last_name,
                     p.first_name AS patient_first_name, p.last_name AS patient_last_name
                     FROM appointments a
                     LEFT JOIN staffs s ON a.staff_id = s.staff_id
                     LEFT JOIN staffs t ON a.therapist_id = t.staff_id
                     LEFT JOIN patients p ON a.patient_id = p.patient_id
                     ORDER BY a.appointment_id DESC";

$result_appointments = $conn->query($sql_appointments);

// Fetch all staff for the dropdown list
$sql_therapists = "SELECT staff_id, first_name, last_name FROM staffs WHERE role = 'physiotherapist'";
$result_therapists = $conn->query($sql_therapists);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Appointments</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Status Filter Dropdown Styling */
        .filter-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-container label {
            margin-right: 10px;
            font-weight: bold;
            color: #34495e;
        }

        .filter-container select {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        .filter-container select:focus {
            border-color: #3498db;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #008CBA;
            color: #fff;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .book-appointment-button {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 1em;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .book-appointment-button:hover {
            background-color: #005f6b;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin: auto;
            width: 50%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(100%);
            position: relative;
        }

        #cancelModal {
            display: none;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: red;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <main>
        <div class="booking-header">
            <h1>Manage All Appointments</h1>
            <strong>Here, you can manage all appointments</strong>
        </div>

        <!-- Status Filter Dropdown -->
        <div class="filter-container">
            <form method="get" action="manage_appointments.php">
                <label for="status_filter">Filter by Status:</label>
                <select name="status_filter" id="status_filter" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="Pending" <?php if (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Pending')
                                                echo 'selected'; ?>>Pending</option>
                    <option value="Scheduled" <?php if (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Scheduled')
                                                    echo 'selected'; ?>>Scheduled</option>
                    <option value="Cancelled" <?php if (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Cancelled')
                                                    echo 'selected'; ?>>Cancelled</option>
                    <option value="Completed" <?php if (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Completed')
                                                    echo 'selected'; ?>>Completed</option>
                </select>
            </form>
        </div>

        <?php
        // Handle the filtering
        $status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

        // Modify the SQL query to filter by status if a filter is set
        $sql_appointments = "SELECT a.appointment_id, a.appointment_datetime, a.managed_at, a.patient_comments, a.staff_comments, a.status, 
                            s.first_name AS staff_first_name, s.last_name AS staff_last_name, 
                            t.first_name AS therapist_first_name, t.last_name AS therapist_last_name,
                            p.first_name AS patient_first_name, p.last_name AS patient_last_name
                            FROM appointments a
                            LEFT JOIN staffs s ON a.staff_id = s.staff_id
                            LEFT JOIN staffs t ON a.therapist_id = t.staff_id
                            LEFT JOIN patients p ON a.patient_id = p.patient_id";

        // Add condition for filtering by status
        if (!empty($status_filter)) {
            $sql_appointments .= " WHERE a.status = ?";
        }

        $sql_appointments .= " ORDER BY a.appointment_id DESC";

        // Prepare and bind the statement for filtered results
        if ($stmt_appointments = $conn->prepare($sql_appointments)) {
            if (!empty($status_filter)) {
                $stmt_appointments->bind_param("s", $status_filter);
            }
            $stmt_appointments->execute();
            $result_appointments = $stmt_appointments->get_result();
        }
        // Start the table and headers
        echo "<table>";
        echo "<tr><th>Appointment Date & Time</th><th>Patient Name</th><th>Patient Comments</th><th>Assigned Therapist</th><th>Managed By</th><th>Staff Comments</th><th>Status</th><th>Managed At</th><th>Actions</th></tr>";

        // Loop through the results and display each appointment
        while ($row_appointments = $result_appointments->fetch_assoc()) {
            $status = htmlspecialchars($row_appointments['status']);
            $managed = htmlspecialchars($row_appointments['managed_at']);
            echo "<tr data-status='$status'>";
            echo "<td>" . (!empty($row_appointments['appointment_datetime']) ? date("d-m-Y H:i", strtotime($row_appointments['appointment_datetime'])) : "-") . "</td>";
            echo "<td>" . htmlspecialchars($row_appointments['patient_first_name']) . " " . htmlspecialchars($row_appointments['patient_last_name']) . "</td>";
            echo "<td>" . (!empty($row_appointments['patient_comments']) ? htmlspecialchars($row_appointments['patient_comments']) : "-") . "</td>";
            echo "<td>" . (!empty($row_appointments['therapist_first_name']) && !empty($row_appointments['therapist_last_name']) ? htmlspecialchars($row_appointments['therapist_first_name']) . " " . htmlspecialchars($row_appointments['therapist_last_name']) : "-") . "</td>";
            echo "<td>" . (!empty($row_appointments['staff_first_name']) && !empty($row_appointments['staff_last_name']) ? htmlspecialchars($row_appointments['staff_first_name']) . " " . htmlspecialchars($row_appointments['staff_last_name']) : "-") . "</td>";
            echo "<td>" . (!empty($row_appointments['staff_comments']) ? htmlspecialchars($row_appointments['staff_comments']) : "-") . "</td>";
            echo "<td>" . $status . "</td>";
            if ($managed === '0000-00-00 00:00:00') {
                echo "<td>-</td>";
            } else {
                echo "<td>" . date("d-m-Y H:i", strtotime($managed)) . "</td>";
            }

            // Conditionally display buttons based on status and lock status
            if ($status === 'Pending') {
                echo "<td>";
                echo "<button type='button' class='book-appointment-button' onclick='approveAppointment(" . $row_appointments['appointment_id'] . ")' " . ($_SESSION['lock_status'] === 'Yes' ? 'disabled title="Your account is locked."' : '') . ">" . ($_SESSION['lock_status'] === 'Yes' ? 'Locked' : 'Approve') . "</button>";
                echo "<button type='button' class='book-appointment-button' onclick='cancelAppointment(" . $row_appointments['appointment_id'] . ")' " . ($_SESSION['lock_status'] === 'Yes' ? 'disabled title="Your account is locked."' : '') . ">" . ($_SESSION['lock_status'] === 'Yes' ? 'Locked' : 'Cancel') . "</button>";
                echo "</td>";
            } elseif ($status === 'Cancelled') {
                echo "<td>None</td>"; // No buttons to display for Cancelled status
            } elseif ($status === 'Scheduled') {
                echo "<td>";
                echo "<button type='button' class='book-appointment-button' onclick='confirmAppointment(" . $row_appointments['appointment_id'] . ")' " . ($_SESSION['lock_status'] === 'Yes' ? 'disabled title="Your account is locked."' : '') . ">" . ($_SESSION['lock_status'] === 'Yes' ? 'Locked' : 'Completed') . "</button>";
                echo "<br>";
                echo "<button type='button' class='book-appointment-button' onclick='cancelAppointment(" . $row_appointments['appointment_id'] . ")' " . ($_SESSION['lock_status'] === 'Yes' ? 'disabled title="Your account is locked."' : '') . ">" . ($_SESSION['lock_status'] === 'Yes' ? 'Locked' : 'Cancel') . "</button>";
                echo "</td>";
            } else {
                echo "<td>
                </td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        $conn->close();
        ?>

        <!-- Modal for approving appointment -->
        <div id="approveModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <form method="post" action="manage_appointments.php">
                    <input type="hidden" name="appointment_id" id="appointment_id">
                    <label for="therapist_id">Assign Physiotherapist:</label>
                    <select name="therapist_id" id="therapist_id" required>
                        <option value="">Select a Physiotherapist</option>
                        <?php while ($row_therapists = $result_therapists->fetch_assoc()) { ?>
                            <option value="<?php echo $row_therapists['staff_id']; ?>">
                                <?php echo htmlspecialchars($row_therapists['first_name']) . " " . htmlspecialchars($row_therapists['last_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <br><br>
                    <label for="staff_comments">Staff Remarks:</label><br>
                    <textarea name="staff_comments" id="staff_comments" rows="4" cols="50"
                        placeholder="Enter remarks here"></textarea>
                    <br><br>
                    <button type="submit" class="book-appointment-button">Approve Appointment</button>
                </form>
            </div>
        </div>

        <!-- Modal for canceling appointment -->
        <div id="cancelModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeCancelModal()">&times;</span>
                <form method="post" action="manage_appointments.php">
                    <input type="hidden" name="cancel_appointment_id" id="cancel_appointment_id">
                    <label for="cancel_remarks">Cancel Remarks:</label><br>
                    <textarea name="cancel_remarks" id="cancel_remarks" rows="4" cols="50"
                        placeholder="Enter cancellation remarks here"></textarea>
                    <br><br>
                    <button type="submit" class="book-appointment-button">Confirm Cancel</button>
                </form>
            </div>
        </div>


    </main>
    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Ensure modal is hidden on page load
            document.getElementById('approveModal').style.display = 'none';
        });

        function approveAppointment(appointmentId) {
            // Set the appointment ID in the hidden field of the form
            document.getElementById('appointment_id').value = appointmentId;
            // Display the modal
            document.getElementById('approveModal').style.display = 'block';
        }

        function closeModal() {
            // Hide the modal
            document.getElementById('approveModal').style.display = 'none';
        }

        function cancelAppointment(appointmentId) {
            // Set the appointment ID in the hidden field of the cancel form
            document.getElementById('cancel_appointment_id').value = appointmentId;
            // Display the modal
            document.getElementById('cancelModal').style.display = 'block';
        }

        function closeCancelModal() {
            // Hide the modal
            document.getElementById('cancelModal').style.display = 'none';
        }


        function removeAppointment(appointmentId) {
            if (confirm('Are you sure you want to remove this appointment?')) {
                window.location.href = 'manage_appointments.php?remove_appointment_id=' + appointmentId;
            }
        }

        function confirmAppointment(appointmentId) {
            if (confirm('Are you sure the session is completed?')) {
                window.location.href = 'manage_appointments.php?confirm_appointment_id=' + appointmentId;
            }
        }

        // Close the modal if it's open and the user clicks outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('approveModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>

</html>