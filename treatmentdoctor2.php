<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the patient ID is passed via URL or form, or retrieve from session
if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    $_SESSION['patient_id'] = $patient_id;  // Store it in session for future use
} elseif (isset($_POST['patient_id'])) {
    $patient_id = $_POST['patient_id'];
    $_SESSION['patient_id'] = $patient_id;
} elseif (isset($_SESSION['patient_id'])) {
    $patient_id = $_SESSION['patient_id'];  // Retrieve from session if available
} else {
    // If no patient_id is available, handle the error (e.g., redirect or show error message)
}

// Check if the appointment ID is passed via URL or form, or retrieve from session
if (isset($_GET['appointment_id'])) {
    $appointment_id = $_GET['appointment_id'];
    $_SESSION['appointment_id'] = $appointment_id;  // Store it in session for future use
} elseif (isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
    $_SESSION['appointment_id'] = $appointment_id;
} elseif (isset($_SESSION['appointment_id'])) {
    $appointment_id = $_SESSION['appointment_id'];  // Retrieve from session if available
} else {
}

// Define roles array for flexibility
$valid_roles = ['admin', 'physiotherapist'];
$role = $_SESSION['role'] ?? '';
// Check if the user is logged in and has the appropriate role, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($role, $valid_roles)) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

// Include different headers based on role
if ($role === 'admin') {
    include 'includes/header_admin.php';
} elseif ($role === 'physiotherapist') {
    include 'includes/header_staff.php';
}

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Retrieve physiotherapist ID based on session username
if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    $sql_therapistid = "SELECT staff_id FROM staffs WHERE username = ?";
    $stmt_therapist = $conn->prepare($sql_therapistid);
    $stmt_therapist->bind_param("s", $username);
    $stmt_therapist->execute();
    $result = $stmt_therapist->get_result();
    $row = $result->fetch_assoc();
    $staff_id = $row['staff_id'] ?? null;
    $stmt_therapist->close();
}
?>
<?php
if (isset($_POST['save_assessment'])) {
    $appointment_id = $_POST['appointment_id'];
    $patient_id = $_POST['patient_id'];
    $assessments = $_POST['assessment'];
    $success = false;  // To track if insertion is successful

    // 1. Delete all existing records for this patient
    $delete_query = "DELETE FROM assessment_findings WHERE appointment_id =? AND patient_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $appointment_id, $patient_id);

    if ($delete_stmt->execute()) {
        $delete_stmt->close();
        $success = true;  // Mark success if deletion was successful

        // 2. Insert the new assessment data if any assessments are provided
        if (!empty($assessments)) {
            foreach ($assessments as $assessment) {
                $body_part = $assessment['body_part'];
                $condition_type = $assessment['condition_type'];
                $severity = $assessment['severity'];
                $remarks = $assessment['condition_text'];

                // Insert new record
                $insert_query = "INSERT INTO assessment_findings (appointment_id, patient_id, body_part, condition_type, severity, remarks) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("iissss", $appointment_id, $patient_id, $body_part, $condition_type, $severity, $remarks);

                if ($insert_stmt->execute()) {
                    $success = true;  // Update success if insertion works
                }
                $insert_stmt->close();
            }
        }
    } else {
        // Handle error during deletion
        $delete_stmt->close();
        // Optionally, set an error message here
    }
    // If successful, show alert and redirect
    if ($success) {
        echo "<script>
                alert('Assessment saved successfully!');
                window.location.href = 'view_treatment.php';
              </script>";
    } else {
        echo "<script>
                alert('Failed to save assessment. Please try again.');
              </script>";
    }
}

// Ensure lock_status is set for current user session
$lock_status = $_SESSION['lock_status'] ?? 'No';

