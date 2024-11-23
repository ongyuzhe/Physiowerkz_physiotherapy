<?php

// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Define roles array for flexibility
$valid_roles = ['admin', 'physiotherapist', 'superadmin'];
$role = $_SESSION['role'] ?? '';
// Check if the user is logged in and has the appropriate role, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($role, $valid_roles)) {
    header("location: login.php");
    exit;
}

$id = $_SESSION["id"];

// Include database connection settings
require_once 'includes/settings.php';

// Initialize error variables
$errors = [
    'username' => '',
    'password' => '',
    'confirm_password' => '',
    'email' => '',
    'gender' => '',
    'first_name' => '',
    'last_name' => '',
    'dob' => '',
    'contact_num' => '',
    'preferred_communication' => '',
    'preferred_class' => ''
];

function get_role_by_id($id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT role FROM staffs WHERE staff_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['role'];
    }

    return null; // Return null if no role found
}

// Function to log patient changes
function log_patient_change($patient_id, $change_type, $changed_by, $message, $old_values = null, $new_values = null, $id = null)
{
    global $conn;

    // Log changes in patient_changes_log
    $stmt = $conn->prepare("INSERT INTO patient_changes_log (patient_id, change_type, changed_by, old_values, new_values) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $patient_id, $change_type, $changed_by, $old_values, $new_values);
    $stmt->execute();
    $log_id = $stmt->insert_id; // Get the last inserted log_id
    $stmt->close();

    // Log notification in admin_notifications
    $stmt = $conn->prepare("INSERT INTO admin_notifications (patient_id, change_type, changed_by, log_id, notification_time, admin_id, status, message) VALUES (?, ?, ?, ?, NOW(), ?, 'Unread', ?)");
    $stmt->bind_param("ississ", $patient_id, $change_type, $changed_by, $log_id, $id, $message);
    $stmt->execute();
    $stmt->close();

    // Check if the role is physiotherapist and insert into notificationstaff table
    $valid_roles = ['admin', 'physiotherapist'];

    // Assuming you have a way to get the role for the given ID, e.g., a function get_role_by_id($id)
    $role = get_role_by_id($id); // You will need to implement this function to get the role

    if (in_array($role, $valid_roles) && $role === 'physiotherapist') {
        // Insert into notificationstaff
        $stmt = $conn->prepare("INSERT INTO notificationstaff (staff_id, message, status, read_status) VALUES (?, ?, null, 'unread')");
        $stmt->bind_param("is", $id, $message);
        $stmt->execute();
        $stmt->close();
    }
}


// Include different headers based on role
if ($role === 'admin') {
    include 'includes/header_admin.php';
} elseif ($role === 'hr') {
    include 'includes/header_staff.php';
} elseif ($role === 'physiotherapist') {
    include 'includes/header_staff.php';
} elseif ($role === 'superadmin') {
    include 'includes/header_superadmin.php';
}
// Define sort order and column
$order = $_GET['order'] ?? 'ASC';
$column = $_GET['column'] ?? 'patient_id';

// Define search term
$search = $_GET['search'] ?? '';

// Fetch patient data from the database
$sql = "SELECT patient_id, username, email, gender, first_name, last_name, dob, contact_num, lastlogin, preferred_communication, class, survey_status  
        FROM patients 
        WHERE username LIKE ? 
        OR email LIKE ? 
        OR first_name LIKE ? 
        OR last_name LIKE ? 
        ORDER BY $column $order";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Function to toggle sort order
function sortOrder($column)
{
    $order = $_GET['order'] ?? 'ASC';
    $order = ($order === 'ASC') ? 'DESC' : 'ASC';
    $search = $_GET['search'] ?? '';
    return "?column=$column&order=$order&search=$search";
}

// Ensure lock_status is set for current user session
$lock_status = $_SESSION['lock_status'] ?? 'No';

if ($lock_status === 'Yes') {
    echo "<script>alert('You are restricted from editing or adding patient due to your lock status.');</script>";
}

// Handle edit
// Prevent edit operation if lock_status is 'Yes'
if (isset($_POST['edit_id']) && $lock_status === 'Yes') {
    echo "<script>alert('You are currently locked and cannot edit patient details.');</script>";
} else if (isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];

    // Fetch old values
    $stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $old_values = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Update patient record
    $stmt = $conn->prepare("UPDATE patients SET username=?, email=?, gender=?, first_name=?, last_name=?, dob=?, contact_num=?, preferred_communication=?, class=?, survey_status=? WHERE patient_id=?");
    $stmt->bind_param("ssssssssssi", $_POST['username'], $_POST['email'], $_POST['gender'], $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['contact_num'], $_POST['preferred_communication'], $_POST['class'], $_POST['survey_status'], $edit_id);
    $stmt->execute();
    $stmt->close();

    $notification_message = "Patient change logged for patient ID: " . $edit_id;
    // Log the change and redirect
    log_patient_change($edit_id, 'Update!', $_SESSION['username'], $notification_message, json_encode($old_values), json_encode($_POST), $id);

    echo "<script>
        window.location.href = 'manage_patient.php';
      </script>";
    exit();
}

