<?php
session_start();

// Define roles array for flexibility
$valid_roles = ['admin', 'physiotherapist'];
$role = $_SESSION['role'] ?? '';
// Check if the user is logged in and has the appropriate role, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($role, $valid_roles)) {
    header("location: login.php");
    exit;
}

$id = $_SESSION["id"];

// Include database connection settings
require_once 'includes/settings.php';

// Include different headers based on role
if ($role === 'admin') {
    include 'includes/header_admin.php';
} elseif ($role === 'physiotherapist') {
    include 'includes/header_staff.php';
}

// Set default values for sorting and search
$search = $_GET['search'] ?? '';
$sort_column = $_GET['sort_column'] ?? 'patient_id'; // Default sort by patient_id
$sort_order = $_GET['sort_order'] ?? 'ASC'; // Default sort order is ascending

// Toggle sorting order for next click (for sorting links)
$next_sort_order = ($sort_order === 'ASC') ? 'DESC' : 'ASC';

// Modify the SQL query to include search and sorting
$sql = "SELECT patient_id, username, first_name, last_name, 
            personality, compliance, bodytype, focus, lifestyle, healthstatus, goal, outcome 
        FROM patients
        WHERE username LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?
        ORDER BY $sort_column $sort_order";

$stmt = $conn->prepare($sql);
$search_param = "%" . $search . "%";
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

// Check if any records were returned
$patients = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Combine first and last name
        $full_name = $row['first_name'] . ' ' . $row['last_name'];

        // Combine the patient profile details into one string with newline characters
        $patient_profile = "Personality: " . $row['personality'] . "\n" .
            "Compliance: " . $row['compliance'] . "\n" .
            "Body Type: " . $row['bodytype'] . "\n" .
            "Focus: " . $row['focus'] . "\n" .
            "Lifestyle: " . $row['lifestyle'] . "\n" .
            "Health Status: " . $row['healthstatus'] . "\n" .
            "Goal: " . $row['goal'] . "\n" .
            "Outcome: " . $row['outcome'];

        // Store data in an array
        $patients[] = [
            'patient_id' => $row['patient_id'],
            'username' => $row['username'],
            'full_name' => $full_name,
            'patient_profile' => $patient_profile
        ];
    }
} else {
    $patients = [];
}

$id = $_SESSION["id"];
// Fetch appointments that are not canceled
$appointments = [];

// Check the user's role (assuming $role and $id are already defined)
if ($role === 'admin') {
    // Admin: Fetch all appointments (no need for therapist_id condition)
    $sql_appointments = "SELECT appointment_id, patient_id, appointment_datetime, status, patient_comments, staff_comments 
                         FROM appointments 
                         WHERE status != 'Cancelled'";
                         
    // Prepare the statement
    $stmt_appointments = $conn->prepare($sql_appointments);

} else {
    // Non-admin: Fetch appointments for the specific therapist
    $sql_appointments = "SELECT appointment_id, patient_id, appointment_datetime, status, patient_comments, staff_comments 
                         FROM appointments 
                         WHERE status != 'Cancelled' AND therapist_id = ?";

    // Prepare the statement
    $stmt_appointments = $conn->prepare($sql_appointments);

    // Bind the therapist ID (assuming $id is the therapist_id for non-admins)
    $stmt_appointments->bind_param("i", $id);
}

// Execute the query
$stmt_appointments->execute();

// Get the result
$result_appointments = $stmt_appointments->get_result();

// Fetch appointments into an array
while ($row_appointment = $result_appointments->fetch_assoc()) {
    $appointments[] = [
        'appointment_id' => $row_appointment['appointment_id'],
        'patient_id' => $row_appointment['patient_id'],
        'appointment_datetime' => $row_appointment['appointment_datetime'],
        'status' => $row_appointment['status'],
        'patient_comments' => $row_appointment['patient_comments'],
        'staff_comments' => $row_appointment['staff_comments'],
    ];
}

