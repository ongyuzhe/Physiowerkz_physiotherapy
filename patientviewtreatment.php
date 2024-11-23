<?php
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

// Fetch the patient ID from the session
$patient_id = $_SESSION['id'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Treatment Assessments - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
</head>
<style>
    h2 {
        color: #333;
        text-align: center;
        margin-top: 20px;
        font-size: 24px;
    }

    /* Styling for the tables */
    table {
        width: 90%;
        margin: 20px auto;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    th,
    td {
        padding: 12px 15px;
        text-align: center;
        border: 1px solid #ccc;
    }

    th {
        font-weight: bold;
        text-transform: uppercase;
    }

    td {
        color: #333;
    }

    /* Row styles */
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #e0e0e0;
    }

    /* Table-specific header colors */
    #videos-table th {
        background-color: #007bff;
        color: white;
    }

    /* Blue */
    #treatment-table th {
        background-color: #28a745;
        color: white;
    }

    /* Green */
    #tissue-table th {
        background-color: #17a2b8;
        color: white;
    }

    /* Teal */
    #exercise-table th {
        background-color: #ffc107;
        color: white;
    }

    /* Yellow */
    #test-table th {
        background-color: #dc3545;
        color: white;
    }

    /* Red */
    #assessment-table th {
        background-color: #6c757d;
        color: white;
    }

    /* Gray */

    /* Collapsible (details/summary) styling */
    details {
        width: 90%;
        margin: 20px auto;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    summary {
        font-size: 18px;
        font-weight: bold;
        padding: 12px;
        background-color: #007bff;
        color: white;
        cursor: pointer;
        text-transform: uppercase;
    }

    summary:hover {
        background-color: #0056b3;
    }

    details[open] summary {
        background-color: #0056b3;
        border-bottom: 2px solid white;
    }

    details>div {
        padding: 10px;
        background-color: #f9f9f9;
        color: #333;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        table {
            width: 100%;
        }

        th,
        td {
            padding: 10px;
            font-size: 14px;
        }

        summary {
            font-size: 16px;
        }
    }
</style>

