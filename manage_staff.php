<?php
ob_start(); // Start output buffering
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'];

// Check if the user is logged in and has the admin role, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["role"] !== 'admin' && $_SESSION["role"] !== 'hr' && $_SESSION["role"] !== 'superadmin')) {
    header("location: login.php");
    exit;
}

// Include database connection settings
require_once 'includes/settings.php';

// Include different headers based on role
if ($role === 'admin') {
    include 'includes/header_admin.php';
} elseif ($role === 'hr') {
    include 'includes/header_staff.php';
} elseif ($role === 'superadmin') {
    include 'includes/header_superadmin.php';
}

$id = $_SESSION["id"];
// Initialize notifications array
if (!isset($_SESSION['notifications'])) {
    $_SESSION['notifications'] = [];
}

// Ensure lock_status is set for current user session
$lock_status = $_SESSION['lock_status'] ?? 'No';

// Check lock status for restriction on add/edit actions
$lock_status = false;
$sql = "SELECT lock_status FROM staffs WHERE staff_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($lock_status_result);
$stmt->fetch();
$lock_status = ($lock_status_result === 'Yes');
$stmt->close();

// Restrict actions if lock_status is Yes
if ($lock_status && $role !== 'superadmin') {
    echo "<script>alert('You are restricted from editing or adding staff due to your lock status.');</script>";
}

function log_staff_change($id, $change_type, $changed_by, $message, $old_values = null, $new_values = null)
{
    global $conn;

    // Insert into staff_changes_log
    $stmt = $conn->prepare("INSERT INTO staff_changes_log (staff_id, change_type, changed_by, old_values, new_values) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $id, $change_type, $changed_by, $old_values, $new_values);
    $stmt->execute();
    $log_id = $stmt->insert_id; // Get the last inserted log_id
    $stmt->close();

    // Prepare SQL statement to insert into admin_notifications
    $stmt = $conn->prepare("INSERT INTO admin_notifications (admin_id, change_type, changed_by, log_id, notification_time, status, message) VALUES (?, ?, ?, ?, NOW(), 'Unread', ?)");

    // Bind parameters: staff_id (integer), change_type (string), changed_by (string), log_id (integer), message (string)
    $stmt->bind_param("issis", $id, $change_type, $changed_by, $log_id, $message);

    // Execute the statement
    $stmt->execute();

    // Close the statement
    $stmt->close();
}


// Define sort order and column
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$column = isset($_GET['column']) ? $_GET['column'] : 'staff_id';

// Define search term
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Handle edit and delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$lock_status) {
    if (isset($_POST['edit_id'])) {
        // Fetch old values before updating
        $old_values = [];
        $sql = "SELECT * FROM staffs WHERE staff_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_POST['edit_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $old_values = $result->fetch_assoc();
        }
        $stmt->close();

        // Update the staff record, including lock_status if superadmin
        $update_sql = "UPDATE staffs SET username=?, email=?, gender=?, first_name=?, last_name=?, dob=?, contact_num=?, role=?";
        if ($role === 'superadmin') {
            $update_sql .= ", lock_status=?";
        }
        $update_sql .= " WHERE staff_id=?";

        if ($role === 'superadmin') {
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sssssssssi", $_POST['username'], $_POST['email'], $_POST['gender'], $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['contact_num'], $_POST['role'], $_POST['lock_status'], $_POST['edit_id']);
        } else {
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssssssssi", $_POST['username'], $_POST['email'], $_POST['gender'], $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $_POST['contact_num'], $_POST['role'], $_POST['edit_id']);
        }

        $stmt->execute();
        $stmt->close();

        $message1 = "Staff record updated!";

        // Log the change
        log_staff_change($id, 'Updated!', $_SESSION['username'], $message1, json_encode($old_values), json_encode($_POST));

        // Add notification
        $_SESSION['notifications'][] = $_SESSION['username'] . " edited staff with ID: " . $_POST['edit_id'];
    } elseif (isset($_POST['delete_id'])) {
        // Fetch old values before deleting
        $old_values = [];
        $sql = "SELECT * FROM staffs WHERE staff_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_POST['delete_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $old_values = $result->fetch_assoc();
        }
        $stmt->close();

        // Delete the staff record
        $stmt = $conn->prepare("DELETE FROM staffs WHERE staff_id=?");
        $stmt->bind_param("i", $_POST['delete_id']);
        $stmt->execute();
        $stmt->close();

        $message2 = "Staff record has been deleted!";

        // Log the change
        log_staff_change($id, 'Deleted!', $_SESSION['username'], $message2, json_encode($old_values));

        // Add notification
        $_SESSION['notifications'][] = $_SESSION['username'] . " deleted staff with ID: " . $_POST['delete_id'];
    }
}


