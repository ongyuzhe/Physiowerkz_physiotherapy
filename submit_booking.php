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

$date_err = $time_err = $comments_err = "";
$date = $time = $patient_comments = "";

// Check for notification message
$notification_message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);  // Clear the message after displaying

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

    // Check if patient is repeating or first-time
    $is_repeating_patient = false;
    $sql_check_appointments = "SELECT COUNT(*) AS count FROM appointments WHERE patient_id = ?";
    $stmt_check = $conn->prepare($sql_check_appointments);
    $stmt_check->bind_param("i", $patient_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    if ($row_check['count'] > 0) {
        $is_repeating_patient = true;
    }
    $stmt_check->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $patient_id !== null) {

    // Get the date, time, and comments from the form
    $date = trim($_POST['appointment_date']);
    $time = trim($_POST['appointment_time']);
    $patient_comments = trim($_POST['patient_comments']);

    // Validate date
    if (empty($date)) {
        $date_err = "Please enter a date.";
    } else {
        $today = strtotime(date('Y-m-d'));
        $appointmentDate = strtotime($date);
        $maxDate = strtotime('+14 days', $today);

        // Check if the appointment date is in the correct range (1-14 days in advance)
        if ($appointmentDate <= $today) {
            $date_err = "The appointment date must be at least 1 day in advance.";
        } elseif ($appointmentDate > $maxDate) {
            $date_err = "The appointment date cannot be more than 14 days in advance.";
        } else {
            // Check if the appointment falls on Thursday (4) or Sunday (0)
            $dayOfWeek = date('w', $appointmentDate);
            if ($dayOfWeek == 4 || $dayOfWeek == 0) {
                $date_err = "Centre is closed on Thursdays and Sundays.";
            }
        }
    }

    // Validate time only if date is valid
    if (empty($date_err) && empty($time)) {
        $time_err = "Please enter a time.";
    } elseif (empty($date_err)) {
        $appointmentTime = strtotime($time);

        // Define allowed times based on the day of the week
        switch ($dayOfWeek) {
            case 1: // Monday
                $minTime = strtotime('10:30');
                $maxTime = strtotime('19:30');
                break;
            case 2: // Tuesday
                $minTime = strtotime('07:00');
                $maxTime = strtotime('19:30');
                break;
            case 3: // Wednesday
                $minTime = strtotime('10:30');
                $maxTime = strtotime('19:30');
                break;
            case 5: // Friday
                $minTime = strtotime('10:30');
                $maxTime = strtotime('19:30');
                break;
            case 6: // Saturday
                $minTime = strtotime('08:00');
                $maxTime = strtotime('17:00');
                break;
            default:
                $minTime = $maxTime = null; // For safety, though this case should never be reached
                break;
        }

        // Validate time based on the day's time restrictions
        if ($appointmentTime < $minTime || $appointmentTime > $maxTime) {
            $time_err = "The appointment time must be between " . date('g:i A', $minTime) . " and " . date('g:i A', $maxTime) . " on " . date('l', $appointmentDate) . ".";
        }
    }

    // Validate patient comments
    if (!empty($patient_comments) && strlen($patient_comments) > 120) {
        $comments_err = "Comments must not exceed 120 characters.";
    }

    // Combine date and time into a single datetime string
    if (empty($date_err) && empty($time_err) && empty($comments_err)) {
        $appointment_datetime = $date . ' ' . $time;

        // Check if patient_comments is empty and set to NULL if so
        $patient_comments = !empty($patient_comments) ? $patient_comments : null;

        // Ensure appointment_datetime is valid
        $formatted_datetime = date('Y-m-d H:i:s', strtotime($appointment_datetime));

        // Assign therapist based on patient type
        if ($is_repeating_patient) {
            // If the patient is repeating, allow for manual assignment or auto-assign a specific therapist
            $therapist_id = $_POST['therapist_id'] ?? null;

            if (empty($therapist_id)) {
                // If therapist_id is not provided, automatically assign a specific therapist (for example, one with a specific role or ID)
                $sql_therapist = "SELECT staff_id FROM staffs WHERE role = 'physiotherapist' LIMIT 1";  // Replace 'specific_role' with appropriate value
                $stmt_therapist = $conn->prepare($sql_therapist);
                $stmt_therapist->execute();
                $result_therapist = $stmt_therapist->get_result();
                $row_therapist = $result_therapist->fetch_assoc();
                $therapist_id = $row_therapist['staff_id'] ?? null;
                $stmt_therapist->close();
            }
        } else {
            // Auto-assign to any available therapist for first-time patients
            $sql_therapist = "SELECT staff_id FROM staffs WHERE role = 'physiotherapist' ORDER BY RAND() LIMIT 1";
            $stmt_therapist = $conn->prepare($sql_therapist);
            $stmt_therapist->execute();
            $result_therapist = $stmt_therapist->get_result();
            $row_therapist = $result_therapist->fetch_assoc();
            $therapist_id = $row_therapist['staff_id'] ?? null;
            $stmt_therapist->close();
        }
        // Insert into the appointments table
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, therapist_id, appointment_datetime, patient_comments) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $patient_id, $therapist_id, $formatted_datetime, $patient_comments);

        if ($stmt->execute()) {
            // Insert notification into the notifications table
            $notification_message = "Booking sent. A confirmation will be sent at a later date as soon as possible.";
            $sql_notification = "INSERT INTO notifications (patient_id, message, status, appointdatetime, read_status) 
                         VALUES (?, ?, 'Pending', ?, 'unread')";
            $stmt_notification = $conn->prepare($sql_notification);
            $stmt_notification->bind_param("iss", $patient_id, $notification_message, $formatted_datetime);

            if ($stmt_notification->execute()) {
                // Prepare and execute the insert statement for admin notification
                $notification_message_admin = "A new booking request is pending approval. Please review and approve.";
            
                $sql_admin_notification = "INSERT INTO admin_notifications (patient_id, message, change_type, log_id, notification_time, status, admin_id) 
                                           VALUES (?, ?, 'Booking', ?, NOW(), 'Unread', ?)";
                $stmt_admin_notification = $conn->prepare($sql_admin_notification);
            
                $admin_id = 1; // Assign the admin_id as needed
                $log_id = 0; // Set the log_id or fetch it as needed
                $stmt_admin_notification->bind_param("isii", $patient_id, $notification_message_admin, $log_id, $admin_id);
            
                if ($stmt_admin_notification->execute()) {
                    // Close the admin notification statement
                    $stmt_admin_notification->close();
            
                    // Close the appointment statement
                    $stmt->close();
            
                    // Set session message
                    $_SESSION['message'] = $notification_message;
            
                    // Redirect to booking.php
                    header("Location: booking.php");
                    exit;
                } else {
                    echo "Error inserting into admin_notifications: " . htmlspecialchars($stmt_admin_notification->error);
                }
            } else {
                echo "Error inserting into notifications: " . htmlspecialchars($stmt_notification->error);
            }
        }
    }
}