// Close the statement
$stmt_appointments->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Treatment - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">

    <style>
        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
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

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
            font-size: 14px;
        }

        /* Adjust the width of individual columns */
        th:nth-child(1),
        td:nth-child(1) {
            width: 5%;
            /* Patient ID */
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 10%;
            /* Username */
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 10%;
            /* Full Name */
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 30%;
            /* Patient Profile (larger width for more text) */
        }

        th:nth-child(5),
        td:nth-child(5) {
            width: 30%;
            /* Treatment buttons */
        }

        th {
            background-color: #3498db;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        th:hover {
            background-color: #2980b9;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ecf0f1;
        }

        /* Style for the select dropdown */
        td select {
            width: 73%;
            /* Full width */
            padding: 10px;
            /* Padding for better touch area */
            border: 1px solid #bdc3c7;
            /* Light gray border */
            border-radius: 5px;
            /* Rounded corners */
            font-size: 16px;
            /* Font size */
            margin-bottom: 10px;
            /* Space below the dropdown */
            transition: border-color 0.3s ease;
            /* Smooth transition for focus */
        }

        /* Change border color on focus */
        td select:focus {
            border-color: #3498db;
            /* Blue border on focus */
        }

        /* Message area styling */
        .message {
            margin: 5px 0;
            /* Margin around message */
        }

        /* Button container for consistent layout */
        .buttons-wrapper {
            display: flex;
            /* Flexbox for layout */
            flex-wrap: wrap;
            /* Stack buttons vertically */
            gap: 10px;
            /* Space between buttons */
        }

        .buttons-wrapper button {
            width: 100%;
            /* Full width */
            padding: 10px;
            /* Padding for better touch area */
            border: 1px solid #bdc3c7;
            /* Light gray border */
            border-radius: 5px;
            /* Rounded corners */
            font-size: 16px;
            /* Font size */
            margin-bottom: 10px;
            /* Space below the dropdown */
            transition: border-color 0.3s ease;
            /* Smooth transition for focus */

        }

        /* Button styling */
        td button {
            width: 100%;
            /* Full width of container */
            padding: 12px;
            /* Padding for better touch area */
            background-color: #2980b9;
            /* Button background color */
            color: white;
            /* Button text color */
            border: none;
            /* No border */
            border-radius: 5px;
            /* Rounded corners */
            font-size: 16px;
            /* Font size */
            cursor: pointer;
            /* Pointer on hover */
            transition: background-color 0.3s ease, transform 0.2s ease;
            /* Smooth transitions */
        }

        /* Button hover effect */
        td button:hover {
            background-color: #3498db;
            /* Lighter blue on hover */
            transform: scale(1.05);
            /* Slightly grow on hover */
        }

        /* Disabled button styling */
        td button:disabled {
            background-color: #bdc3c7;
            /* Grey background for disabled state */
            cursor: not-allowed;
            /* Change cursor to indicate disabled */
            opacity: 0.7;
            /* Slightly transparent */
        }
    </style>
</head>