// Handle add staff operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["username"]) && !empty($_POST["password"])) {
    $errors = [];

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $errors[] = "Please enter a username.";
    } else {
        // Prepare a select statement to check if username exists
        $sql = "SELECT staff_id FROM staffs WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $errors[] = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                $errors[] = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $errors[] = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate gender
    if (empty(trim($_POST["gender"]))) {
        $errors[] = "Please select your gender.";
    } else {
        $gender = trim($_POST["gender"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $errors[] = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $errors[] = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $errors[] = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $errors[] = "Password did not match.";
        }
    }

    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $errors[] = "Please enter your first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $errors[] = "Please enter your last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate date of birth
    if (empty(trim($_POST["dob"]))) {
        $errors[] = "Please enter your date of birth.";
    } else {
        $dob = trim($_POST["dob"]);
    }

    // Validate contact number (optional)
    $contact_num = trim($_POST["contact_num"]);

    // Check if there are errors
    if (!empty($errors)) {
        // Convert errors array to a single string message
        $error_message = implode("\\n", $errors);
        echo "<script>
                alert('$error_message');
                window.history.back();
              </script>";
        exit;
    }

    // Proceed with registration if no errors
    $sql = "INSERT INTO staffs (username, password, email, gender, first_name, last_name, dob, contact_num, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssssss", $param_username, $param_password, $param_email, $param_gender, $param_first_name, $param_last_name, $param_dob, $param_contact_num, $param_role);
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT); // Hashed password
        $param_email = $email;
        $param_gender = $_POST["gender"];
        $param_first_name = $first_name;
        $param_last_name = $last_name;
        $param_dob = $dob;
        $param_contact_num = $contact_num;
        $param_role = $_POST["role"];

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Log the new insertion
            $message3 = "New Staff has been added!";
            $new_staff_id = $stmt->insert_id;
            log_staff_change($id, 'Inserted!', $_SESSION['username'], $message3, json_encode($_POST), null);
            // Add notification
            $_SESSION['notifications'][] = $_SESSION['username'] . " added new staff";

            // Redirect to manage_staff page after successful addition
            echo "<script>
                    alert('Staff added successfully!');
                    window.location.href='manage_staff.php';
                  </script>";
            exit();
        } else {
            echo "<script>alert('Oops! Something went wrong. Please try again later.');</script>";
        }
        $stmt->close();
    }

    // Redirect to avoid form resubmission
    header("Location: manage_staff.php");
    exit;
}

// Fetch staff data, including lock_status
$sql = "SELECT staff_id, username, email, gender, first_name, last_name, dob, contact_num, role, lock_status, lastlogin  
        FROM staffs 
        WHERE (username LIKE '%$search%' 
               OR email LIKE '%$search%' 
               OR first_name LIKE '%$search%' 
               OR last_name LIKE '%$search%' 
               OR role LIKE '%$search%')
          AND role != 'superadmin'
        ORDER BY $column $order";


$result = $conn->query($sql);

// Function to toggle sort order
function sortOrder($column)
{
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    $order = ($order === 'ASC') ? 'DESC' : 'ASC';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    return "?column=$column&order=$order&search=$search";
}