if ($lock_status === 'Yes') {
    echo "<script>alert('You are restricted from edit or add assesment due to your lock status.');</script>";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Findings</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        form {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            line-height: 1.5;
        }

        .hidden-form {
            max-width: none;
            padding: 0;
            background-color: transparent;
            border: none;
            box-shadow: none;
        }

        .testtable {
            margin: 0 auto;
            max-width: 800px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .testtable h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .testtable table {
            width: 100%;
            margin-bottom: 20px;
        }

        .testtable th,
        .testtable td {
            padding: 12px;
            text-align: left;
        }

        .testtable th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .testtable ul {
            padding-left: 20px;
            list-style-type: disc;
        }

        .testtable td ul li {
            margin-bottom: 5px;
        }

        .testtable input[type="submit"],
        .testtable button {
            display: inline-block;
            margin: 10px auto;
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .testtable button:hover {
            background-color: #0056b3;
        }

        .testtable .btn-danger {
            background-color: #dc3545;
        }

        .testtable .btn-success {
            background-color: #28a745;
        }

        .testtable .btn-primary {
            background-color: #007bff;
        }

        .testtable .btn:hover {
            opacity: 0.9;
        }

        .assessment-test {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        .assessment-test form {
            width: 100%;
            max-width: 1500px;
        }

        .form-group-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-wrap: wrap;
        }

        .form-group>* {
            flex: 1 1 100%;
        }

        .form-group label {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .form-group select,
        .form-group textarea,
        .form-group input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #fff;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-group select:focus,
        .form-group textarea:focus,
        .form-group input:focus {
            border-color: #007BFF;
            background-color: #fff;
            outline: none;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }

        .form-group select,
        .form-group input {
            width: calc(100% - 20px);
        }

        button[type="submit"] {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        button[type="submit"]:focus {
            outline: none;
            box-shadow: 0 0 6px rgba(0, 123, 255, 0.5);
        }

        .assessment-group {
            border: 2px solid #f7f7f7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f7f7f7;
            position: relative;
        }

        .assessment-test {
            border: 2px solid #f7f7f7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f7f7f7;
            position: relative;
        }

        #add-activity-button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-danger {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 12px 18px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            margin-bottom: 20px;
            margin-left: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-danger:hover {
            background-color: #cc0000;
        }

        #remove-activity-button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #add-activity-button:hover {
            background-color: #218838;
        }

        #remove-activity-button:hover {
            background-color: #cc0000;
        }

        .remove-button-container {
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }

        .remove-button-container button {
            margin-top: 10px;
            background-color: #FF0000;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .form-group {
                flex: 1 1 100%;
            }

            button[type="submit"] {
                width: 100%;
            }

            #add-activity-button {
                width: 100%;
            }

            #remove-activity-button {
                width: 100%;
            }
        }

        .physiotherapist-tests {
            margin-top: 40px;
            border-top: 2px solid #007BFF;
            padding-top: 20px;
        }

        .test-section {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }

        .test-section h4 {
            margin-bottom: 10px;
            color: #007BFF;
        }

        .test-section label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .test-section select,
        .test-section textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .add-test-button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-test-button:hover {
            background-color: #218838;
        }

        .testform {
            max-width: 1300px;
        }
    </style>
</head>

