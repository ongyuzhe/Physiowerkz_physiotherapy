<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
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

include 'includes/header_staff.php';

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

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
                            $day_of_week = date('l', strtotime($appointment_datetime));
                            $time_slot = date('H:i:s', strtotime($appointment_datetime));

                            // Insert into schedules
                            $sql_insert_schedule = "INSERT INTO schedules (staff_id, day_of_week, time_slot, status, managed_at) 
                                VALUES (?, ?, ?, 'booked', NOW())";
                            if ($stmt_schedule = $conn->prepare($sql_insert_schedule)) {
                                $stmt_schedule->bind_param("iss", $therapist_id, $day_of_week, $time_slot);
                                if ($stmt_schedule->execute()) {
                                    echo "Appointment approved and schedule updated successfully.";

                                    // Update notification status to 'scheduled'
                                    $sql_update_notification = "UPDATE notifications 
                                        SET status = 'Scheduled' 
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

                                    // Update notificationstaff status to 'Approved'
                                    $sql_update_notificationstaff = "UPDATE notificationstaff 
                                        SET status = 'Approved' 
                                        WHERE staff_id = ? AND appointdatetime = ? AND status = 'Pending'";

                                    if ($stmt_notificationstaff = $conn->prepare($sql_update_notificationstaff)) {
                                        // Bind the staff_id and appointment_datetime
                                        $stmt_notificationstaff->bind_param("is", $staff_id, $appointment_datetime);

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

// Handle cancellation of appointment
if (isset($_GET['cancel_appointment_id'])) {
    // Sanitize and validate the appointment_id
    $appointment_id = filter_var($_GET['cancel_appointment_id'], FILTER_VALIDATE_INT);

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

            // Prepare SQL to update appointment status to 'Cancelled'
            $sql_cancel = "UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?";
            if ($stmt_cancel = $conn->prepare($sql_cancel)) {
                $stmt_cancel->bind_param("i", $appointment_id);
                if ($stmt_cancel->execute()) {
                    echo "Appointment cancelled successfully.<br>";

                    // Update the status in notificationstaff to 'Cancelled'
                    $sql_update_notificationstaff = "UPDATE notificationstaff 
                                                     SET status = 'Cancelled' 
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
                                                SET status = 'Cancelled' 
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
                                                     SET status = 'Removed' 
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
// Retrieve the logged-in user's staff ID from the session
$username = $_SESSION["username"];

// Retrieve the staff ID based on the username
$sql_get_staff_id = "SELECT staff_id FROM staffs WHERE username = ?";
if ($stmt_get_staff_id = $conn->prepare($sql_get_staff_id)) {
    $stmt_get_staff_id->bind_param("s", $username);
    $stmt_get_staff_id->execute();
    $stmt_get_staff_id->bind_result($staff_id);
    $stmt_get_staff_id->fetch();
    $stmt_get_staff_id->close();

    // Ensure staff_id is valid
    if (!isset($staff_id) || !is_numeric($staff_id)) {
        echo "Invalid staff ID.";
        exit;
    }

    // Retrieve the search terms (either name or date)
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    $search_date = isset($_GET['search_date']) ? trim($_GET['search_date']) : '';
    $filter_status = isset($_GET['filter']) ? $_GET['filter'] : '';

    // Convert date from 'dd-mm-yyyy' to 'yyyy-mm-dd' if provided
    if (!empty($search_date)) {
        $date_parts = explode('-', $search_date);
        if (count($date_parts) == 3) {
            // Rearrange date to 'yyyy-mm-dd' format
            $search_date = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        } else {
            $search_date = ''; // Invalid date, reset search
        }
    }

    // Construct the base SQL query
    $sql_appointments = "SELECT a.appointment_id, a.appointment_datetime, a.managed_at, a.patient_comments, 
a.staff_comments, a.status, 
s.first_name AS staff_first_name, s.last_name AS staff_last_name, 
t.first_name AS therapist_first_name, t.last_name AS therapist_last_name,
p.first_name AS patient_first_name, p.last_name AS patient_last_name
FROM appointments a
LEFT JOIN staffs s ON a.staff_id = s.staff_id
LEFT JOIN staffs t ON a.therapist_id = t.staff_id
LEFT JOIN patients p ON a.patient_id = p.patient_id
WHERE a.therapist_id = ?";

    $parameters = [$staff_id];
    $types = "i"; // Initial type for therapist ID

    // Add conditions based on available search terms and filter criteria
    if (!empty($filter_status)) {
        $sql_appointments .= " AND a.status = ?";
        $parameters[] = $filter_status;
        $types .= "s"; // Adding type for the status parameter
    }

    if (!empty($search_term)) {
        $sql_appointments .= " AND (p.first_name LIKE ? OR p.last_name LIKE ?)";
        $search_like_term = "%$search_term%";
        $parameters[] = $search_like_term;
        $parameters[] = $search_like_term;
        $types .= "ss"; // Adding types for the name search parameters
    }

    if (!empty($search_date)) {
        $sql_appointments .= " AND (DATE(a.appointment_datetime) = ? OR DATE(a.managed_at) = ?)";
        $parameters[] = $search_date;
        $parameters[] = $search_date;
        $types .= "ss"; // Adding types for the date search parameters
    }

    $sql_appointments .= " ORDER BY a.appointment_id DESC";

    // Prepare and execute the statement
    if ($stmt = $conn->prepare($sql_appointments)) {
        // Use dynamic parameters for binding
        $stmt->bind_param($types, ...$parameters);
        $stmt->execute();
        $result_appointments = $stmt->get_result();
    }
}

// Fetch all staff for the dropdown list
$sql_therapists = "SELECT staff_id, first_name, last_name FROM staffs WHERE role = 'physiotherapist'";
$result_therapists = $conn->query($sql_therapists);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Manage Bookings</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        strong {
            display: block;
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        /* Search Container Styling */
        .search-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-container form {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .search-container input[type="text"] {
            padding: 10px;
            font-size: 1em;
            width: 250px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        .search-container input[type="text"]:focus {
            border-color: #3498db;
        }

        .search-container input[type="submit"],
        .search-container button {
            padding: 10px 15px;
            font-size: 1em;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .search-container button[type="button"] {
            background-color: #2ecc71;
        }

        .search-container input[type="submit"]:hover,
        .search-container button:hover {
            background-color: #2980b9;
        }

        .search-container button[type="button"]:hover {
            background-color: #27ae60;
        }

        /* Reset Button Styling */
        .search-container form+form button {
            background-color: #95a5a6;
        }

        .search-container form+form button:hover {
            background-color: #7f8c8d;
        }

        .filter-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .filter-container button,
        .filter-container input[type="submit"] {
            padding: 10px 15px;
            font-size: 1em;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-container button:hover,
        .filter-container input[type="submit"]:hover {
            background-color: #2980b9;
        }

        /* Table Styling */
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

        /* Button Styling */
        .book-appointment-button {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 1em;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .book-appointment-button:hover {
            background-color: #005f6b;
            transform: scale(1.05);
        }

        /* Modal Styling */
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
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover,
        .close:focus {
            color: #e74c3c;
            text-decoration: none;
            cursor: pointer;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            .modal-content {
                width: 95%;
            }
        }
    </style>
</head>

<body>
    <main>
        <div>
            <h1>Your Appointments</h1>
            <strong>Here, you can view all your appointments</strong>
        </div>

        <!-- Search Container -->
        <div class="search-container">
            <form method="GET" action="view_appointments.php">
                <input type="text" name="search" placeholder="Search by patient name" value="<?= htmlspecialchars(str_replace('%', '', $search_term)) ?>">
                <input type="text" name="search_date" placeholder="Search by date (dd-mm-yyyy)" value="<?= htmlspecialchars($search_date) ?>">
                <input type="submit" value="Search">
            </form>
            <!-- Optional reset button to clear the search -->
            <form method="GET" action="view_appointments.php">
                <button type="submit">Reset</button>
            </form>
        </div>

        <div class="filter-container">
            <form method="GET" action="">
                <button type="submit" name="filter" value="pending">Pending</button>
                <button type="submit" name="filter" value="scheduled">Scheduled</button>
                <button type="submit" name="filter" value="completed">Completed</button>
                <button type="submit" name="filter" value="cancelled">Cancelled</button>
            </form>
        </div>

        <?php
        // Start the table and headers
        echo "<table>";
        echo "<tr><th>Appointment Date & Time</th><th>Patient Name</th><th>Patient Comments</th><th>Assigned Therapist</th><th>Managed By</th><th>Staff Comments</th><th>Status</th><th>Managed At</th></tr>";

        // Loop through the results and display each appointment
        while ($row_appointments = $result_appointments->fetch_assoc()) {
            $status = htmlspecialchars($row_appointments['status']);
            $managed = htmlspecialchars($row_appointments['managed_at'] ?? '');
            echo "<tr data-status='$status'>";
            echo "<td>" . (!empty($row_appointments['appointment_datetime']) ? date("d-m-Y H:i", strtotime($row_appointments['appointment_datetime'])) : "-") . "</td>";
            echo "<td>" . htmlspecialchars($row_appointments['patient_first_name'] ?? '') . " " . htmlspecialchars($row_appointments['patient_last_name'] ?? '') . "</td>";
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
            echo "</tr>";
        }
        echo "</table>";
        $conn->close();
        ?>
    </main>

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
            if (confirm('Are you sure you want to cancel this appointment?')) {
                window.location.href = 'manage_appointments.php?cancel_appointment_id=' + appointmentId;
            }
        }

        function removeAppointment(appointmentId) {
            if (confirm('Are you sure you want to remove this appointment?')) {
                window.location.href = 'manage_appointments.php?remove_appointment_id=' + appointmentId;
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
<?php include 'includes/footer.php'; ?>

</html>