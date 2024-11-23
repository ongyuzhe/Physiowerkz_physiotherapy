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

        // Convert data into FullCalendar-compatible format
        $events = array_map(function ($schedule) {
            return [
                'title' => isset($schedule['type']) ? ucfirst($schedule['type']) : 'Unknown',
                'start' => isset($schedule['start_time']) ? $schedule['start_time'] : '',
                'end' => isset($schedule['end_time']) ? $schedule['end_time'] : '',
                'className' => isset($schedule['type']) ? $schedule['type'] : 'default'
            ];
        }, $schedules);
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

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>

    <!-- jQuery (ensure this is loaded before FullCalendar JS) -->
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>

    <style>
        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        main {
            max-width: 1500px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Styling the welcome section */
        .welcome-section {
            text-align: center;
            padding: 20px;
        }

        .welcome-section h1 {
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-size: 1.2em;
            color: #7f8c8d;
        }

        /* Center the Legend title and align colors beneath */
        .legend {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
        }

        .legend h2 {
            margin-bottom: 15px;
            /* Add some space below the title */
            font-size: 1.5em;
            color: #2c3e50;
        }

        .legend-colors {
            display: flex;
            justify-content: center;
            gap: 20px;
            /* Space out color labels */
        }

        .legend-item {
            display: flex;
            align-items: center;
        }

        .legend-item div {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            /* Space between color box and label */
            border-radius: 3px;
        }

        /* Colors for each item */
        .legend-item .gray {
            background-color: gray;
        }

        .legend-item .red {
            background-color: red;
        }

        .legend-item .orange {
            background-color: orange;
        }

        .legend-item .green {
            background-color: green;
        }


        /* Schedule calendar styling */
        #calendar {
            max-width: 100%;
            margin: 0 auto;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            padding: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* FullCalendar specific customizations */
        .fc .fc-toolbar-title {
            font-size: 1.8em;
            color: #2c3e50;
        }

        .fc-button {
            background-color: #3498db;
            color: #fff;
            border-radius: 4px;
            padding: 6px 12px;
        }

        .fc-button:hover {
            background-color: #2980b9;
        }

        .fc-event {
            font-size: 0.9em;
        }

        .fc-daygrid-day-number {
            width: 30px;
            min-width: 30px;
            display: inline-block;
            text-align: center;
        }

        .fc-daygrid-day {
            padding: 5px;
            max-width: 50px;
        }

        .fc-daygrid-day-frame {
            padding: 10px;
            max-width: 150px;
        }

        /* Buttons */
        .book-appointment-button {
            display: inline-block;
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
            margin: 10px 0;
        }

        .book-appointment-button:hover {
            background-color: #27ae60;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            border: 1px solid #dcdcdc;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        td {
            font-size: 1em;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            h1 {
                font-size: 2em;
            }

            .book-appointment-button {
                width: 100%;
                font-size: 1.2em;
            }

            table,
            th,
            td {
                font-size: 0.9em;
            }
        }
    </style>

</head>

<body>
    <main>

        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <p>Manage your appointments, view booking forms and health questionnaires by patients.</p>
        </div>
        <h1>My Schedule</h1>
        <strong>View and manage your schedule here.</strong>

        <button type="button" class="book-appointment-button" onclick="window.location.href='insert_schedule.php';">Add
            New Events</button>
        <div id="calendar"></div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: <?php echo json_encode($events); ?>, // Pass PHP events to JavaScript
                    editable: true,
                    eventLimit: true, // Allow "more" link when too many events
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    businessHours: [ // Define business hours
                        {
                            daysOfWeek: [1, 5], // Monday - Friday
                            startTime: '10:30', // 10:30 AM
                            endTime: '19:30' // 7:30 PM
                        },
                        {
                            daysOfWeek: [2], // Tuesday
                            startTime: '07:00', // 7:00 AM
                            endTime: '19:30' // 7:30 PM
                        },
                        {
                            daysOfWeek: [3], // Wednesday
                            startTime: '10:30', // 10:30 AM
                            endTime: '19:30' // 7:30 PM
                        },
                        {
                            daysOfWeek: [6], // Saturday
                            startTime: '08:00', // 8:00 AM
                            endTime: '17:00' // 5:00 PM
                        }
                    ],
                    slotMinTime: "07:00:00", // Earliest time shown
                    slotMaxTime: "20:00:00", // Latest time shown
                    hiddenDays: [4, 0], // Thursday and Sunday hidden (closed)
                    slotDuration: '00:30:00', // 30-minute intervals for week and day views
                    views: {
                        timeGridWeek: {
                            slotLabelInterval: '00:30', // Label intervals (week view)
                        },
                        timeGridDay: {
                            slotLabelInterval: '00:30', // Label intervals (day view)
                        }
                    }
                });

                calendar.render();
            });
        </script>
        <br>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>