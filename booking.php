<?php
ob_start(); // Start output buffering
// Check if session is already started
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

include 'includes/header_patient.php';

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Check if the cancel appointment request is set and is a number
if (isset($_GET['cancel_appointment_id']) && is_numeric($_GET['cancel_appointment_id'])) {
    $appointment_id = $_GET['cancel_appointment_id'];

    // Prepare SQL to update appointment status to 'cancelled'
    $sql_update = "UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ? AND status IN ('pending', 'scheduled')";
    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("i", $appointment_id);
        if ($stmt_update->execute()) {
            // Appointment cancellation successful
            echo "Appointment cancelled successfully.";

            // Now update the notifications table
            $sql_notification_update = "UPDATE notifications SET status = 'Cancelled', message = 'Your booking has been cancelled', read_status = 'unread' WHERE id = ?";
            if ($stmt_notification_update = $conn->prepare($sql_notification_update)) {
                $stmt_notification_update->bind_param("i", $appointment_id);
                if ($stmt_notification_update->execute()) {
                    echo "Notification updated successfully.";
                } else {
                    echo "Error updating notification: " . $stmt_notification_update->error;
                }
                $stmt_notification_update->close(); // Close the statement
            } else {
                echo "Error preparing notification update statement: " . $conn->error;
            }

            // Close statement and connection
            $stmt_update->close();
            $conn->close();

            // Redirect to booking page
            header("Location: booking.php");
            exit;
        } else {
            echo "Error cancelling appointment: " . $stmt_update->error;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];

    // Retrieve patient id based on username
    $sql_patientid = "SELECT patient_id FROM patients WHERE username = ?";

    $stmt_patients = $conn->prepare($sql_patientid);
    $stmt_patients->bind_param("s", $username);
    $stmt_patients->execute();
    $result = $stmt_patients->get_result();
    $row = $result->fetch_assoc();
    $patient_id = $row['patient_id'] ?? null;
    $stmt_patients->close();
}
// End of file, flush output buffer
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Appointments</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
</head>

<style>
    /* Main Container Styling */
    main {
        max-width: 1000px;
        width: 90%;
        margin: 40px auto;
        padding: 20px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    /* Heading Styling */
    main h1 {
        font-size: 2em;
        margin-bottom: 10px;
        color: #003366;
        text-align: center;
    }

    main strong {
        display: block;
        font-size: 1.2em;
        margin-bottom: 20px;
        text-align: center;
    }

    /* Button Styling */
    .book-appointment-button,
    .filter-appointment-button {
        background-color: #008CBA;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-bottom: 15px;
        transition: background-color 0.3s ease;
    }

    .book-appointment-button:hover,
    .filter-appointment-button:hover {
        background-color: #005f6b;
    }

    /* Filter Button Container */
    .filter-buttons {
        text-align: center;
        margin-bottom: 20px;
    }

    .filter-buttons button {
        margin: 0 5px;
    }

    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table th,
    table td {
        border: 1px solid #ddd;
        padding: 12px 15px;
        text-align: left;
    }

    table th {
        background-color: #f4f4f9;
        color: #003366;
        text-transform: uppercase;
    }

    table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    table tr:hover {
        background-color: #f1f1f1;
    }

    /* Status Coloring */
    [data-status="pending"] td:nth-child(6) {
        color: #FFA500;
        /* Orange for Pending */
    }

    [data-status="scheduled"] td:nth-child(6) {
        color: #007bff;
        /* Blue for Scheduled */
    }

    [data-status="completed"] td:nth-child(6) {
        color: #28a745;
        /* Green for Completed */
    }

    [data-status="cancelled"] td:nth-child(6) {
        color: #dc3545;
        /* Red for Cancelled */
    }

    /* Responsive Design */
    @media (max-width: 768px) {

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
        }

        table tr {
            margin-bottom: 15px;
        }

        table th {
            display: none;
        }

        table td {
            padding-left: 50%;
            position: relative;
        }

        table td::before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            text-transform: uppercase;
        }

        .filter-buttons button {
            width: 100%;
            margin: 5px 0;
        }

        .book-appointment-button {
            width: 100%;
        }
    }
</style>

