<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
}

// Ensure lock_status is set for current user session
$lock_status = $_SESSION['lock_status'] ?? 'No';

if ($lock_status === 'Yes') {
    echo "<script>alert('You are restricted from edit or add assesment due to your lock status.');</script>";
}

// Fetch patients' information
$sql = "SELECT patient_id, CONCAT(first_name, ' ', last_name) AS patient_name FROM patients";
$result = $conn->query($sql);

$patient_id = isset($_POST['patient_id']) ? $_POST['patient_id'] : null;

// Retrieve the patient_id from previous form submission (if exists)
if (isset($_POST['patient_id'])) {
    // Prepare SQL to fetch first_name and last_name based on patient_id
    $sql = "SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM patients WHERE patient_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind the patient_id to the SQL statement
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch the row if available
        if ($row = $result->fetch_assoc()) {
            $patient_name = $row['full_name'];  // Concatenate first_name and last_name

        } else {
            $patient_name = "Unknown";  // Default if no match found
        }

        // Close statement
        $stmt->close();
    }
}

// Retrieve treatment assessment data if patient_id is provided
$treatmentData = [];
if (isset($_POST['patient_id'])) {
    $appointment_id = $_POST['appointment_id'];
    $sql_treatment = "SELECT * FROM treatmentassessment WHERE patient_id = ? AND appointment_id = ?";

    $stmt_treatment = $conn->prepare($sql_treatment);
    $stmt_treatment->bind_param("ii", $patient_id, $appointment_id);
    $stmt_treatment->execute();
    $result_treatment = $stmt_treatment->get_result();

    while ($row = $result_treatment->fetch_assoc()) {
        // Extract pain type and description
        if (!empty($row['painanddescription'])) {
            list($pain_type, $pain_description) = explode(': ', $row['painanddescription'], 2);
            $row['pain_type'] = $pain_type; // Store pain type
            $row['pain_description'] = $pain_description; // Store pain description
        } else {
            $row['pain_type'] = '';
            $row['pain_description'] = '';
        }
        $treatmentData[] = $row; // Add to treatment data array
    }
    $stmt_treatment->close();
}

// Determine activity count
$activityCount = count($treatmentData) > 0 ? count($treatmentData) + 1 : 2; // Count + 1 or start from 2

// If no treatment data exists, initialize with an empty form
if (empty($treatmentData)) {
    $treatmentData[] = [
        'pain_type' => '',
        'pain_description' => '',
        'activity' => '',
        'severe_activity' => '',
        'vas_activity' => 5,
        'irritability_activity' => '',
        'symptoms_activity' => '',
        'acute_activity' => '',
        'remarks_activity' => '',
        'indicates_activity' => '',
        'dos_donts_activity' => '',
        'duration_activity' => '',
        'aware_of_pain_body_activity' => '',
        'movement_activity' => '',
        'protect_by_activity' => '',
        'gradual_load_activity' => '',
        'd1_3_activity' => '',
        'd4_7_activity' => '',
        'week2_activity' => '',
        'week3_activity' => '',
        'week4_activity' => '',
    ];
}

