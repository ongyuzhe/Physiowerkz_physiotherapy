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

    if ($staff_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $type = $_POST['type'];

            try {
                $stmt = $conn->prepare("INSERT INTO schedules (staff_id, start_time, end_time, type) VALUES (?, ?, ?, ?)");
                $stmt->execute([$staff_id, $start_time, $end_time, $type]);
                echo "<script>
                alert('Schedule submitted successfully.');
                window.location.href='welcome_staff.php';
              </script>";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
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
        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        strong {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Form styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fafafa;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.05);
            max-width: 500px;
            margin: 0 auto;
        }

        form label {
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        input[type="datetime-local"]:focus,
        select:focus {
            border-color: #34C8F5;
            outline: none;
        }

        button[type="submit"] {
            background-color: #34C8F5;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 1.1em;
        }

        button[type="submit"]:hover {
            background-color: #259fcf;
        }

        /* Adjust responsiveness */
        @media (max-width: 768px) {

            form,
            table {
                font-size: 0.9em;
            }

            main {
                padding: 10px;
            }

            button[type="submit"] {
                font-size: 1em;
                padding: 8px 12px;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header_staff.php'; ?>
    <main>
        <h1>Schedule Calendar</h1>
        <strong>View and manage your schedule here.</strong>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

            <label for="start_time">Start Time:</label>
            <input type="datetime-local" name="start_time" required><br><br>

            <label for="end_time">End Time:</label>
            <input type="datetime-local" name="end_time" required><br><br>

            <label for="type">Type:</label>
            <select name="type" required>
                <option value="break">Break</option>
                <option value="leave">Holiday Leave</option>
            </select><br><br>

            <button type="submit">Submit</button>
        </form>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>