<body>
    <main>
        <h1>View Treatment</h1>

        <!-- Search Form -->
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search by username or full name" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
            <form method="GET" action="" style="display: inline-block;">
                <button type="submit">Reset</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <!-- Sortable column headers -->
                    <th><a href="?search=<?php echo htmlspecialchars($search); ?>&sort_column=patient_id&sort_order=<?php echo $next_sort_order; ?>">Patient ID</a></th>
                    <th><a href="?search=<?php echo htmlspecialchars($search); ?>&sort_column=username&sort_order=<?php echo $next_sort_order; ?>">Username</a></th>
                    <th><a href="?search=<?php echo htmlspecialchars($search); ?>&sort_column=first_name&sort_order=<?php echo $next_sort_order; ?>">Full Name</a></th>
                    <th>Patient Profile</th>
                    <th>Treatment</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($patients)): ?>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                            <td><?php echo htmlspecialchars($patient['username']); ?></td>
                            <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                            <td class="patient-profile">
                                <?php echo nl2br(htmlspecialchars($patient['patient_profile'])); ?>
                            </td>
                            <td>
                                <div class="button-container">
                                    <!-- Dropdown for selecting an appointment -->
                                    <select name="appointment_id" id="appointment_id_<?php echo $patient['patient_id']; ?>" onchange="setAppointmentId(<?php echo $patient['patient_id']; ?>)" <?php echo empty($appointments) ? 'disabled' : ''; ?>>
                                        <option value="">Select Appointment</option>
                                        <?php
                                        foreach ($appointments as $appointment) {
                                            if ($appointment['patient_id'] == $patient['patient_id']) {
                                                echo '<option value="' . htmlspecialchars($appointment['appointment_id']) . '">' .
                                                    'ID: ' . htmlspecialchars($appointment['appointment_id']) . ' - ' .
                                                    htmlspecialchars($appointment['appointment_datetime']) . ' (' .
                                                    htmlspecialchars($appointment['status']) . ')</option>';
                                            }
                                        }
                                        ?>
                                    </select>

                                    <div id="message_<?php echo $patient['patient_id']; ?>" class="message" style="color: red; display: none;">Please choose a session</div>

                                    <div class="buttons-wrapper">
                                        <div class="button-column left">
                                            <form action="treatmentdoctor1.php" method="POST">
                                                <input type="hidden" name="patient_id" value="<?php echo $patient['patient_id']; ?>">
                                                <input type="hidden" name="appointment_id" id="hidden_appointment_id_1_<?php echo $patient['patient_id']; ?>" value="">
                                                <input type="hidden" name="username" value="<?php echo $patient['username']; ?>">
                                                <button type="submit" id="pain_activities_<?php echo $patient['patient_id']; ?>" class="pain-activities-button-<?php echo $patient['patient_id']; ?>" disabled>Pain and Activities</button>
                                            </form>
                                            <form action="treatmentdoctor2.php" method="POST">
                                                <input type="hidden" name="patient_id" value="<?php echo $patient['patient_id']; ?>">
                                                <input type="hidden" name="appointment_id" id="hidden_appointment_id_2_<?php echo $patient['patient_id']; ?>" value="">
                                                <input type="hidden" name="username" value="<?php echo $patient['username']; ?>">
                                                <button type="submit" id="assessment_findings_<?php echo $patient['patient_id']; ?>" class="assessment-findings-button-<?php echo $patient['patient_id']; ?>" disabled>Assessment Findings</button>
                                            </form>
                                        </div>
                                        <div class="button-column right">
                                            <form action="treatmentdoctor3.php" method="POST">
                                                <input type="hidden" name="patient_id" value="<?php echo $patient['patient_id']; ?>">
                                                <input type="hidden" name="appointment_id" id="hidden_appointment_id_3_<?php echo $patient['patient_id']; ?>" value="">
                                                <input type="hidden" name="username" value="<?php echo $patient['username']; ?>">
                                                <button type="submit" id="exercise_suggestions_<?php echo $patient['patient_id']; ?>" class="exercise-suggestions-button-<?php echo $patient['patient_id']; ?>" disabled>Exercise Suggestions</button>
                                            </form>
                                            <form action="treatmentdoctor4.php" method="POST">
                                                <input type="hidden" name="patient_id" value="<?php echo $patient['patient_id']; ?>">
                                                <input type="hidden" name="appointment_id" id="hidden_appointment_id_4_<?php echo $patient['patient_id']; ?>" value="">
                                                <input type="hidden" name="username" value="<?php echo $patient['username']; ?>">
                                                <button type="submit" id="patient_videos_<?php echo $patient['patient_id']; ?>" class="patient-videos-button-<?php echo $patient['patient_id']; ?>" disabled>Patient Videos</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No patients found.</td>
                        </tr>
                    <?php endif; ?>
            </tbody>
        </table>

    </main>
    <?php include 'includes/footer.php'; ?>