<body>
    <br>
    <br>
    <br>

    <form method="POST" action="treatmentdoctor2.php">
        <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment_id ?? ''); ?>">
        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">
        <h2>Assessment Findings</h2>
        <h3>Standing Posture</h3>

        <?php
        $appointment_id = $_POST['appointment_id'];
        // Assume $patient_id is correctly set and retrieved (e.g., from session or URL)
        $patient_id = $_POST['patient_id'];

        // Fetch assessment findings from the database
        $assessment_findings_query = "SELECT * FROM assessment_findings WHERE appointment_id =? AND patient_id = ?";
        $stmt = $conn->prepare($assessment_findings_query);
        $stmt->bind_param("ii", $appointment_id, $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Store the fetched data in an array
        $assessment_findings = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $assessment_findings[] = $row;
            }
        }
        $stmt->close();
        ?>
        <div id="assessment-container">
            <!-- Initial body part assessment -->
            <?php if (!empty($assessment_findings)): ?>
                <?php foreach ($assessment_findings as $index => $finding): ?>
                    <div class="assessment-group form-group">
                        <label for="body_part_<?php echo $index; ?>">Body part:</label>
                        <select name="assessment[<?php echo $index; ?>][body_part]" id="body_part_<?php echo $index; ?>"
                            onchange="showConditionOptions(<?php echo $index; ?>)">
                            <option value="" disabled>Select a Body Part</option>
                            <option value="left_foot" <?php if ($finding['body_part'] == 'left_foot')
                                                            echo 'selected'; ?>>Left
                                Foot</option>
                            <option value="right_foot" <?php if ($finding['body_part'] == 'right_foot')
                                                            echo 'selected'; ?>>Right
                                Foot</option>
                            <option value="left_knee" <?php if ($finding['body_part'] == 'left_knee')
                                                            echo 'selected'; ?>>Left
                                Knee</option>
                            <option value="right_knee" <?php if ($finding['body_part'] == 'right_knee')
                                                            echo 'selected'; ?>>Right
                                Knee</option>
                            <option value="patellar" <?php if ($finding['body_part'] == 'patellar')
                                                            echo 'selected'; ?>>Patellar
                            </option>
                            <option value="hip" <?php if ($finding['body_part'] == 'hip')
                                                    echo 'selected'; ?>>Hip
                            </option>
                            <option value="pelvis" <?php if ($finding['body_part'] == 'pelvis')
                                                        echo 'selected'; ?>>Pelvis
                            </option>
                            <option value="lumbar_spine" <?php if ($finding['body_part'] == 'lumbar_spine')
                                                                echo 'selected'; ?>>Lumbar Spine
                            </option>
                            <option value="thoracic_spine" <?php if ($finding['body_part'] == 'thoracic_spine')
                                                                echo 'selected'; ?>>Thoracic Spine
                            </option>
                            <option value="shoulder" <?php if ($finding['body_part'] == 'shoulder')
                                                            echo 'selected'; ?>>Shoulder
                            </option>
                            <option value="neck" <?php if ($finding['body_part'] == 'neck')
                                                        echo 'selected'; ?>>Neck
                            </option>
                            <option value="other" <?php if ($finding['body_part'] == 'other')
                                                        echo 'selected'; ?>>Other
                            </option>
                            <!-- Add more options as needed -->
                        </select>
                        <div class="error-message" style="color:red; display:none;">Please fill out this field.</div>

                        <div id="condition_<?php echo $index; ?>" style="display: block;">
                            <label for="condition_type_<?php echo $index; ?>">Condition:</label>
                            <input type="text" name="assessment[<?php echo $index; ?>][condition_type]"
                                value="<?php echo htmlspecialchars($finding['condition_type']); ?>"
                                id="condition_type_<?php echo $index; ?>" required>

                            <label for="severity_<?php echo $index; ?>">Severity:</label>
                            <select name="assessment[<?php echo $index; ?>][severity]" id="severity_<?php echo $index; ?>"
                                required>
                                <option value="mild" <?php if ($finding['severity'] == 'mild')
                                                            echo 'selected'; ?>>Mild</option>
                                <option value="moderate" <?php if ($finding['severity'] == 'moderate')
                                                                echo 'selected'; ?>>
                                    Moderate</option>
                                <option value="severe" <?php if ($finding['severity'] == 'severe')
                                                            echo 'selected'; ?>>Severe
                                </option>
                            </select>

                            <label for="condition_text_<?php echo $index; ?>">Comments:</label>
                            <input type="text" name="assessment[<?php echo $index; ?>][condition_text]"
                                value="<?php echo htmlspecialchars($finding['remarks']); ?>"
                                id="condition_text_<?php echo $index; ?>" required>
                        </div>

                        <div class="remove-button-container">
                            <button type="button" onclick="removeBodyPart(this)">Remove</button>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Display empty fields for new assessments -->
                <div class="assessment-group form-group">
                    <p>No assessments found for this patient. Please add new assessments below.</p>
                    <!-- Add the same structure for new assessments here -->
                </div>
            <?php endif; ?>
        </div>
        <?php if ($_SESSION['lock_status'] === 'Yes') : ?>
            <!-- Change Add Body Part button to text -->
            <span style="color: red;">Add Body Part (Locked)</span><br><br>

            <!-- Change Save Assessment button to Back -->
            <button type="button" onclick="window.location.href='view_treatment.php'">Back</button>
        <?php else : ?>
            <!-- Button to add more body parts -->
            <button type="button" id="add-activity-button" onclick="addMoreBodyPart()">Add Body Part</button>

            <!-- Save Assessment Findings button -->
            <button type="submit" name="save_assessment">Save Assessment Findings</button>
        <?php endif; ?>

    </form>
    <script>
        function showConditionOptions(index) {
            const conditionDiv = document.getElementById(`condition_${index}`);
            conditionDiv.style.display = 'block';
        }

        function removeBodyPart(button) {
            const assessmentGroup = button.closest('.assessment-group');
            assessmentGroup.remove();
            toggleSaveButton();
        }

        let bodyPartCount = 2;

        function addMoreBodyPart() {
            bodyPartCount++;
            const assessmentContainer = document.getElementById('assessment-container');

            const newAssessmentGroup = document.createElement('div');
            newAssessmentGroup.className = 'assessment-group form-group';

            newAssessmentGroup.innerHTML = `
            <label for="body_part_${bodyPartCount}">Body part:</label>
            <select name="assessment[${bodyPartCount}][body_part]" id="body_part_${bodyPartCount}" required onchange="showConditionOptions(${bodyPartCount})">
                <option value="" disabled selected>Select a Body Part</option>
                <option value="left_foot">Left Foot</option>
                <option value="right_foot">Right Foot</option>
                <option value="left_knee">Left Knee</option>
                <option value="right_knee">Right Knee</option>
                <option value="patellar">Patellar</option>
                <option value="hip">Hip</option>
                <option value="pelvis">Pelvis</option>
                <option value="lumbar_spine">Lumbar Spine</option>
                <option value="thoracic_spine">Thoracic Spine</option>
                <option value="shoulder">Shoulder</option>
                <option value="neck">Neck</option>
                <option value="other">Other</option>
            </select>
            <div class="error-message" style="color:red; display:none;">Please fill out this field.</div>
            <div id="condition_${bodyPartCount}" style="display: none;">
                <label for="condition_type_${bodyPartCount}">Condition:</label>
                <input type="text" name="assessment[${bodyPartCount}][condition_type]" id="condition_type_${bodyPartCount}" placeholder="Condition (e.g., Collapsed, VARUS)" required>
                <label for="severity_${bodyPartCount}">Severity:</label>
                <select name="assessment[${bodyPartCount}][severity]" id="severity_${bodyPartCount}" required>
                    <option value="mild">Mild</option>
                    <option value="moderate">Moderate</option>
                    <option value="severe">Severe</option>
                </select>
                <label for="condition_text_${bodyPartCount}">Comments:</label>
                <input type="text" name="assessment[${bodyPartCount}][condition_text]" id="condition_text_${bodyPartCount}" placeholder="Additional comments..." required>
            </div>
            <div class="remove-button-container">
                <button type="button" id="remove-activity-button" onclick="removeBodyPart(this)">Remove</button>
            </div>
            <div class="error-message" style="color:red; display:none;">Please fill out this field.</div>
        `;
            assessmentContainer.appendChild(newAssessmentGroup);
            toggleSaveButton();
        }



        function validateForm() {
            const allFields = document.querySelectorAll('select[required], input[required]');
            let firstInvalidField = null;

            allFields.forEach((field) => {
                const errorMessage = field.parentElement.querySelector('.error-message');
                if (!field.value.trim()) {
                    errorMessage.style.display = 'block'; // Show the error message
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                } else {
                    errorMessage.style.display = 'none'; // Hide error message if valid
                }
            });

            // If there is an invalid field, scroll to it
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return false; // Prevent form submission
            }

            return true; // Allow form submission
        }

        document.querySelector('form').addEventListener('submit', function(event) {
            if (!validateForm()) {
                event.preventDefault(); // Stop form submission if validation fails
            }
        });

        document.addEventListener('input', toggleSaveButton); // Check inputs as user types

        document.addEventListener('DOMContentLoaded', function() {
            toggleSaveButton(); // Ensure save button state is checked on page load
        });
    </script>
    <?php
    // Fetch saved tests from the database
    $fetch_tests_query = "SELECT * FROM test_tracking WHERE appointment_id =? AND patient_id = ?";
    $stmt = $conn->prepare($fetch_tests_query);
    $stmt->bind_param("ii", $appointment_id, $patient_id);
    $stmt->execute();
    $saved_tests_result = $stmt->get_result();

    $saved_tests = [];
    if ($saved_tests_result->num_rows > 0) {
        while ($row = $saved_tests_result->fetch_assoc()) {
            $saved_tests[] = $row;
        }
    }

    // Handle form submission for removing test
    if (isset($_GET['remove_test_id'])) {
        $remove_test_id = $_GET['remove_test_id'];
        $delete_test_query = "DELETE FROM test_tracking WHERE id = ?";
        $stmt = $conn->prepare($delete_test_query);
        $stmt->bind_param("i", $remove_test_id);

        if ($stmt->execute()) {
            echo "<script>
                alert('Test removed successfully!');
                window.location.href = 'view_treatment.php';
              </script>";
        } else {
            echo "<script>
                alert('Failed to remove test.');
              </script>";
        }
        $stmt->close();
    }

    // Handle form submission for saving test results
    if (isset($_POST['save_test_tracking'])) {
        $patient_id = $_POST['patient_id'];  // Retrieve the patient ID from the form
        $appointment_id = $_POST['appointment_id'];
        $test_results = $_POST['test_result'];  // Array containing all the test result strings
        $success = false;  // To track if the insertion was successful

        // Insert the new test tracking results
        foreach ($test_results as $test_result) {
            if (!empty($test_result)) {  // Only insert non-empty test results
                $insert_query = "INSERT INTO test_tracking (appointment_id, patient_id, test_result) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iis", $appointment_id, $patient_id, $test_result);

                if ($stmt->execute()) {
                    $success = true;
                }
            }
        }
        $stmt->close();

        // If successful, show alert and redirect
        if ($success) {
            echo "<script>
                alert('Test results saved successfully!');
                window.location.href = 'view_treatment.php';
              </script>";
        } else {
            echo "<script>
                alert('Failed to save test results.');
              </script>";
        }
    }
    ?>

    <form class="testtable">
        <h2>Saved Tests</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Test Result</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($saved_tests)): ?>
                    <?php foreach ($saved_tests as $test): ?>
                        <tr>
                            <td>
                                <?php
                                // Get the test result and split into parts
                                $test_result = htmlspecialchars($test['test_result']);
                                $parts = explode(', ', $test_result);
                                ?>
                                <ul>
                                    <?php foreach ($parts as $part): ?>
                                        <li><?php echo $part; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <a href="treatmentdoctor2.php?remove_test_id=<?php echo $test['id']; ?>" class="btn btn-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No tests saved yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </form>
    <!-- Test Tracking Table -->
    <div class="assessment-test form-group">

        <form class="testtable" method="POST" action="treatmentdoctor2.php">
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment_id ?? ''); ?>">
            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">
            <h2>Add Tests</h2>
            <table id="testTable" class="table table-bordered">
                <tbody>
                </tbody>
            </table>
            <?php if ($_SESSION['lock_status'] === 'Yes') : ?>
                <!-- Change Add Test button to text -->
                <span style="color: red;">Add Test (Locked)</span><br><br>

                <!-- Disable Save button -->
                <button type="button" class="btn btn-secondary mt-2" disabled title="Your account is locked and cannot save.">Save</button>
            <?php else : ?>
                <!-- Button to add tests -->
                <button type="button" class="btn btn-success" id="add-activity-button" onclick="addTestRow()">Add Test</button>

                <!-- Save Test Tracking button -->
                <button type="submit" class="btn btn-primary mt-2" name="save_test_tracking">Save</button>
            <?php endif; ?>

        </form>
    </div>

    <div>
        <!-- Hidden Form -->
        <form id="navigationForm" class="hidden-form" method="POST" action="">
            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($_POST['patient_id']); ?>">
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($_POST['appointment_id']); ?>">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($_POST['username']); ?>">
        </form>

        <!-- Floating Buttons -->
        <div
            class="floating-button left"
            style="position: fixed; top: 50%; left: 10px; transform: translateY(-50%); cursor: pointer; z-index: 1000;"
            onclick="navigateToPage('treatmentdoctor1.php')">
            <img src="images/left-arrow.png" alt="Previous" style="width: 50px; height: 50px;">
        </div>

        <div
            class="floating-button right"
            style="position: fixed; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; z-index: 1000;"
            onclick="navigateToPage('treatmentdoctor3.php')">
            <img src="images/right-arrow.png" alt="Next" style="width: 50px; height: 50px;">
        </div>
    </div>
    <script>
        function addTestRow() {
            var table = document.getElementById("testTable").getElementsByTagName('tbody')[0];
            var newRow = table.insertRow();

            // First column (Test Name with dropdown)
            var cell1 = newRow.insertCell(0);
            var testNameLabel = document.createElement("label");
            testNameLabel.innerText = "Test Name:";
            cell1.appendChild(testNameLabel);
            var testName = document.createElement("select");
            testName.name = "test_name[]";
            testName.classList.add("form-control");

            // Default option
            var defaultOption = document.createElement("option");
            defaultOption.value = "";
            defaultOption.text = "Select Test";
            testName.appendChild(defaultOption);

            var tests = ["Single Leg Stand Stability *Right* / Left", "Single Leg Stand Stability Right / *Left*", "ASLR", "SLB", "LSP Rotation *Right* / Left", "LSP Rotation Right / *Left*", "TSP Rotation *Right* / Left", "TSP Rotation Right / *Left*", "Lats Crossover", "Swimmers", "Open Book", "Close Book", "Scaption", "PSLR", "Short Flexion (SF)", "Short Diagonal (SD)", "Long Diagonal (LD)", "Long Traction *Left* / Right *Hip* / Lumbar", "Long Traction *Left* / Right Hip / *Lumbar*", "Long Traction Left / *Right* *Hip* / Lumbar", "Long Traction *Left* / Right Hip / *Lumbar*", "90/90 Hip *Left* / Right *External Rot* / Internal Rot", "90/90 Hip *Left* / Right External Rot / *Internal Rot*", "90/90 Hip Left / *Right* *External Rot* / Internal Rot", "90/90 Hip Left / *Right* External Rot / *Internal Rot*", "SIJ Compression *Left* / Right *AP* / PA", "SIJ Compression *Left* / Right AP / *PA*", "SIJ Compression Left / *Right* *AP* / PA", "SIJ Compression Left / *Right* AP / *PA*", "Talocrural", "Subtalar", "Midfoot", "Toes", "Patello-femoral", "TibioFib Proximal", "Tibiofibular Distal", "Special Test"];
            for (var i = 0; i < tests.length; i++) {
                var option = document.createElement("option");
                option.value = tests[i];
                option.text = tests[i];
                testName.appendChild(option);
            }
            testName.onchange = function() {
                updateRow(newRow, testName.value);
            };
            cell1.appendChild(testName);

            // Other columns are initially empty but will be filled based on the selected test
            newRow.insertCell(1); // Column 2 (Trunk, Pelvis Control, etc.)
            newRow.insertCell(2); // Column 3 (Hip, etc.)
            newRow.insertCell(3); // Column 4 (Foot, Pain, etc.)
            newRow.insertCell(4); // Column 5 (Duration, etc.)
            var cell6 = newRow.insertCell(5); // Remarks
            var remarksLabel = document.createElement("label");
            remarksLabel.innerText = "Remarks:";
            cell6.appendChild(remarksLabel);
            var remarks = document.createElement("input");
            remarks.type = "text";
            remarks.name = "remarks[]";
            remarks.classList.add("form-control");
            cell6.appendChild(remarks);

            // Hidden field to store test result string
            var testResultInput = document.createElement("input");
            testResultInput.type = "hidden";
            testResultInput.name = "test_result[]";
            newRow.appendChild(testResultInput); // Hidden input for test_result

            // Action column with Remove button
            var cell7 = newRow.insertCell(6); // Action column
            var removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.classList.add("btn", "btn-danger");
            removeButton.innerText = "Remove";
            removeButton.onclick = function() {
                removeRow(newRow);
            };
            cell7.appendChild(removeButton);
        }

        function removeRow(row) {
            row.parentNode.removeChild(row);
        }

        // Function to dynamically update the row based on the selected test and generate the test_result string
        function updateRow(row, testName) {
            var col2 = row.cells[1]; // Column 2
            var col3 = row.cells[2]; // Column 3
            var col4 = row.cells[3]; // Column 4
            var col5 = row.cells[4]; // Column 5
            var remarks = row.cells[5].getElementsByTagName("input")[0]; // Remarks input
            var testResultInput = row.getElementsByTagName("input")[1]; // Hidden input for test_result

            // Clear the content of the cells first
            col2.innerHTML = '';
            col3.innerHTML = '';
            col4.innerHTML = '';
            col5.innerHTML = '';

            // Depending on the test, create the respective input fields and dropdowns
            if (testName.includes("Single Leg Stand Stability")) {
                // Column 2: Trunk
                col2.innerHTML = '<label>Trunk:</label><select name="trunk[]" class="form-control"><option>Normal</option><option>Lean Toward</option><option>Lean Away</option><option>Lean Back</option><option>Lean Forward</option></select>';
                // Column 3: Hip
                col3.innerHTML = '<label>Hip:</label><select name="hip[]" class="form-control"><option>Poor</option><option>Moderate</option><option>Good</option></select>';
                // Column 4: Foot
                col4.innerHTML = '<label>Foot:</label><select name="foot[]" class="form-control"><option>Poor</option><option>Moderate</option><option>Good</option></select>';
                // Column 5: Duration
                col5.innerHTML = '<label>Duration:</label><select name="duration[]" class="form-control"><option>< 3s</option><option>4-9s</option><option>> 10s</option></select>';
            } else if (testName === "ASLR") {
                // Column 2: Pelvis Control
                col2.innerHTML = '<label>Pelvis Control:</label><select name="pelvis_control[]" class="form-control"><option>Good</option><option>Moderate</option><option>Poor</option></select>';
                // Column 3: Umbilicus Reaction
                col3.innerHTML = '<label>Umbilicus Reaction:</label><select name="umbilicus_reaction[]" class="form-control"><option>Flip Down</option><option>Flip Up</option><option>Shift Left</option><option>Shift Right</option></select>';
                // Column 4 and 5: Empty

            } else if (testName === "SLB") {
                // Column 2: Pelvis Control
                col2.innerHTML = '<label>Pelvis Control:</label><select name="pelvis_control[]" class="form-control"><option>Good</option><option>Moderate</option><option>Poor</option></select>';
                // Column 3: Hip Extension
                col3.innerHTML = '<label>Hip Extension:</label><select name="hip_extension[]" class="form-control"><option>Good</option><option>Moderate</option><option>Poor</option></select>';
                // Column 4 and 5: Empty

            } else if (testName.includes("LSP Rotation") || testName.includes("TSP Rotation")) {
                // Column 2: Restriction
                col2.innerHTML = '<label>Restriction:</label><select name="restriction[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 3: Pain
                col3.innerHTML = '<label>Pain:</label><select name="pain[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 4: Block
                col4.innerHTML = '<label>Block:</label><select name="block[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 5: Empty

            } else if (testName === "Lats Crossover" || testName === "Swimmers" || testName === "Open Book" || testName === "Close Book") {
                // Column 2: Restriction
                col2.innerHTML = '<label>Restriction:</label><select name="restriction[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 3: Pain
                col3.innerHTML = '<label>Pain:</label><select name="pain[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 4: Block
                col4.innerHTML = '<label>Block:</label><select name="block[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 5: Empty

            } else if (testName === "Scaption" || testName === "PSLR") {
                // Column 2: Restriction
                col2.innerHTML = '<label>Restriction:</label><select name="restriction[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 3: Pain
                col3.innerHTML = '<label>Pain:</label><select name="pain[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 4: Block
                col4.innerHTML = '<label>Block:</label><select name="block[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 5: Empty

            } else if (testName === "Short Flexion (SF)" || testName === "Short Diagonal (SD)" || testName === "Long Diagonal (LD)") {
                // Column 2: Restriction
                col2.innerHTML = '<label>Restriction:</label><select name="restriction[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 3: Pain
                col3.innerHTML = '<label>Pain:</label><select name="pain[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 4: Block
                col4.innerHTML = '<label>Block:</label><select name="block[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 5: Empty

            } else if (testName.includes("Long Traction")) {
                // Column 2: Restriction
                col2.innerHTML = '<label>Restriction:</label><select name="restriction[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 3: Pain
                col3.innerHTML = '<label>Pain:</label><select name="pain[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 4: Block
                col4.innerHTML = '<label>Block:</label><select name="block[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 5: Empty

            } else if (testName.includes("90/90 Hip")) {
                // Column 2: Restriction
                col2.innerHTML = '<label>Restriction:</label><select name="restriction[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 3: Pain
                col3.innerHTML = '<label>Pain:</label><select name="pain[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 4: Block
                col4.innerHTML = '<label>Block:</label><select name="block[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 5: Degree
                col5.innerHTML = '<label>Degree:</label><input type="text" name="degree[]" class="form-control">';

            } else if (testName.includes("SIJ Compression")) {
                // Column 2: Restriction
                col2.innerHTML = '<label>Restriction:</label><select name="restriction[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 3: Pain
                col3.innerHTML = '<label>Pain:</label><select name="pain[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 4: Block
                col4.innerHTML = '<label>Block:</label><select name="block[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 5: Empty

            } else if (testName === "Talocrural" || testName === "Subtalar" || testName === "Midfoot" || testName === "Toes" || testName === "Patello-femoral" || testName === "TibioFib Proximal" || testName === "Tibiofibular Distal") {
                // Column 2: Restriction
                col2.innerHTML = '<label>Restriction:</label><select name="restriction[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 3: Pain
                col3.innerHTML = '<label>Pain:</label><select name="pain[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 4: Block
                col4.innerHTML = '<label>Block:</label><select name="block[]" class="form-control"><option>Severe</option><option>Moderate</option><option>Mild</option><option>Normal</option></select>';
                // Column 5: Empty

            } else if (testName === "Special Test") {
                // Column 2 to 5: Empty text inputs
                col2.innerHTML = '<input type="text" name="special_test_2[]" class="form-control">';
                col3.innerHTML = '<input type="text" name="special_test_3[]" class="form-control">';
                col4.innerHTML = '<input type="text" name="special_test_4[]" class="form-control">';
                col5.innerHTML = '<input type="text" name="special_test_5[]" class="form-control">';
            }
            // Update test result string when any field changes
            row.onchange = function() {
                var trunk = row.querySelector('select[name="trunk[]"]') ? row.querySelector('select[name="trunk[]"]').value : "";
                var hip = row.querySelector('select[name="hip[]"]') ? row.querySelector('select[name="hip[]"]').value : "";
                var foot = row.querySelector('select[name="foot[]"]') ? row.querySelector('select[name="foot[]"]').value : "";
                var duration = row.querySelector('select[name="duration[]"]') ? row.querySelector('select[name="duration[]"]').value : "";
                var pelvis_control = row.querySelector('select[name="pelvis_control[]"]') ? row.querySelector('select[name="pelvis_control[]"]').value : "";
                var umbilicus_reaction = row.querySelector('select[name="umbilicus_reaction[]"]') ? row.querySelector('select[name="umbilicus_reaction[]"]').value : "";
                var hip_extension = row.querySelector('select[name="hip_extension[]"]') ? row.querySelector('select[name="hip_extension[]"]').value : "";
                var restriction = row.querySelector('select[name="restriction[]"]') ? row.querySelector('select[name="restriction[]"]').value : "";
                var pain = row.querySelector('select[name="pain[]"]') ? row.querySelector('select[name="pain[]"]').value : "";
                var block = row.querySelector('select[name="block[]"]') ? row.querySelector('select[name="block[]"]').value : "";
                var degree = row.querySelector('input[name="degree[]"]') ? row.querySelector('input[name="degree[]"]').value : "";
                var test2 = row.querySelector('input[name="special_test_2[]"]') ? row.querySelector('input[name="special_test_2[]"]').value : "";
                var test3 = row.querySelector('input[name="special_test_3[]"]') ? row.querySelector('input[name="special_test_3[]"]').value : "";
                var test4 = row.querySelector('input[name="special_test_4[]"]') ? row.querySelector('input[name="special_test_4[]"]').value : "";
                var test5 = row.querySelector('input[name="special_test_5[]"]') ? row.querySelector('input[name="special_test_5[]"]').value : "";
                var remarksText = remarks.value;

                // Construct the test result string
                testResultInput.value = `Test: ${testName}, ` +
                    (trunk ? `Trunk: ${trunk}, ` : "") +
                    (pelvis_control ? `Pelvis Control: ${pelvis_control}, ` : "") +
                    (umbilicus_reaction ? `Umbilicus Reaction: ${umbilicus_reaction}, ` : "") +
                    (hip ? `Hip: ${hip}, ` : "") +
                    (hip_extension ? `Hip Extension: ${hip_extension}, ` : "") +
                    (foot ? `Foot: ${foot}, ` : "") +
                    (restriction ? `Restriction: ${restriction}, ` : "") +
                    (pain ? `Pain: ${pain}, ` : "") +
                    (block ? `Block: ${block}, ` : "") +
                    (degree ? `Degree: ${degree}, ` : "") +
                    (duration ? `Duration: ${duration}, ` : "") +
                    (test2 ? `${test2}, ` : "") +
                    (test3 ? `${test3}, ` : "") +
                    (test4 ? `${test4}, ` : "") +
                    (test5 ? `${test5}, ` : "") +
                    `Remarks: ${remarksText}`;
            };
        }

        function navigateToPage(page) {
            // Set the form action to the desired page
            document.getElementById('navigationForm').action = page;
            // Submit the form
            document.getElementById('navigationForm').submit();
        }
    </script>
</body>

</html>