// End of file, flush output buffer
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Booking</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
</head>

<style>
    main {
        padding: 20px;
        max-width: 600px;
        margin: 40px auto;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        font-size: 2.5em;
        margin-bottom: 20px;
        color: #2c3e50;
    }

    strong {
        display: block;
        margin-bottom: 15px;
        font-size: 16px;
        color: #333;
    }

    /* Form Styles */
    form {
        display: flex;
        flex-direction: column;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    input[type="date"],
    input[type="time"],
    textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s;
    }

    input[type="date"]:focus,
    input[type="time"]:focus,
    textarea:focus {
        border-color: #4a90e2;
        outline: none;
    }

    textarea {
        resize: vertical;
    }

    input[type="submit"] {
        background-color: #4a90e2;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    input[type="submit"]:hover {
        background-color: #357abd;
    }

    /* Error Message Styles */
    .error {
        color: #e74c3c;
        font-size: 14px;
        margin-top: -15px;
        margin-bottom: 20px;
        display: block;
    }

    /* Responsive Styles */
    @media (max-width: 600px) {
        main {
            padding: 15px;
            margin: 10px;
        }

        h1 {
            font-size: 24px;
        }

        input[type="submit"] {
            font-size: 16px;
            padding: 10px;
        }
    }

    /* Notification Styles */
    .notification {
        background-color: #e9f7ef;
        color: #2ecc71;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #2ecc71;
        border-radius: 4px;
        text-align: center;
    }
</style>

<body>
    <main>
        <h1>Book an Appointment</h1>

        <?php if (!empty($notification_message)): ?>
            <div class="notification">
                <?php echo htmlspecialchars($notification_message); ?>
            </div>
        <?php endif; ?>

        <strong>Appointment Policy:</strong> You may reschedule or cancel an appointment, but it must be done at least 2
        days in advance, or charges will apply.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <!-- Appointment Date -->
            <label for="appointment_date">Appointment Date:</label>
            <input type="date" id="appointment_date" name="appointment_date"
                value="<?php echo htmlspecialchars($date); ?>">
            <span class="error"><?php echo $date_err; ?></span>

            <!-- Appointment Time -->
            <label for="appointment_time">Appointment Time:</label>
            <input type="time" id="appointment_time" name="appointment_time"
                value="<?php echo htmlspecialchars($time); ?>">
            <span class="error"><?php echo $time_err; ?></span>

            <!-- Patient Comments -->
            <label for="patient_comments">Additional Comments (Optional):</label>
            <textarea id="patient_comments" name="patient_comments" rows="4"
                maxlength="120"><?php echo htmlspecialchars($patient_comments); ?></textarea>
            <span class="error"><?php echo $comments_err; ?></span>

            <!-- Submit Button -->
            <input type="submit" value="Submit Booking">
        </form>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>