</body>
<script>
    function toggleButtons(patientId) {
        const dropdown = document.getElementById(`appointment_id_${patientId}`);
        const selectedAppointment = dropdown.value;

        // Get the message area for the current patient
        const messageDiv = document.getElementById(`message_${patientId}`);

        // Check if an appointment is selected
        const isDisabled = selectedAppointment === '';

        // Enable or disable buttons accordingly
        document.getElementById(`pain_activities_${patientId}`).disabled = isDisabled;
        document.getElementById(`assessment_findings_${patientId}`).disabled = isDisabled;
        document.getElementById(`exercise_suggestions_${patientId}`).disabled = isDisabled;
        document.getElementById(`patient_videos_${patientId}`).disabled = isDisabled;

        // Show or hide the message based on selection
        if (isDisabled) {
            // Disable buttons and show message
            messageDiv.style.display = 'block';
            setButtonStyleDisabled(patientId); // Call function to apply disabled style
        } else {
            // Hide the message
            messageDiv.style.display = 'none';
            setButtonStyleEnabled(patientId); // Call function to reset button style
        }
    }

    // Function to set disabled button style
    function setButtonStyleDisabled(patientId) {
        const buttons = [
            `pain_activities_${patientId}`,
            `assessment_findings_${patientId}`,
            `exercise_suggestions_${patientId}`,
            `patient_videos_${patientId}`
        ];

        buttons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            button.style.backgroundColor = 'lightgrey'; // Grey color for disabled state
            button.style.cursor = 'not-allowed'; // Change cursor to not-allowed
        });
    }

    // Function to reset button style to enabled state
    function setButtonStyleEnabled(patientId) {
        const buttons = [
            `pain_activities_${patientId}`,
            `assessment_findings_${patientId}`,
            `exercise_suggestions_${patientId}`,
            `patient_videos_${patientId}`
        ];

        buttons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            button.style.backgroundColor = ''; // Reset to original color
            button.style.cursor = ''; // Reset cursor to default
        });
    }

// Function to reset button style to enabled state
function setButtonStyleEnabled(patientId) {
    const buttons = [
        `pain_activities_${patientId}`,
        `assessment_findings_${patientId}`,
        `exercise_suggestions_${patientId}`,
        `patient_videos_${patientId}`
    ];

    buttons.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            button.style.backgroundColor = '#4CAF50'; // Green color for enabled state
            button.style.cursor = 'pointer'; // Change cursor to pointer for enabled state
        }
    });
}

function setAppointmentId(patientId) {
    const dropdown = document.getElementById("appointment_id_" + patientId);
    const selectedAppointmentId = dropdown.value;

    // Debugging: Log the selected appointment ID
    console.log("Selected Appointment ID: " + selectedAppointmentId);

    // Set the hidden appointment ID value for the specific patient
    document.getElementById("hidden_appointment_id_1_" + patientId).value = selectedAppointmentId;
    document.getElementById("hidden_appointment_id_2_" + patientId).value = selectedAppointmentId;
    document.getElementById("hidden_appointment_id_3_" + patientId).value = selectedAppointmentId;
    document.getElementById("hidden_appointment_id_4_" + patientId).value = selectedAppointmentId;

    // Enable or disable buttons based on the selection
    const isDisabled = selectedAppointmentId === '';
    document.getElementById("pain_activities_" + patientId).disabled = isDisabled;
    document.getElementById("assessment_findings_" + patientId).disabled = isDisabled;
    document.getElementById("exercise_suggestions_" + patientId).disabled = isDisabled;
    document.getElementById("patient_videos_" + patientId).disabled = isDisabled;

    // Debugging: Log the hidden input values after setting them
    console.log("Hidden Appointment ID 1: " + document.getElementById("hidden_appointment_id_1_" + patientId).value);
}

</script>

</html>