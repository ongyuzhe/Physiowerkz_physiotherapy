<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and has the physiotherapist role, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'physiotherapist') {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

include 'includes/header_staff.php';

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

$events = []; // Initialize events array

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];

    // Retrieve physiotherapist id based on username
    $sql_therapistid = "SELECT staff_id FROM staffs WHERE username = ?";

    $stmt_therapist = $conn->prepare($sql_therapistid);
    $stmt_therapist->bind_param("s", $username);
    $stmt_therapist->execute();
    $result = $stmt_therapist->get_result();
    $row = $result->fetch_assoc();
    $staff_id = $row['staff_id'] ?? null;
    $stmt_therapist->close();

    if ($staff_id) { // Check if staff_id is not null
        // Retrieve physiotherapist schedule based on id
        $sql_schedules = "SELECT * FROM schedules WHERE staff_id = ?";
        
        $stmt_schedules = $conn->prepare($sql_schedules);
        $stmt_schedules->bind_param("s", $staff_id);
        $stmt_schedules->execute();
        $schedules_result = $stmt_schedules->get_result();
        
        // Fetch all schedules as an associative array
        $schedules = $schedules_result->fetch_all(MYSQLI_ASSOC);
        $stmt_schedules->close();

        // Debugging: Print schedules fetched from the database
        echo "<pre>";
        print_r($schedules);
        echo "</pre>";
        
        // Convert data into FullCalendar-compatible format
        $events = array_map(function($schedule) {
            return [
                'title' => ucfirst($schedule['type']),
                'start' => $schedule['start_time'],
                'end' => $schedule['end_time'],
                'className' => $schedule['type'] // For CSS styling
            ];
        }, $schedules);
        
        // Debugging: Print the events array before JSON encoding
        echo "<pre>";
        print_r($events);
        echo "</pre>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physiotherapist Schedule</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">

    <!-- FullCalendar CSS and JS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>

    <style>
        table,
        th,
        td {
            border: 1px solid black;
        }

        .available {
            background-color: green;
        }

        .booked {
            background-color: red;
        }

        .unavailable {
            background-color: gray;
        }

        table {
            border-collapse: collapse;
            margin: 20px 0;
            width: 100%;
        }

        th,
        td {
            padding: 5px;
            text-align: center;
            border: 1px solid black;
            font-size: 12px;
        }

        th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .schedule-container {
            overflow-x: auto;
        }

        .save-button-container {
            margin-top: 20px;
        }

        .legend {
            margin: 20px 0;
        }

        .legend h4 {
            margin: 5px 0;
        }

        /* Custom color coding for different types */
        .break { background-color: orange; }
        .leave { background-color: green; }
        .booked { background-color: red; }
        .fc-nonbusiness { background-color: gray; } /* Gray for non-business hours */

    </style>
</head>

<body>
    <?php include 'includes/header_staff.php'; ?>
    <main>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>