// End of file, flush output buffer
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Physiowerkz</title>
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
            transition: background-color 0.3s ease;
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

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
                gap: 15px;
            }

            .search-container input[type="text"] {
                width: 100%;
            }
        }

        /* Add Staff Form Styling */
        #addStaffForm {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 30px;
            border: 1px solid #bdc3c7;
            background-color: #fff;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-height: 80vh;
            width: 400px;
            overflow-y: auto;
        }

        #addStaffForm h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #2c3e50;
            text-align: center;
        }

        /* Add Staff Form Inputs */
        #addStaffForm input[type="text"],
        #addStaffForm select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #bdc3c7;
        }

        /* Add Staff Form Submit Button */
        #addStaffForm input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #addStaffForm input[type="submit"]:hover {
            background-color: #2980b9;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background-color: #fff;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
            word-wrap: break-word;
            font-size: 14px;
        }

        th {
            cursor: pointer;
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        th:hover {
            background-color: #2980b9;
        }

        /* Form Group Styling */
        .form-group input[type="text"],
        .form-group select {
            margin-right: 10px;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Action Buttons Styling */
        .actions button {
            margin-right: 5px;
            padding: 8px 15px;
            background-color: #e67e22;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .actions button:hover {
            background-color: #d35400;
            transform: scale(1.05);
        }

        /* Role Select Styling */
        .role-select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #bdc3c7;
            font-size: 14px;
            background-color: #fff;
        }

        /* Add Staff Form Submit Button */
        #addStaffForm input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #addStaffForm input[type="submit"]:hover {
            background-color: #2980b9;
        }
    </style>
    <script>
        function editRow(staffId) {
            var row = document.getElementById("row-" + staffId);
            var cells = row.getElementsByTagName("td");
            for (var i = 1; i < cells.length - 1; i++) {
                var cell = cells[i];

                if (cell.dataset.field === 'lastlogin') {
                    continue;
                }

                if (cell.dataset.field === 'role' || cell.dataset.field === 'gender' || (cell.dataset.field === 'lock_status' && <?= json_encode($role === 'superadmin'); ?>)) {
                    var select = document.createElement("select");
                    select.name = cell.dataset.field;
                    select.className = cell.dataset.field + '-select';
                    var options;

                    if (cell.dataset.field === 'role') {
                        options = ['admin', 'hr', 'physiotherapist', 'trainer'];
                    } else if (cell.dataset.field === 'gender') {
                        options = ['Male', 'Female'];
                    } else if (cell.dataset.field === 'lock_status') {
                        options = ['No', 'Yes'];
                    }

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
                    var input = document.createElement("input");
                    input.type = "text";
                    input.value = cell.innerText;
                    input.name = cell.dataset.field;
                    input.className = "edit-input";
                    cell.innerText = '';
                    cell.appendChild(input);
                }
            }
            var actionsCell = document.getElementById("actions-" + staffId);
            actionsCell.innerHTML = '<button type="button" onclick="saveRow(' + staffId + ')">Save</button><button type="button" onclick="cancelEdit()">Cancel</button>';
        }

        function saveRow(staffId) {
            var row = document.getElementById("row-" + staffId);
            var inputs = row.getElementsByTagName("input");
            var selects = row.getElementsByTagName("select");
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "manage_staff.php";
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
            editIdInput.value = staffId;
            form.appendChild(editIdInput);
            document.body.appendChild(form);
            form.submit();
        }

        function cancelEdit() {
            location.reload();
        }

        function deleteRow(staffId) {
            if (confirm("Are you sure you want to delete this staff member?")) {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "manage_staff.php";
                var deleteIdInput = document.createElement("input");
                deleteIdInput.type = "hidden";
                deleteIdInput.name = "delete_id";
                deleteIdInput.value = staffId;
                form.appendChild(deleteIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openAddStaffForm() {
            document.getElementById("addStaffForm").style.display = "block";
        }

        function closeAddStaffForm() {
            document.getElementById("addStaffForm").style.display = "none";
        }
    </script>
</head>

<body>
    <main>
        <h1>Manage Staff</h1>
        <div>
            <div class="search-container">
                <form action="manage_staff.php" method="get">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <input type="submit" value="Search">
                </form>
                <form action="manage_staff.php" method="get">
                    <button type="submit">Reset</button>
                </form>
                <?php if ($_SESSION['lock_status'] !== 'Yes') : ?>
                    <button type="button" onclick="openAddStaffForm()">Add Staff</button>
                <?php else : ?>
                    <button type="button" onclick="openAddStaffForm()" disabled title="Your account is locked and cannot add staff.">Account Locked</button>
                    <p style="color: red; font-weight: bold;">Your account is locked and cannot perform any actions.</p>
                <?php endif; ?>

            </div>

        </div>
        <table>
            <thead>
                <tr>
                    <th><a href="<?= sortOrder('staff_id'); ?>">Staff ID</a></th>
                    <th><a href="<?= sortOrder('username'); ?>">Username</a></th>
                    <th><a href="<?= sortOrder('email'); ?>">Email</a></th>
                    <th><a href="<?= sortOrder('gender'); ?>">Gender</a></th>
                    <th><a href="<?= sortOrder('first_name'); ?>">First Name</a></th>
                    <th><a href="<?= sortOrder('last_name'); ?>">Last Name</a></th>
                    <th><a href="<?= sortOrder('dob'); ?>">Date of Birth</a></th>
                    <th><a href="<?= sortOrder('contact_num'); ?>">Contact Number</a></th>
                    <th><a href="<?= sortOrder('role'); ?>">Role</a></th>
                    <?php if ($role === 'superadmin') : ?>
                        <th><a href="<?= sortOrder('lock_status'); ?>">Lock Status</a></th>
                    <?php endif; ?>
                    <th><a href="<?= sortOrder('lastlogin'); ?>">Last Login</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr id='row-" . $row["staff_id"] . "'>";
                        echo "<td>" . $row["staff_id"] . "</td>";
                        echo "<td data-field='username'>" . $row["username"] . "</td>";
                        echo "<td data-field='email'>" . $row["email"] . "</td>";
                        echo "<td data-field='gender'>" . $row["gender"] . "</td>";
                        echo "<td data-field='first_name'>" . $row["first_name"] . "</td>";
                        echo "<td data-field='last_name'>" . $row["last_name"] . "</td>";
                        echo "<td data-field='dob'>" . $row["dob"] . "</td>";
                        echo "<td data-field='contact_num'>" . $row["contact_num"] . "</td>";
                        echo "<td data-field='role'>" . $row["role"] . "</td>";

                        // Display lock_status only for superadmin
                        if ($role === 'superadmin') {
                            echo "<td data-field='lock_status'>" . $row["lock_status"] . "</td>";
                        }

                        echo "<td data-field='lastlogin'>" . $row["lastlogin"] . "</td>";

                        echo "<td id='actions-" . $row["staff_id"] . "' class='actions'>";

                        // Check if lock_status is 'Yes' and set disabled attribute accordingly
                        $disabledAttr = ($_SESSION['lock_status'] === 'Yes') ? 'disabled title="Your account is locked and cannot perform this action."' : '';

                        // Display Edit button with appropriate attributes
                        echo "<button type='button' onclick='editRow(" . $row["staff_id"] . ")' $disabledAttr>";
                        echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Edit';
                        echo "</button>";
                        echo "<br><br>";

                        // Display Delete button with appropriate attributes
                        echo "<button type='button' onclick='deleteRow(" . $row["staff_id"] . ")' $disabledAttr>";
                        echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Delete';
                        echo "</button>";

                        echo "</td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>No Results Found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div id="addStaffForm">
            <h2>Add Staff</h2>
            <form id="addStaff" action="manage_staff.php" method="post">
                <label for="new_username">Username:</label>
                <input type="text" id="new_username" name="username" required>

                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="password" required><br><br>

                <label for="new_confirm_password">Confirm Password:</label>
                <input type="password" id="new_confirm_password" name="confirm_password" required><br><br>

                <label for="new_email">Email:</label>
                <input type="email" id="new_email" name="email" required><br><br>

                <label for="new_gender">Gender:</label>
                <select id="new_gender" name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="other">Other</option>
                </select>

                <label for="new_first_name">First Name:</label>
                <input type="text" id="new_first_name" name="first_name" required>

                <label for="new_last_name">Last Name:</label>
                <input type="text" id="new_last_name" name="last_name" required>

                <label for="new_dob">Date of Birth:</label>
                <input type="date" id="new_dob" name="dob" required><br><br>

                <label for="new_contact_num">Contact Number:</label>
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

                <label for="new_role">Role:</label>
                <select id="new_role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="hr">HR</option>
                    <option value="physiotherapist">Physiotherapist</option>
                    <option value="trainer">Trainer</option>
                </select>

                <button type="submit">Add Staff</button>
                <button type="button" onclick="closeAddStaffForm()">Cancel</button>
            </form>
        </div>

        <h2>Recent Log Entries</h2>
        <table>
            <thead>
                <tr>
                    <th>Change Time</th>
                    <th>Action</th>
                    <th>Staff ID</th>
                    <th>Changed By</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $logQuery = "SELECT * FROM staff_changes_log ORDER BY change_time DESC LIMIT 10";
                $logResult = $conn->query($logQuery);

                if ($logResult->num_rows > 0) {
                    while ($logRow = $logResult->fetch_assoc()) {
                        $changes = '';

                        if (!empty($logRow['old_values']) && !empty($logRow['new_values'])) {
                            $oldValues = json_decode($logRow['old_values'], true);
                            $newValues = json_decode($logRow['new_values'], true);

                            foreach ($oldValues as $key => $oldValue) {
                                if (isset($newValues[$key]) && $newValues[$key] != $oldValue) {
                                    $changes .= $key . ': ' . htmlspecialchars($oldValue) . ' -> ' . htmlspecialchars($newValues[$key]) . '<br>';
                                }
                            }
                        }

                        echo "<tr>";
                        echo "<td>" . $logRow['change_time'] . "</td>";
                        echo "<td>" . $logRow['change_type'] . "</td>";
                        echo "<td>" . $logRow['staff_id'] . "</td>";
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

    </main>
    <?php include 'includes/footer.php'; ?>
</body>
<script>
    window.onload = function() {
        // Check if there are errors from the server
        <?php if (isset($_SESSION['form_errors'])): ?>
            let errors = <?php echo json_encode($_SESSION['form_errors']); ?>;
            let errorMsg = "";
            for (let key in errors) {
                if (errors[key]) {
                    errorMsg += errors[key] + "\\n";
                }
            }
            if (errorMsg) {
                alert(errorMsg); // Show the error messages in a popup
            }
            <?php unset($_SESSION['form_errors']); // Clear errors after showing 
            ?>
        <?php endif; ?>
    }
</script>

</html>