<body>
    <main>
        <?php
        if ($patient_id) {
            // Fetch appointments for the patient
            $appointments = [];
            $sql_appointments = "SELECT appointment_id, patient_id, appointment_datetime, staff_id, status, patient_comments, staff_comments 
                         FROM appointments 
                         WHERE status != 'Cancelled' AND patient_id = ?";
            $stmt_appointments = $conn->prepare($sql_appointments);
            $stmt_appointments->bind_param("i", $patient_id);
            $stmt_appointments->execute();
            $result_appointments = $stmt_appointments->get_result();

            // Check if any records were returned
            if ($result_appointments->num_rows > 0) {
                while ($row_appointment = $result_appointments->fetch_assoc()) {
                    $appointments[] = [
                        'appointment_id' => $row_appointment['appointment_id'],
                        'patient_id' => $row_appointment['patient_id'],
                        'appointment_datetime' => $row_appointment['appointment_datetime'],
                        'staff_id' => $row_appointment['staff_id'],
                        'status' => $row_appointment['status'],
                        'patient_comments' => $row_appointment['patient_comments'],
                        'staff_comments' => $row_appointment['staff_comments'],
                    ];
                }
            }
            $stmt_appointments->close();

            // Loop through each appointment to fetch data
            foreach ($appointments as $appointment) {
                $appointment_id = htmlspecialchars($appointment['appointment_id']);
                $appointment_datetime = htmlspecialchars($appointment['appointment_datetime']);
                $staff_id = htmlspecialchars($appointment['staff_id']);

                echo "<details>";
                echo "<summary>Appointment ID: $appointment_id (Date: $appointment_datetime)</summary>";

                // Fetch Videos for the current appointment
                $videos = [];
                $sql_videos = "SELECT * FROM videos WHERE appointment_id = ? AND patient_id = ?";
                $stmt_videos = $conn->prepare($sql_videos);
                $stmt_videos->bind_param("ii", $appointment_id, $patient_id);
                $stmt_videos->execute();
                $result_videos = $stmt_videos->get_result();

                if ($result_videos->num_rows > 0) {
                    echo "<h3>Videos for Appointment ID: $appointment_id</h3>";
                    echo "<table id = 'videos-table' border='1'>
                            <tr>
                                <th>Video Name</th>
                                <th>Uploaded At</th>
                                <th>Preview</th>
                            </tr>";
                    while ($row_video = $result_videos->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row_video['name']) . "</td>
                                <td>" . htmlspecialchars($row_video['uploaded_at']) . "</td>
                                <td>
                                    <video width='320' height='240' controls>
                                        <source src='" . htmlspecialchars($row_video['location']) . "' type='video/mp4'>
                                        Your browser does not support the video tag.
                                    </video>
                                </td>
                                </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No videos found for Appointment ID: $appointment_id.</p>";
                }
                $stmt_videos->close();

                // SQL query condition based on appointment_id
                $appointment_condition = "AND appointment_id = ?";

                // Fetch Treatment Assessments
                $sql = "SELECT * FROM treatmentassessment WHERE patient_id = ? $appointment_condition";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $patient_id, $appointment_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<h3>Treatment Assessments</h3>";
                    echo "<table id = 'treatment-table' border='1'>
                            <tr>
                                <th>Pain Description</th>
                                <th>Activity</th>
                                <th>Severe Activity</th>
                                <th>VAS (Pain Scale)</th>
                                <th>Irritability</th>
                                <th>Symptoms</th>
                                <th>Acute Activity</th>
                                <th>Indications</th>
                                <th>Dos & Don'ts</th>
                                <th>Assessment Date</th>
                            </tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['painanddescription']) . "</td>
                                <td>" . htmlspecialchars($row['activity']) . "</td>
                                <td>" . htmlspecialchars($row['severe_activity']) . "</td>
                                <td>" . htmlspecialchars($row['vas_activity']) . "</td>
                                <td>" . htmlspecialchars($row['irritability_activity']) . "</td>
                                <td>" . htmlspecialchars($row['symptoms_activity']) . "</td>
                                <td>" . htmlspecialchars($row['acute_activity']) . "</td>
                                <td>" . htmlspecialchars($row['indicates_activity']) . "</td>
                                <td>" . htmlspecialchars($row['dos_donts_activity']) . "</td>
                                <td>" . htmlspecialchars($row['created_at']) . "</td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No treatment assessments found for Appointment ID: $appointment_id.</p>";
                }
                $stmt->close();

                // Repeat the above process for other data types (Tissue Assessments, Exercise Suggestions, etc.)
                // Fetch Tissue Assessments
                $sql_tissue = "SELECT * FROM treatmentassessment3 WHERE patient_id = ? $appointment_condition";
                $stmt_tissue = $conn->prepare($sql_tissue);
                $stmt_tissue->bind_param("ii", $patient_id, $appointment_id);
                $stmt_tissue->execute();
                $result_tissue = $stmt_tissue->get_result();

                if ($result_tissue->num_rows > 0) {
                    echo "<h3>Tissue Assessments</h3>";
                    echo "<table id = 'tissue-table' border='1'>
                            <tr>
                                <th>Tissue Type</th>
                                <th>Tissue Findings</th>
                                <th>Remarks</th>
                            </tr>";
                    while ($row = $result_tissue->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['tissue_type']) . "</td>
                                <td>" . htmlspecialchars($row['tissue_findings']) . "</td>
                                <td>" . htmlspecialchars($row['remarks']) . "</td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No tissue assessments found for Appointment ID: $appointment_id.</p>";
                }
                $stmt_tissue->close();

                // Fetch Exercise Suggestions
                $sql_exercise = "SELECT * FROM exercise_suggestions WHERE patient_id = ? $appointment_condition";
                $stmt_exercise = $conn->prepare($sql_exercise);
                $stmt_exercise->bind_param("ii", $patient_id, $appointment_id);
                $stmt_exercise->execute();
                $result_exercise = $stmt_exercise->get_result();

                if ($result_exercise->num_rows > 0) {
                    echo "<h3>Exercise Suggestions</h3>";
                    echo "<table id = 'exercise-table' border='1'>
                            <tr>
                                <th>Exercise Suggestion</th>
                                <th>Situation (Severe/Moderate/Low)</th>
                                <th>Suggested On</th>
                                <th>Additional Remarks</th>
                            </tr>";
                    while ($row = $result_exercise->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['suggestion']) . "</td>
                                <td>" . htmlspecialchars($row['situation']) . "</td>
                                <td>" . htmlspecialchars($row['date']) . "</td>
                                <td>" . htmlspecialchars($row['remarks']) . "</td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No exercise suggestions found for Appointment ID: $appointment_id.</p>";
                }
                $stmt_exercise->close();

                // Fetch Test Tracking
                $sql_test = "SELECT * FROM test_tracking WHERE patient_id = ? $appointment_condition";
                $stmt_test = $conn->prepare($sql_test);
                $stmt_test->bind_param("ii", $patient_id, $appointment_id);
                $stmt_test->execute();
                $result_test = $stmt_test->get_result();

                if ($result_test->num_rows > 0) {
                    echo "<h3>Test Tracking Results</h3>";
                    echo "<table id = 'test-table' border='1'>
                            <tr>
                                <th>Test Results</th>
                            </tr>";
                    while ($row_test = $result_test->fetch_assoc()) {
                        echo "<tr>
                <td>" . htmlspecialchars($row_test['test_result']) . "</td>
              </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No test tracking records found for Appointment ID: $appointment_id.</p>";
                }
                $stmt_test->close();

                // Fetch Assessment Findings
                $sql_assessment = "SELECT * FROM assessment_findings WHERE patient_id = ? $appointment_condition";
                $stmt_assessment = $conn->prepare($sql_assessment);
                $stmt_assessment->bind_param("ii", $patient_id, $appointment_id);
                $stmt_assessment->execute();
                $result_assessment = $stmt_assessment->get_result();

                if ($result_assessment->num_rows > 0) {
                    echo "<h3>Assessment Findings</h3>";
                    echo "<table id = 'assessment-table' border='1'>
                            <tr>
                                <th>Body Part</th>
                                <th>Condition Type</th>
                                <th>Severity</th>
                                <th>Remarks</th>
                            </tr>";
                    while ($row_assessment = $result_assessment->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row_assessment['body_part']) . "</td>
                                <td>" . htmlspecialchars($row_assessment['condition_type']) . "</td>
                                <td>" . htmlspecialchars($row_assessment['severity']) . "</td>
                                <td>" . htmlspecialchars($row_assessment['remarks']) . "</td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No assessment findings found for Appointment ID: $appointment_id.</p>";
                }
                $stmt_assessment->close();

                echo "</details>";
            }
        } else {
            echo "<p>Please log in to view your treatment assessments, tissue assessments, exercise suggestions, and test results.</p>";
        }
        ?>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>