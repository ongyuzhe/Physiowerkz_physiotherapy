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

// Fetch the account creation date from the patients table
$created_at = '';
$days_with_physiowerkz = 0;
$questionnaire_status = 1; // Default to completed
$session_count_status = 0;

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];

    // Fetch account creation date
    $sql = "SELECT created_at FROM patients WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_username);
        $param_username = $username;

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($created_at);
                $stmt->fetch();

                $created_at_date = new DateTime($created_at);
                $current_date = new DateTime();
                $interval = $created_at_date->diff($current_date);
                $days_with_physiowerkz = $interval->days; // Days since account creation
            }
        } else {
            echo "Oops! Something went wrong while fetching the creation date. Please try again later.";
        }

        $stmt->close();
    }

    // Check if the questionnaire is completed and get session count
    $sql = "SELECT patient_id, ques_status, session_count, survey_status FROM patients WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_username);
        $param_username = $username;

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($patient_id, $ques_status, $session_count, $survey_status);
                $stmt->fetch();

                // Set questionnaire status and survey status
                $questionnaire_status = ($ques_status == 0) ? 0 : 1;
                $session_count_status = $session_count;
                $survey_status1 = $survey_status; // Set survey_status to be used in JavaScript
            } else {
                // Default values if no patient data is found
                $questionnaire_status = null;
                $session_count_status = null;
                $patient_id = null;
                $survey_status1 = null;
            }
        } else {
            echo "Oops! Something went wrong while fetching the questionnaire status. Please try again later.";
        }

        $stmt->close();
    }
} else {
    // Handle the case where the user is not logged in
    echo "User is not logged in.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Physiowerkz</title>
    <?php
    // Server-side checks for JavaScript variables
    $questionnaire_status = $questionnaire_status ?? null;
    $session_count_status = $session_count_status ?? null;
    $patient_id = $patient_id ?? null;

    // Database check for existing entry in `treatment_survey` for patient_id and session_count_status
    $survey_exists = false;
    if ($patient_id && $session_count_status) {
        $query = "SELECT 1 FROM treatment_survey WHERE patient_id = ? AND session_num = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $patient_id, $session_count_status);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $survey_exists = true;
        }
        $stmt->close();
    }
    ?>

    <script>
        window.onload = function () {

            // Initialize variables from PHP
            var questionnaireStatus = <?php echo json_encode($questionnaire_status); ?>;

            if (questionnaireStatus === 0) {
                alert("You need to fill out the questionnaire. Redirecting...");
                window.location.href = "health_questionnaire.php";
            }
            var sessionCountStatus = <?php echo json_encode($session_count_status); ?>;
            var surveyExists = <?php echo json_encode($survey_exists); ?>;
            var surveyStatus = <?php echo json_encode($survey_status1); ?>;
            // Check all conditions
            if (surveyStatus === "Yes" && sessionCountStatus > 0 && (sessionCountStatus === 1 || sessionCountStatus === 6 || sessionCountStatus === 10 || sessionCountStatus % 10 === 0) && !surveyExists) {
                alert(`Please complete the survey for your ${sessionCountStatus}th session. Redirecting...`);
                window.location.href = "survey_session.php";
            }


        };
    </script>

    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
</head>

<style>
    /* Our Services Section Styling */
    .services {
        text-align: center;
        margin: 40px 0;
    }

    .services h2 {
        font-size: 1.8em;
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .service-list {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .service-item {
        border: 1px solid #bdc3c7;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s;
        width: 150px;
        /* Adjust based on your layout preference */
    }

    .service-item img {
        width: 100%;
        height: auto;
    }

    .service-item p {
        margin: 10px 0;
        font-weight: bold;
        color: #34495e;
    }

    .service-item:hover {
        transform: scale(1.05);
        border-color: #3498db;
    }
</style>

<body>
    <main>
        <div>
            <h1>Welcome to Physiowerkz, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <p>We are dedicated to providing the best physiotherapy services for your health and well-being.</p>
            <p>Thank you for <strong><?php echo $days_with_physiowerkz; ?> days</strong> with Physiowerkz.</p>
        </div>

        <!-- Our Services Section -->
        <div class="services">
            <h2>Our Services</h2>
            <div class="service-list">
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/body-mechanix-physiowerkz/">
                        <img src="images/body_mechanix.jpeg" alt="Body Mechanix">
                        <p>Body Mechanix</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/scolio-x-physiowerkz/">
                        <img src="images/scolio_x.jpeg" alt="Scolio-X">
                        <p>Scolio-X</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/sports-physiowerkz/">
                        <img src="images/sports.jpeg" alt="Sports">
                        <p>Sports</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/mobifit/">
                        <img src="images/mobifit.jpeg" alt="MOBIFIT">
                        <p>MOBIFIT</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/dra/">
                        <img src="images/dra.jpeg" alt="DRA">
                        <p>DRA</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/infitnitum/">
                        <img src="images/infitnitum.jpeg" alt="inFITnitum">
                        <p>inFITnitum</p>
                    </a>
                </div>
            </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>