// Handle delete activity request
if (isset($_GET['delete_activity_id'])) {
    $delete_activity_id = $_GET['delete_activity_id'];

    // Prepare and execute deletion
    $sql_delete = "DELETE FROM treatmentassessment WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_activity_id);

    if ($stmt_delete->execute()) {
        echo "<script>alert('Activity deleted successfully!'); window.location.href='view_treatment.php';</script>";
    } else {
        echo "<script>alert('Error deleting activity.');</script>";
    }

    $stmt_delete->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Log the POST data for debugging
    error_log("POST Data: " . print_r($_POST, true));
    $patient_id = $_POST['patient_id'];
    $appointment_id = $_POST['appointment_id'];

    // Check if 'activities' is set and is an array, otherwise initialize it
    $activities = $_POST['activities'] ?? [];

    // Loop through activities array if it is not empty
    if (is_array($activities) && !empty($activities)) {
        foreach ($activities as $activity) {

            $pain_type = trim($activity['name']);  // Get the selected pain type
            $pain_description = trim($activity['pain_description']);  // Get the pain description
            $activity_name = trim($activity['activity1']);
            $merged_pain_details = $pain_type . ': ' . $pain_description;
            $severe_activity = $activity['severity'];
            $remarks_activity = $activity['remarks'] ?? NULL;
            $vas_activity = $activity['vas'];
            $irritability_activity = trim($activity['irritability']);
            $symptoms_activity = $activity['symptoms'] ?? NULL;
            $acute_activity = $activity['acute'];
            $indicates_activity = $activity['indicates'] ?? NULL;
            $dos_donts_activity = $activity['dos_donts'] ?? NULL;
            $duration_activity = $activity['duration'] ?? NULL;
            $aware_of_pain_body_activity = $activity['aware_of_pain_body'];
            $movement_activity = $activity['movement'] ?? NULL;
            $protect_by_activity = $activity['protect_by'] ?? NULL;
            $gradual_load_activity = $activity['gradual_load'] ?? NULL;
            $d1_3_activity = $activity['d1_3'] ?? NULL;
            $d4_7_activity = $activity['d4_7'] ?? NULL;
            $week2_activity = $activity['week2'] ?? NULL;
            $week3_activity = $activity['week3'] ?? NULL;
            $week4_activity = $activity['week4'] ?? NULL;

            // Check if the record already exists
            $sql_check = "SELECT id FROM treatmentassessment WHERE appointment_id = ? AND patient_id = ? AND painanddescription = ? AND activity = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("iiss", $appointment_id, $patient_id, $merged_pain_details, $activity_name);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Update the existing row if found
                $sql_update = "UPDATE treatmentassessment SET 
                    severe_activity = ?, remarks_activity = ?, vas_activity = ?, irritability_activity = ?, 
                    symptoms_activity = ?, acute_activity = ?, indicates_activity = ?, dos_donts_activity = ?, 
                    duration_activity = ?, aware_of_pain_body_activity = ?, movement_activity = ?, protect_by_activity = ?, 
                    gradual_load_activity = ?, d1_3_activity = ?, d4_7_activity = ?, week2_activity = ?, 
                    week3_activity = ?, week4_activity = ?
                    WHERE appointment_id = ? AND patient_id = ? AND painanddescription = ? AND activity = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param(
                    "ssisssssssssssssssiiss",
                    $severe_activity,
                    $remarks_activity,
                    $vas_activity,
                    $irritability_activity,
                    $symptoms_activity,
                    $acute_activity,
                    $indicates_activity,
                    $dos_donts_activity,
                    $duration_activity,
                    $aware_of_pain_body_activity,
                    $movement_activity,
                    $protect_by_activity,
                    $gradual_load_activity,
                    $d1_3_activity,
                    $d4_7_activity,
                    $week2_activity,
                    $week3_activity,
                    $week4_activity,
                    $appointment_id,
                    $patient_id,
                    $merged_pain_details,
                    $activity_name
                );

                if ($stmt_update->execute()) {
                    echo "<script>alert('Activity updated successfully.'); window.location.href='view_treatment.php';</script>";
                } else {
                    echo "<script>alert('Error updating activity: " . addslashes($stmt_update->error) . "');</script>";
                }
                $stmt_update->close();
            } else {
                // Proceed with your SQL insert
                $sql_insert = "INSERT INTO treatmentassessment (
        appointment_id, patient_id, Name, painanddescription, activity, severe_activity, remarks_activity, 
        vas_activity, irritability_activity, symptoms_activity, acute_activity, indicates_activity,
        dos_donts_activity, duration_activity, aware_of_pain_body_activity, movement_activity,
        protect_by_activity, gradual_load_activity, d1_3_activity, d4_7_activity, week2_activity,
        week3_activity, week4_activity
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param(
                    "iisssssisssssssssssssss",
                    $appointment_id,  // Pass the appointment_id here
                    $patient_id,
                    $patient_name,
                    $merged_pain_details,
                    $activity_name,
                    $severe_activity,
                    $remarks_activity,
                    $vas_activity,
                    $irritability_activity,
                    $symptoms_activity,
                    $acute_activity,
                    $indicates_activity,
                    $dos_donts_activity,
                    $duration_activity,
                    $aware_of_pain_body_activity,
                    $movement_activity,
                    $protect_by_activity,
                    $gradual_load_activity,
                    $d1_3_activity,
                    $d4_7_activity,
                    $week2_activity,
                    $week3_activity,
                    $week4_activity
                );
                if ($stmt_insert->execute()) {
                    echo "<script>alert('Activity inserted successfully.'); window.location.href='view_treatment.php';</script>";
                } else {
                    echo "<script>alert('Error inserting activity: " . addslashes($stmt_insert->error) . "');</script>";
                }
                $stmt_insert->close();
            }

            // Close check statement
            $stmt_check->close();
        }
    }
}