<body>
    <main>
        <h1>Appointments</h1>
        <strong>Find all your appointments here</strong>
        <button type="button" class="book-appointment-button" onclick="window.location.href='submit_booking.php';">Add New Appointment</button>
        <br><br>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <button type="button" class="filter-appointment-button" data-status="all">All</button>
            <button type="button" class="filter-appointment-button" data-status="Pending">Pending</button>
            <button type="button" class="filter-appointment-button" data-status="Scheduled">Scheduled</button>
            <button type="button" class="filter-appointment-button" data-status="Completed">Completed</button>
            <button type="button" class="filter-appointment-button" data-status="Cancelled">Cancelled</button>
        </div>
        <br>
        <?php
        // Determine the sort order
        $order = "DESC";
        if (isset($_GET['sort']) && $_GET['sort'] == 'asc') {
            $order = "ASC";
        }

        // Ensure the patient ID is set
        if ($patient_id !== null) {
            // Prepare the SQL to fetch all appointments for the patient with staff and therapist names
            $sql_appointments = "SELECT a.appointment_id, a.appointment_datetime, a.patient_comments, a.staff_comments, a.status, 
                                s.first_name AS staff_first_name, s.last_name AS staff_last_name, 
                                t.first_name AS therapist_first_name, t.last_name AS therapist_last_name 
                         FROM appointments a
                         LEFT JOIN staffs s ON a.staff_id = s.staff_id
                         LEFT JOIN staffs t ON a.therapist_id = t.staff_id
                         WHERE a.patient_id = ? 
                         ORDER BY a.appointment_datetime $order";

            $stmt_appointments = $conn->prepare($sql_appointments);
            $stmt_appointments->bind_param("i", $patient_id);
            $stmt_appointments->execute();
            $result_appointments = $stmt_appointments->get_result();

            // Start the table and headers
            echo "<table>";
            echo "<tr>
            <th><a href='?sort=" . ($order == "ASC" ? "desc" : "asc") . "'>Appointment Date & Time</a></th>
            <th>Patient Comments</th>
            <th>Assigned Therapist</th>
            <th>Approved By</th>
            <th>Staff Comments</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>";

            // Loop through the results and display each appointment
            while ($row_appointments = $result_appointments->fetch_assoc()) {
                $status = htmlspecialchars($row_appointments['status']);
                echo "<tr data-status='$status'>";
                echo "<td>" . (!empty($row_appointments['appointment_datetime']) ? date("d-m-Y H:i", strtotime($row_appointments['appointment_datetime'])) : "-") . "</td>";
                echo "<td>" . (!empty($row_appointments['patient_comments']) ? htmlspecialchars($row_appointments['patient_comments']) : "-") . "</td>";

                // Conditionally hide therapist name if the status is pending or cancelled
                if ($status === 'Pending' || $status === 'Cancelled') {
                    echo "<td>-</td>";
                } else {
                    echo "<td>" . (!empty($row_appointments['therapist_first_name']) && !empty($row_appointments['therapist_last_name']) ? htmlspecialchars($row_appointments['therapist_first_name']) . " " . htmlspecialchars($row_appointments['therapist_last_name']) : "-") . "</td>";
                }

                echo "<td>" . (!empty($row_appointments['staff_first_name']) && !empty($row_appointments['staff_last_name']) ? htmlspecialchars($row_appointments['staff_first_name']) . " " . htmlspecialchars($row_appointments['staff_last_name']) : "-") . "</td>";
                echo "<td>" . (!empty($row_appointments['staff_comments']) ? htmlspecialchars($row_appointments['staff_comments']) : "-") . "</td>";
                echo "<td>" . $status . "</td>";

                // Conditionally display buttons based on status
                if ($status === 'Pending' || $status === 'Scheduled') {
                    echo "<td>
                    <button type='button' class='book-appointment-button' onclick='cancelAppointment(" . $row_appointments['appointment_id'] . ")'>Cancel</button>
                </td>";
                } else {
                    echo "<td></td>"; // Empty cell for completed or cancelled appointments
                }
                echo "</tr>";
            }
            echo "</table>";
            $stmt_appointments->close();
        } else {
            echo "Please log in to view your appointments.";
        }
        $conn->close();
        ?>
    </main>

    <script>
        function cancelAppointment(appointmentId) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                window.location.href = 'booking.php?cancel_appointment_id=' + appointmentId;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            function filterTable(status) {
                var rows = document.querySelectorAll('table tr[data-status]');
                rows.forEach(row => {
                    var rowStatus = row.getAttribute('data-status');
                    if (status === 'all' || rowStatus === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            document.querySelectorAll('.filter-buttons button').forEach(button => {
                button.addEventListener('click', function() {
                    var status = this.getAttribute('data-status');
                    filterTable(status);
                });
            });
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>

</html>