// Handle delete
// Prevent delete operation if lock_status is 'Yes'
if (isset($_POST['delete_id']) && $lock_status === 'Yes') {
    echo "<script>alert('You are currently locked and cannot delete patient records.');</script>";
} else if (isset($_POST['delete_id'])) {
    // Fetch old values before deleting
    $stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->bind_param("i", $_POST['delete_id']);
    $stmt->execute();
    $old_values = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Delete the patient record
    $stmt = $conn->prepare("DELETE FROM patients WHERE patient_id=?");
    $stmt->bind_param("i", $_POST['delete_id']);
    $stmt->execute();
    $stmt->close();

    // Log the change
    log_patient_change($_POST['delete_id'], 'Deleted!', $_SESSION['username'], "Patient's record deleted!", json_encode($old_values), null, $id);

    echo "<script>
        window.location.href = 'manage_patient.php';
      </script>";
    exit();
}

$errorMessages = [];

// Prevent add operation if lock_status is 'Yes'
if ($_SERVER["REQUEST_METHOD"] == "POST" && $lock_status === 'Yes') {
    echo "<script>alert('You are currently locked and cannot add new patients.');</script>";
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["username"]) && !empty($_POST["password"])) {

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
        $errorMessages[] = $username_err;
    } else {
        // Prepare a select statement to check if username exists
        $sql = "SELECT patient_id FROM patients WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $username_err = "This username is already taken.";
                    $errorMessages[] = $username_err;
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                $errorMessages[] = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
        $errorMessages[] = $email_err;
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
        $errorMessages[] = $email_err;
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate gender
    if (empty(trim($_POST["gender"]))) {
        $gender_err = "Please select your gender.";
        $errorMessages[] = $gender_err;
    } else {
        $gender = trim($_POST["gender"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
        $errorMessages[] = $password_err;
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
        $errorMessages[] = $password_err;
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
        $errorMessages[] = $confirm_password_err;
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
            $errorMessages[] = $confirm_password_err;
        }
    }

    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name.";
        $errorMessages[] = $first_name_err;
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name.";
        $errorMessages[] = $last_name_err;
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate date of birth
    if (empty(trim($_POST["dob"]))) {
        $dob_err = "Please enter your date of birth.";
        $errorMessages[] = $dob_err;
    } else {
        $dob = trim($_POST["dob"]);
    }

    // Validate contact number
    $contact_num = trim($_POST["contact_num"]);
    $area_code = trim($_POST["area_code"]);

    // Combine area code with contact number
    $full_contact_number = $area_code . $contact_num;

    if (!empty($contact_num)) {
        if (!preg_match('/^[1-9][0-9]{9,14}$/', $contact_num)) {
            $contact_num_err = "Contact number must be between 10 and 15 digits long, should not start with 0, and should only contain digits.";
            $errorMessages[] = $contact_num_err;
        }
    } else {
        $contact_num_err = "Please fill in your contact number.";
        $errorMessages[] = $contact_num_err;
    }

    // Validate preferred communication 
    $preferred_communication = trim($_POST["preferred_communication"]);

    // Validate preferred class
    $preferred_class = trim($_POST["preferred_class"]);

    // Validate survey status
    if (!isset($_POST["survey_status"])) {
        $survey_status_err = "Please select a survey status.";
        $errorMessages[] = $survey_status_err;
    } else {
        $survey_status = (int)$_POST["survey_status"]; // Ensure it's an integer
    }


    // Check if there are any errors
    if (empty($errorMessages)) {

        $sql = "INSERT INTO patients (username, password, email, gender, first_name, last_name, dob, contact_num, preferred_communication, ques_status, class, survey_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssssssi", $param_username, $param_password, $param_email, $param_gender, $param_first_name, $param_last_name, $param_dob, $param_contact_num, $param_preferred_communication, $param_preferred_class, $survey_status);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Hashed password
            $param_email = $email;
            $param_gender = $_POST["gender"];
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_dob = $dob;
            $param_contact_num = $full_contact_number;
            $param_preferred_communication = $_POST["preferred_communication"];
            $param_preferred_class = $_POST["preferred_class"];

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Log the new insertion
                $new_patient_id = $stmt->insert_id;

                log_patient_change($new_patient_id, 'Inserted!', $_SESSION['username'], "New patient has been added!", json_encode($_POST), null, $id);

                // Redirect to manage_patient page after successful addition
                echo "<script>
                        alert('Patient added successfully!');
                        window.location.href='manage_patient.php';
                      </script>";
                exit();
            } else {
                $errorMessages[] = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // If there are errors, show them in a popup
    if (!empty($errorMessages)) {
        echo "<script>
            window.onload = function() {
                alert('" . implode("\\n", $errorMessages) . "');
            }
        </script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patient - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        /* General Page Styling */
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
            table-layout: fixed;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
            word-wrap: break-word;
            font-size: 14px;
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

        /* Action Buttons in Table */
        .actions button {
            margin-right: 5px;
            padding: 10px 20px;
            background-color: #e67e22;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .actions button:hover {
            background-color: #d35400;
            transform: scale(1.05);
        }

        /* Form Group Styling */
        .form-group input[type="text"],
        .form-group input[type="submit"],
        .form-group button {
            margin-right: 10px;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #bdc3c7;
            font-size: 14px;
        }

        /* Add Patient Form Styling */
        #addpatientForm {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 600px;
            max-height: 30vh;
            overflow-y: auto;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #bdc3c7;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        #addpatientForm h2 {
            margin-top: 0;
            color: #2c3e50;
        }

        #addpatientForm button {
            padding: 10px 15px;
            margin-right: 10px;
            border-radius: 4px;
            border: none;
            background-color: #3498db;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #addpatientForm button:hover {
            background-color: #2980b9;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
                gap: 15px;
            }

            .search-container input[type="text"] {
                width: 100%;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
                width: 100%;
            }

            th,
            td {
                text-align: left;
                padding: 10px;
                word-wrap: break-word;
            }

            .form-group .search-container {
                flex-direction: column;
                gap: 5px;
            }

            #addpatientForm {
                width: 100%;
            }
        }
    </style>
    <script>
        function editRow(patientId) {
            var row = document.getElementById("row-" + patientId);
            var cells = row.getElementsByTagName("td");
            for (var i = 1; i < cells.length - 1; i++) {
                var cell = cells[i];

                if (cell.dataset.field === 'lastlogin') {
                    continue;
                }

                // Handle Gender, Preferred Communication, and Class as dropdowns
                if (cell.dataset.field === 'gender' || cell.dataset.field === 'preferred_communication' || cell.dataset.field === 'class' || cell.dataset.field === 'survey_status') {
                    var select = document.createElement("select");
                    select.name = cell.dataset.field;
                    select.className = cell.dataset.field + '-select';

                    // Set options based on field type
                    var options;
                    if (cell.dataset.field === 'gender') {
                        options = ['Male', 'Female'];
                    } else if (cell.dataset.field === 'preferred_communication') {
                        options = ['Email', 'Phone', 'SMS']; // Add appropriate communication methods here
                    } else if (cell.dataset.field === 'class') {
                        options = ['Class 1', 'Class 2', 'Class 3', 'Class 4']; // Add your class options here
                    } else if (cell.dataset.field === 'survey_status') {
                        options = ['Yes', 'No']; // Add your class options here
                    }


                    // Create and append options
                    options.forEach(function(optionValue) {
                        var option = document.createElement("option");
                        option.value = optionValue;
                        option.text = optionValue.charAt(0).toUpperCase() + optionValue.slice(1);
                        if (cell.innerText === optionValue) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });

                    cell.innerText = '';
                    cell.appendChild(select);
                } else {
                    // Handle other fields as text inputs
                    var input = document.createElement("input");
                    input.type = "text";
                    input.value = cell.innerText;
                    input.name = cell.dataset.field;
                    input.className = "edit-input";
                    cell.innerText = '';
                    cell.appendChild(input);
                }
            }

            var actionsCell = document.getElementById("actions-" + patientId);
            actionsCell.innerHTML = '<button type="button" onclick="saveRow(' + patientId + ')">Save</button><button type="button" onclick="cancelEdit()">Cancel</button>';
        }

        function saveRow(patientId) {
            var row = document.getElementById("row-" + patientId);
            var inputs = row.getElementsByTagName("input");
            var selects = row.getElementsByTagName("select");
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "manage_patient.php";
            for (var i = 0; i < inputs.length; i++) {
                var input = inputs[i];
                var hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = input.name;
                hiddenInput.value = input.value;
                form.appendChild(hiddenInput);
            }
            for (var i = 0; i < selects.length; i++) {
                var select = selects[i];
                var hiddenSelect = document.createElement("input");
                hiddenSelect.type = "hidden";
                hiddenSelect.name = select.name;
                hiddenSelect.value = select.value;
                form.appendChild(hiddenSelect);
            }
            var editIdInput = document.createElement("input");
            editIdInput.type = "hidden";
            editIdInput.name = "edit_id";
            editIdInput.value = patientId;
            form.appendChild(editIdInput);
            document.body.appendChild(form);
            form.submit();
        }

        function cancelEdit() {
            location.reload();
        }

        function deleteRow(patientId) {
            if (confirm("Are you sure you want to delete this patient?")) {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "manage_patient.php";
                var deleteIdInput = document.createElement("input");
                deleteIdInput.type = "hidden";
                deleteIdInput.name = "delete_id";
                deleteIdInput.value = patientId;
                form.appendChild(deleteIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openAddpatientForm() {
            console.log("Opening add patient form...");
            document.getElementById("addpatientForm").style.display = "block";
        }

        function closeAddpatientForm() {
            console.log("Closing add patient form...");
            document.getElementById("addpatientForm").style.display = "none";
        }
    </script>
</head>

<body>
    <main>
        <h1>Manage Patient</h1>
        <div>
            <div class="search-container">
                <form action="manage_patient.php" method="get">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <input type="submit" value="Search">
                </form>
                <form action="manage_patient.php" method="get">
                    <button type="submit">Reset</button>
                </form>
                <!-- Add Patient button with lock_status check -->
                <button type="button" onclick="openAddpatientForm()"
                    <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot add patients."'; ?>>
                    <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Add Patient'; ?>
                </button>
            </div>
            <?php if ($_SESSION['lock_status'] === 'Yes'): ?>
                    <p style="color: red; font-weight: bold;">Your account is locked and cannot perform any actions.</p>
                <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th><a href="<?= sortOrder('patient_id'); ?>">Patient ID</a></th>
                    <th><a href="<?= sortOrder('username'); ?>">Username</a></th>
                    <th><a href="<?= sortOrder('email'); ?>">Email</a></th>
                    <th><a href="<?= sortOrder('gender'); ?>">Gender</a></th>
                    <th><a href="<?= sortOrder('first_name'); ?>">First Name</a></th>
                    <th><a href="<?= sortOrder('last_name'); ?>">Last Name</a></th>
                    <th><a href="<?= sortOrder('dob'); ?>">Date of Birth</a></th>
                    <th><a href="<?= sortOrder('contact_num'); ?>">Contact Number</a></th>
                    <th><a href="<?= sortOrder('preferred_communication'); ?>">Preferred Communication</a></th>
                    <th><a href="<?= sortOrder('class'); ?>">Class</a></th>
                    <th><a href="<?= sortOrder('survey_status'); ?>">Survey status</a></th>
                    <th><a href="<?= sortOrder('lastlogin'); ?>">Last Login</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr id='row-" . $row["patient_id"] . "'>";
                        echo "<td>" . $row["patient_id"] . "</td>";
                        echo "<td data-field='username'>" . $row["username"] . "</td>";
                        echo "<td data-field='email'>" . $row["email"] . "</td>";
                        echo "<td data-field='gender'>" . $row["gender"] . "</td>";
                        echo "<td data-field='first_name'>" . $row["first_name"] . "</td>";
                        echo "<td data-field='last_name'>" . $row["last_name"] . "</td>";
                        echo "<td data-field='dob'>" . $row["dob"] . "</td>";
                        echo "<td data-field='contact_num'>" . $row["contact_num"] . "</td>";
                        echo "<td data-field='preferred_communication'>" . $row["preferred_communication"] . "</td>";
                        echo "<td data-field='class'>" . $row["class"] . "</td>";
                        echo "<td data-field='survey_status'>" . $row["survey_status"] . "</td>";
                        echo "<td data-field='lastlogin'>" . $row["lastlogin"] . "</td>";
                        echo "<td id='actions-" . $row["patient_id"] . "' class='actions'>";

                        // Check if lock_status is 'Yes' and set disabled attribute accordingly
                        $disabledAttr = ($_SESSION['lock_status'] === 'Yes') ? 'disabled title="Your account is locked and cannot perform this action."' : '';

                        // Display Edit button with appropriate attributes
                        echo "<button type='button' onclick='editRow(" . $row["patient_id"] . ")' $disabledAttr>";
                        echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Edit';
                        echo "</button>";
                        echo "<br><br>";

                        // Display Delete button with appropriate attributes
                        echo "<button type='button' onclick='deleteRow(" . $row["patient_id"] . ")' $disabledAttr>";
                        echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Delete';
                        echo "</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='13'>No Results Found</td>";  // Updated colspan to 13 to match the number of columns
                }
                ?>
            </tbody>
        </table>
        <div id="addpatientForm">
            <h2>Add Patient</h2>
            <form id="addpatient" action="manage_patient.php" method="post">
                <label for="new_username">Username:</label><br>
                <input type="text" id="new_username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                <span style="color: red;"><?= htmlspecialchars($username_err ?? '') ?></span><br>


                <label for="new_password">Password:</label><br>
                <input type="password" id="new_password" name="password" required>
                <span style="color: red;"><?= $password_err ?? '' ?></span><br>

                <label for="new_confirm_password">Confirm Password:</label><br>
                <input type="password" id="new_confirm_password" name="confirm_password" required>
                <span style="color: red;"><?= $confirm_password_err ?? '' ?></span><br>

                <label for="new_email">Email:</label><br>
                <input type="email" id="new_email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <span style="color: red;"><?= $email_err ?? '' ?></span><br>

                <label for="new_gender">Gender:</label><br>
                <select id="new_gender" name="gender" required>
                    <option value="Male" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
                </select>
                <span style="color: red;"><?= $gender_err ?? '' ?></span><br><br>

                <label for="new_first_name">First Name:</label><br>
                <input type="text" id="new_first_name" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                <span style="color: red;"><?= $first_name_err ?? '' ?></span><br>

                <label for="new_last_name">Last Name:</label><br>
                <input type="text" id="new_last_name" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                <span style="color: red;"><?= $last_name_err ?? '' ?></span><br>

                <label for="new_dob">Date of Birth:</label><br>
                <input type="date" id="new_dob" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>
                <span style="color: red;"><?= $dob_err ?? '' ?></span><br>

                <label for="new_contact_num">Contact Number:</label><br>
                <div style="display: flex;">
                    <select name="area_code" style="flex: 0 0 110px;" required>
                        <!-- Options for area codes -->
                        <option value="+60" <?= (isset($_POST['area_code']) && $_POST['area_code'] == '+60') ? 'selected' : '' ?>>+60 (Malaysia)</option>
                        <!-- Add other options here -->
                        <option value="+1">+1 (USA, Canada)</option>
                        <option value="+44">+44 (UK)</option>
                        <option value="+61">+61 (Australia)</option>
                        <option value="+91">+91 (India)</option>
                        <option value="+81">+81 (Japan)</option>
                        <option value="+49">+49 (Germany)</option>
                        <option value="+33">+33 (France)</option>
                        <option value="+86">+86 (China)</option>
                        <option value="+55">+55 (Brazil)</option>
                        <option value="+7">+7 (Russia)</option>
                        <option value="+39">+39 (Italy)</option>
                        <option value="+34">+34 (Spain)</option>
                        <option value="+52">+52 (Mexico)</option>
                        <option value="+27">+27 (South Africa)</option>
                        <option value="+82">+82 (South Korea)</option>
                        <option value="+62">+62 (Indonesia)</option>
                        <option value="+31">+31 (Netherlands)</option>
                        <option value="+47">+47 (Norway)</option>
                        <option value="+46">+46 (Sweden)</option>
                        <option value="+41">+41 (Switzerland)</option>
                        <option value="+65">+65 (Singapore)</option>
                        <option value="+63">+63 (Philippines)</option>
                        <option value="+64">+64 (New Zealand)</option>
                        <option value="+20">+20 (Egypt)</option>
                        <option value="+51">+51 (Peru)</option>
                        <option value="+54">+54 (Argentina)</option>
                        <option value="+56">+56 (Chile)</option>
                        <option value="+32">+32 (Belgium)</option>
                        <option value="+48">+48 (Poland)</option>
                        <option value="+30">+30 (Greece)</option>
                        <option value="+36">+36 (Hungary)</option>
                        <option value="+234">+234 (Nigeria)</option>
                        <option value="+254">+254 (Kenya)</option>
                        <option value="+378">+378 (San Marino)</option>
                        <option value="+421">+421 (Slovakia)</option>
                        <option value="+386">+386 (Slovenia)</option>
                        <option value="+66">+66 (Thailand)</option>
                        <option value="+672">+672 (Australian External Territories)</option>
                        <option value="+677">+677 (Solomon Islands)</option>
                        <option value="+686">+686 (Kiribati)</option>
                        <option value="+689">+689 (French Polynesia)</option>
                        <option value="+690">+690 (Tokelau)</option>
                        <option value="+691">+691 (Micronesia)</option>
                        <option value="+692">+692 (Marshall Islands)</option>
                    </select>
                    <input type="text" name="contact_num" class="input-phone-number" value="<?= htmlspecialchars($_POST['contact_num'] ?? '') ?>" required style="flex: 1; margin-left: 10px;">
                </div>
                <span style="color: red;"><?= $contact_num_err ?? '' ?></span><br>

                <label for="preferred_communication">Preferred Method of Communication:</label><br>
                <select name="preferred_communication" required>
                    <option value="email" <?= (isset($_POST['preferred_communication']) && $_POST['preferred_communication'] == 'email') ? 'selected' : '' ?>>Email</option>
                    <option value="phone" <?= (isset($_POST['preferred_communication']) && $_POST['preferred_communication'] == 'phone') ? 'selected' : '' ?>>Phone</option>
                    <option value="sms" <?= (isset($_POST['preferred_communication']) && $_POST['preferred_communication'] == 'sms') ? 'selected' : '' ?>>SMS</option>
                </select>
                <span style="color: red;"><?= $preferred_communication_err ?? '' ?></span><br><br>

                <label for="preferred_class">Preferred Payment:</label><br>
                <select name="preferred_class" required>
                    <option value="class1" <?= (isset($_POST['preferred_class']) && $_POST['preferred_class'] == 'class1') ? 'selected' : '' ?>>Class 1 - Pay by cash</option>
                    <option value="class2" <?= (isset($_POST['preferred_class']) && $_POST['preferred_class'] == 'class2') ? 'selected' : '' ?>>Class 2 - Pay by insurance</option>
                    <option value="class3" <?= (isset($_POST['preferred_class']) && $_POST['preferred_class'] == 'class3') ? 'selected' : '' ?>>Class 3</option>
                    <option value="class4" <?= (isset($_POST['preferred_class']) && $_POST['preferred_class'] == 'class4') ? 'selected' : '' ?>>Class 4</option>
                </select>
                <span style="color: red;"><?= $preferred_class_err ?? '' ?></span><br><br>

                <label for="survey_status">Survey Status:</label>
                <select name="survey_status" id="survey_status">
                    <option value="No" <?= (isset($_POST['survey_status']) && $_POST['survey_status'] == 'No') ? 'selected' : '' ?>>No</option>
                    <option value="Yes" <?= (isset($_POST['survey_status']) && $_POST['survey_status'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                </select><br><br>

                <button type="submit">Add Patient</button>
                <button type="button" onclick="closeAddpatientForm()">Cancel</button>
            </form>
        </div>

        <h2>Recent Log Entries</h2>
        <table>
            <thead>
                <tr>
                    <th>Change Time</th>
                    <th>Action</th>
                    <th>Patient ID</th>
                    <th>Changed By</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch the latest 10 logs
                $logQuery = "SELECT * FROM patient_changes_log ORDER BY change_time DESC LIMIT 10";
                $logResult = $conn->query($logQuery);

                if ($logResult === false) {
                    // Handle query error
                    echo "<tr><td colspan='5'>Error fetching logs: " . $conn->error . "</td></tr>";
                } elseif ($logResult->num_rows > 0) {
                    while ($logRow = $logResult->fetch_assoc()) {
                        $changes = '';

                        if (!empty($logRow['old_values']) && !empty($logRow['new_values'])) {
                            $oldValues = json_decode($logRow['old_values'], true);
                            $newValues = json_decode($logRow['new_values'], true);

                            foreach ($oldValues as $key => $oldValue) {
                                if (isset($newValues[$key]) && $newValues[$key] != $oldValue) {
                                    $changes .= htmlspecialchars($key) . ': ' . htmlspecialchars($oldValue) . ' -> ' . htmlspecialchars($newValues[$key]) . '<br>';
                                }
                            }
                        }

                        // Format change_time for readability
                        $formattedChangeTime = date('Y-m-d H:i:s', strtotime($logRow['change_time']));

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($formattedChangeTime) . "</td>";
                        echo "<td>" . htmlspecialchars($logRow['change_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($logRow['patient_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($logRow['changed_by']) . "</td>";
                        echo "<td>" . $changes . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No log entries found.</td></tr>";
                }
                ?>

            </tbody>
        </table>
        <br><br>
        
    </main>
    
</body>
<?php include 'includes/footer.php'; ?>
</html>