// Close the connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        /* General form styles */
        form {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f7f7;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }

        /* Styles for the hidden form */
        .hidden-form {
            max-width: none;
            padding: 0;
            background-color: transparent;
            border: none;
            box-shadow: none;
        }

        /* Flex container for two-column layout */
        .form-group-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Each form group in the half-width column */
        .form-group {
            flex: 1 1 calc(50% - 10px);
            /* Two columns, with space in between */
            min-width: 250px;
            /* Ensures fields don't get too narrow */
            margin-bottom: 20px;
        }

        /* Full-width form group for fields that need to span across columns */
        .form-group.full-width {
            flex: 1 1 100%;
        }

        .form-group label {
            width: 100px;
            /* Set a fixed width for all labels */
            margin-right: 15px;
            /* Adds space between label and input */
            font-size: 16px;
            font-weight: 600;
        }

        .form-group select,
        .form-group textarea,
        .form-group input {
            flex: 1;
            /* Allows input fields to fill available space */
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .form-group textarea {
            min-height: 70px;
            resize: vertical;
        }

        /* Hover and focus effects */
        .form-group select:focus,
        .form-group textarea:focus,
        .form-group input:focus {
            border-color: #007BFF;
            outline: none;
        }

        /* Styling for submit button */
        button[type="submit"] {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        #add-activity-button {
            display: inline-block;
            background-color: #28a745;
            /* Green background */
            color: white;
            /* White text */
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 20px;
            /* Add some space below the button */
        }

        #add-activity-button:hover {
            background-color: #218838;
            /* Darker green on hover */
        }

        #add-activity-button:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
        }


        /* Responsive design for small screens */
        @media (max-width: 768px) {
            .form-group {
                flex: 1 1 100%;
                /* Full width on small screens */
            }

            button[type="submit"] {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main>
        <h1>Assessment</h1>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment_id ?? ''); ?>">
            <!-- Hidden field to store patient name -->
            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id ?? ''); ?>">
            <input type="hidden" name="patient_name" id="patient_name" value="<?php echo htmlspecialchars($patient_name); ?>">

            <!-- Container for Activities -->
            <div id="activities-container">
                <?php foreach ($treatmentData as $index => $activityData): ?>
                    <h2>Activity <?php echo $index + 1; ?></h2>
                    <div class="activity-form" id="activity-form-<?php echo $index; ?>">

                        <!-- Pain Type Details -->
                        <div class="form-group-wrapper">
                            <div class="form-group">
                                <label for="pain_type_<?php echo $index; ?>">Pain Type:</label>
                                <select name="activities[<?php echo $index; ?>][name]" id="pain_type_<?php echo $index; ?>">
                                    <option value="low_back_pain" <?php echo ($activityData['pain_type'] == 'low_back_pain') ? 'selected' : ''; ?>>Low Back Pain</option>
                                    <option value="neck_pain" <?php echo ($activityData['pain_type'] == 'neck_pain') ? 'selected' : ''; ?>>Neck Pain</option>
                                    <option value="shoulder_pain" <?php echo ($activityData['pain_type'] == 'shoulder_pain') ? 'selected' : ''; ?>>Shoulder Pain</option>
                                    <option value="knee_pain" <?php echo ($activityData['pain_type'] == 'knee_pain') ? 'selected' : ''; ?>>Knee Pain</option>
                                    <option value="hip_pain" <?php echo ($activityData['pain_type'] == 'hip_pain') ? 'selected' : ''; ?>>Hip Pain</option>
                                    <option value="other" <?php echo ($activityData['pain_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <!-- Pain Description -->
                            <div class="form-group">
                                <label for="pain_description_<?php echo $index; ?>">Describe your pain:</label>
                                <textarea id="pain_description_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][pain_description]" placeholder="Describe your condition..."><?php echo htmlspecialchars($activityData['pain_description'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group-wrapper">
                            <div class="form-group">
                                <label for="activity1_<?php echo $index; ?>">Activity:</label>
                                <input type="text" id="activity1_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][activity1]" placeholder="Details about activity..." value="<?php echo htmlspecialchars($activityData['activity'] ?? ''); ?>">
                            </div>

                            <!-- Severity, VAS, Irritability, Symptoms... -->
                            <div class="form-group">
                                <label>Severity:</label>
                                <select name="activities[<?php echo $index; ?>][severity]">
                                    <option value="short" <?php echo ($activityData['severe_activity'] == 'short') ? 'selected' : ''; ?>>Short &lt; 1hr</option>
                                    <option value="medium" <?php echo ($activityData['severe_activity'] == 'medium') ? 'selected' : ''; ?>>Medium 1-2hrs</option>
                                    <option value="long" <?php echo ($activityData['severe_activity'] == 'long') ? 'selected' : ''; ?>>Long &gt; 2hrs</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group-wrapper">
                            <div class="form-group">
                                <label for="vas_activity_<?php echo $index; ?>">Visual Analog Scale (VAS):</label>
                                <input type="range" name="activities[<?php echo $index; ?>][vas]" id="vas_slider_<?php echo $index; ?>" min="0" max="10" value="<?php echo htmlspecialchars($activityData['vas_activity'] ?? 5); ?>">
                                <span id="vas_value_<?php echo $index; ?>"><?php echo htmlspecialchars($activityData['vas_activity'] ?? 5); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="irritability_<?php echo $index; ?>">Irritability:</label>
                                <select id="irritability_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][irritability]">
                                    <option value="low" <?php echo ($activityData['irritability_activity'] ?? '') == 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="moderate" <?php echo ($activityData['irritability_activity'] ?? '') == 'moderate' ? 'selected' : ''; ?>>Moderate</option>
                                    <option value="high" <?php echo ($activityData['irritability_activity'] ?? '') == 'high' ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group-wrapper">
                            <div class="form-group">
                                <label for="symptoms_activity_<?php echo $index; ?>">Symptoms:</label>
                                <textarea name="activities[<?php echo $index; ?>][symptoms]" placeholder="Describe your symptoms..."><?php echo htmlspecialchars($activityData['symptoms_activity'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="acute_activity_<?php echo $index; ?>">Acute:</label>
                                <select id="acute_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][acute]">
                                    <option value="2-6_wk" <?php echo ($activityData['acute_activity'] ?? '') == '2-6_wk' ? 'selected' : ''; ?>>2-6 wk</option>
                                    <option value="acute_on_chronic" <?php echo ($activityData['acute_activity'] ?? '') == 'acute_on_chronic' ? 'selected' : ''; ?>>Acute on Chronic</option>
                                    <option value="chronic" <?php echo ($activityData['acute_activity'] ?? '') == 'chronic' ? 'selected' : ''; ?>>Chronic (supervised exercises/movement)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group-wrapper">
                            <div class="form-group">
                                <label for="remarks_activity_<?php echo $index; ?>">Remarks:</label>
                                <textarea name="activities[<?php echo $index; ?>][remarks]" id="remarks_activity_<?php echo $index; ?>" placeholder="Remarks with duration..."><?php echo htmlspecialchars($activityData['remarks'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="indicates_activity_<?php echo $index; ?>">Indicates:</label>
                                <textarea id="indicates_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][indicates]" placeholder="Indications..."><?php echo htmlspecialchars($activityData['indicates_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <div class="form-group">
                                <label for="dos_donts_activity_<?php echo $index; ?>">Dos and Don'ts:</label>
                                <textarea id="dos_donts_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][dos_donts]" placeholder="Dos and Don'ts..."><?php echo htmlspecialchars($activityData['dos_donts_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group-wrapper">
                            <div class="form-group">
                                <label for="duration_activity_<?php echo $index; ?>">Duration:</label>
                                <input type="text" id="duration_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][duration]" placeholder="Duration of pain..." value="<?php echo htmlspecialchars($activityData['duration_activity'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="aware_of_pain_body_activity_<?php echo $index; ?>">Aware of Pain in Body:</label>
                                <select name="activities[<?php echo $index; ?>][aware_of_pain_body]" id="aware_of_pain_body_activity_<?php echo $index; ?>">
                                    <option value="yes" <?php echo (isset($activity['aware_of_pain_body']) && $activity['aware_of_pain_body'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                                    <option value="no" <?php echo (isset($activity['aware_of_pain_body']) && $activity['aware_of_pain_body'] == 'no') ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <div class="form-group">
                                <label for="movement_activity_<?php echo $index; ?>">Movement:</label>
                                <textarea id="movement_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][movement]" placeholder="Movement-related details..."><?php echo htmlspecialchars($activityData['movement_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group-wrapper">
                            <div class="form-group">
                                <label for="protect_by_activity_<?php echo $index; ?>">Protect By:</label>
                                <textarea id="protect_by_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][protect_by]" placeholder="Protection mechanisms..."><?php echo htmlspecialchars($activityData['protect_by_activity'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="gradual_load_activity_<?php echo $index; ?>">Gradual Load:</label>
                                <textarea id="gradual_load_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][gradual_load]" placeholder="Gradual load strategies..."><?php echo htmlspecialchars($activityData['gradual_load_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <div class="form-group">
                                <label for="d1_3_activity_<?php echo $index; ?>">Days 1-3:</label>
                                <textarea id="d1_3_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][d1_3]" placeholder="Activities from Day 1 to Day 3..."><?php echo htmlspecialchars($activityData['d1_3_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <div class="form-group">
                                <label for="d4_7_activity_<?php echo $index; ?>">Days 4-7:</label>
                                <textarea id="d4_7_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][d4_7]" placeholder="Activities from Day 4 to Day 7..."><?php echo htmlspecialchars($activityData['d4_7_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <div class="form-group">
                                <label for="week2_activity_<?php echo $index; ?>">Week 2:</label>
                                <textarea id="week2_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][week2]" placeholder="Activities for Week 2..."><?php echo htmlspecialchars($activityData['week2_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <div class="form-group">
                                <label for="week3_activity_<?php echo $index; ?>">Week 3:</label>
                                <textarea id="week3_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][week3]" placeholder="Activities for Week 3..."><?php echo htmlspecialchars($activityData['week3_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <div class="form-group">
                                <label for="week4_activity_<?php echo $index; ?>">Week 4:</label>
                                <textarea id="week4_activity_<?php echo $index; ?>" name="activities[<?php echo $index; ?>][week4]" placeholder="Activities for Week 4..."><?php echo htmlspecialchars($activityData['week4_activity'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <?php if ($_SESSION['lock_status'] === 'Yes'): ?>
                            <span class="locked-text">Activity ID: <?php echo htmlspecialchars($activityData['id']); ?> (Locked) Viewed Only</span>
                        <?php else: ?>
                            <!-- Delete Link -->
                            <a href="treatmentdoctor1.php?delete_activity_id=<?php echo htmlspecialchars($activityData['id']); ?>"
                                onclick="return confirm('Are you sure you want to delete this activity?');"
                                class="delete-button">Delete Activity</a>
                        <?php endif; ?>
                        <br><br>
                        <hr>

                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($_SESSION['lock_status'] === 'Yes') : ?>
                <!-- Change Add New Activity button to text -->
                <span style="color: red;">Add New Activity (Locked)</span><br><br>

                <!-- Change Submit button to Back -->
                <button type="button" onclick="window.location.href='view_treatment.php'">Back</button>
            <?php else : ?>
                <!-- Add New Activity Button -->
                <button type="button" id="add-activity-button">Add New Activity</button>

                <!-- Submit Button -->
                <button type="submit">Submit</button>
            <?php endif; ?>

        </form>

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
                onclick="navigateToPage('treatmentdoctor4.php')">
                <img src="images/left-arrow.png" alt="Previous" style="width: 50px; height: 50px;">
            </div>

            <div
                class="floating-button right"
                style="position: fixed; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; z-index: 1000;"
                onclick="navigateToPage('treatmentdoctor2.php')">
                <img src="images/right-arrow.png" alt="Next" style="width: 50px; height: 50px;">
            </div>
        </div>

        <script>
            let activityCount = <?php echo $activityCount; ?>; // Initialize from PHP

            function createNewActivityForm(index) {
                return `
        <h2>Activity ${index}</h2>
        <div class="activity-form" id="activity-form-${index}">
            <div class="form-group-wrapper">
                <div class="form-group">
                    <label for="pain_type_${index}">Pain Type:</label>
                    <select name="activities[${index}][name]" id="pain_type_${index}">
                        <option value="low_back_pain">Low Back Pain</option>
                        <option value="neck_pain">Neck Pain</option>
                        <option value="shoulder_pain">Shoulder Pain</option>
                        <option value="knee_pain">Knee Pain</option>
                        <option value="hip_pain">Hip Pain</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pain_description_${index}">Describe your pain:</label>
                    <textarea id="pain_description_${index}" name="activities[${index}][pain_description]" placeholder="Describe your condition..."></textarea>
                </div>
            </div>
            <div class="form-group-wrapper">
                <div class="form-group">
                    <label for="activity1_${index}">Activity 1:</label>
                    <input type="text" id="activity1_${index}" name="activities[${index}][activity1]" placeholder="Details about activity...">
                </div>
                <div class="form-group">
                    <label>Severe:</label>
                    <select name="activities[${index}][severity]">
                        <option value="short">Short < 1hr</option>
                        <option value="medium">Medium 1-2hrs</option>
                        <option value="long">Long > 2hrs</option>
                    </select>
                </div>
            </div>
            <div class="form-group-wrapper">
                <div class="form-group">
                    <label for="vas_slider_${index}">Visual Analog Scale (VAS):</label>
                    <input type="range" name="activities[${index}][vas]" id="vas_slider_${index}" min="0" max="10" value="5">
                    <span id="vas_value_${index}">5</span> <!-- Display for VAS value -->
                </div>
                <div class="form-group">
                    <label for="irritability_${index}">Irritability:</label>
                    <select name="activities[${index}][irritability]" id="irritability_${index}">
                        <option value="low">Low</option>
                        <option value="moderate">Moderate</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="form-group-wrapper">
                <div class="form-group">
                    <label for="symptoms_activity_${index}">Symptoms:</label>
                    <textarea name="activities[${index}][symptoms]" id="symptoms_activity_${index}" placeholder="Describe your symptoms..."></textarea>
                </div>
                <div class="form-group">
                    <label>Acute:</label>
                    <select name="activities[${index}][acute]">
                        <option value="2-6_wk">2-6 wk</option>
                        <option value="acute_on_chronic">Acute on Chronic</option>
                        <option value="chronic">Chronic (supervised exercises/movement)</option>
                    </select>
                </div>
            </div>
            <div class="form-group-wrapper">
                <div class="form-group">
                    <label for="remarks_activity_${index}">Remarks:</label>
                    <textarea name="activities[${index}][remarks]" id="remarks_activity_${index}" placeholder="Remarks with duration..."></textarea>
                </div>
                <div class="form-group">
                    <label for="indicates_activity_${index}">Indicates:</label>
                    <textarea name="activities[${index}][indicates]" id="indicates_activity_${index}" placeholder="Indications..."></textarea>
                </div>
            </div>
            <div class="form-group full-width">
                <div class="form-group">
                    <label for="dos_donts_activity_${index}">Dos/Donts:</label>
                    <textarea name="activities[${index}][dos_donts]" id="dos_donts_activity_${index}" placeholder="What to do and what not to do..."></textarea>
                </div>
            </div>
            <div class="form-group-wrapper">
                <div class="form-group">
                    <label for="duration_activity_${index}">Duration:</label>
                    <input type="text" name="activities[${index}][duration]" id="duration_activity_${index}" placeholder="Max 15, 30, 45, 60 mins.">
                </div>
                <div class="form-group">
                    <label>Aware of Pain/Body:</label>
                    <select name="activities[${index}][aware_of_pain_body]">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
            </div>
            <div class="form-group full-width">
                <div class="form-group">
                    <label for="movement_activity_${index}">Movement:</label>
                    <textarea name="activities[${index}][movement]" id="movement_activity_${index}" placeholder="Describe your movement..."></textarea>
                </div>
            </div>
            <div class="form-group-wrapper">
                <div class="form-group">
                    <label for="protect_by_activity_${index}">Protect by:</label>
                    <textarea name="activities[${index}][protect_by]" id="protect_by_activity_${index}" placeholder="How to protect..."></textarea>
                </div>
                <div class="form-group">
                    <label for="gradual_load_activity_${index}">Gradual load by:</label>
                    <textarea name="activities[${index}][gradual_load]" id="gradual_load_activity_${index}" placeholder="Gradual loading strategy..."></textarea>
                </div>
            </div>
            <div class="form-group full-width">
                <div class="form-group">
                    <label>D1-3:</label>
                    <input type="text" name="activities[${index}][d1_3]" placeholder="Complete rest">
                </div>
            </div>
            <div class="form-group full-width">
                <div class="form-group">
                    <label>D4-7:</label>
                    <input type="text" name="activities[${index}][d4_7]" placeholder="Protected move/rest (cautious with _____)">
                </div>
            </div>
            <div class="form-group full-width">
                <div class="form-group">
                    <label>Week 2:</label>
                    <input type="text" name="activities[${index}][week2]" placeholder="10% _____">
                </div>
            </div>
            <div class="form-group full-width">
                <div class="form-group">
                    <label>Week 3:</label>
                    <input type="text" name="activities[${index}][week3]" placeholder="20% _____">
                </div>
            </div>
            <div class="form-group full-width">
                <div class="form-group">
                    <label>Week 4:</label>
                    <input type="text" name="activities[${index}][week4]" placeholder="30% _____">
                </div>
            </div>
        </div>
    `;
            }

            // Event listener for adding new activity sections
            document.getElementById('add-activity-button').addEventListener('click', function() {
                const activitiesContainer = document.getElementById('activities-container');
                const newActivityForm = createNewActivityForm(activityCount);
                activitiesContainer.insertAdjacentHTML('beforeend', newActivityForm);

                // Attach event listener for the VAS slider of the newly added activity
                const vasSlider = document.getElementById(`vas_slider_${activityCount}`);
                const vasValue = document.getElementById(`vas_value_${activityCount}`);

                // Update the displayed value when the slider is changed
                vasSlider.oninput = function() {
                    vasValue.innerHTML = this.value;
                };

                activityCount++;
            });

            // Get the first VAS slider and value display elements
            const vasSlider0 = document.getElementById('vas_slider_0');
            const vasValue0 = document.getElementById('vas_value_0');

            // Update the displayed value when the slider is changed
            vasSlider0.oninput = function() {
                vasValue0.innerHTML = this.value;
            };

            function updatePatientName() {
                var patientDropdown = document.getElementById("patient");
                var selectedOptionText = patientDropdown.options[patientDropdown.selectedIndex].text;
                document.getElementById("patient_name").value = selectedOptionText;

                console.log("Selected Patient Name: ", selectedOptionText); // Log the selected name
            }

            function navigateToPage(page) {
                // Set the form action to the desired page
                document.getElementById('navigationForm').action = page;
                // Submit the form
                document.getElementById('navigationForm').submit();
            }
        </script>

    </main>
</body>
<?php include 'includes/footer.php'; ?>

</html>