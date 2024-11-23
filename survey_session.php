<?php
// Initialize the session
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

// Default value for full name
$full_name = '';

// Fetch user details from the database
if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];

    $sql = "SELECT first_name, last_name FROM patients WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            $stmt->bind_result($first_name, $last_name);
            $stmt->fetch();

            // Combine first name and last name
            $full_name = $first_name . ' ' . $last_name;
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

        $stmt->close();
    }
}
// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect responses from the form
    $progress = $_POST['progress'];
    $therapist_rating = $_POST['therapist_rating'];
    $therapist_feedback = $_POST['therapist_feedback'];
    $trainer_input = $_POST['trainer_input'];
    $goals = $_POST['goals'];
    $treatment_plan = $_POST['treatment_plan'];
    $overall_experience = $_POST['overall_experience'];
    $recommend = $_POST['recommend'];
    $patient_id = $_SESSION['id']; // Assuming patient ID is stored in session

    // Retrieve session_count from the patients table
    $session_query = "SELECT session_count FROM patients WHERE patient_id = ?";
    $session_stmt = $conn->prepare($session_query);
    $session_stmt->bind_param("i", $patient_id);
    $session_stmt->execute();
    $session_stmt->bind_result($session_num);
    $session_stmt->fetch();
    $session_stmt->close();

    // Null check for session_num
    if (is_null($session_num)) {
        // Handle null session_num (e.g., display error or set a default value)
        echo "Error: Session count not found for this patient.";
        exit; // Stop further processing if session_num is null
    }

    // Insert data into the new survey table, including session_num
    $query = "INSERT INTO treatment_survey (patient_id, session_num, progress, therapist_rating, therapist_feedback, trainer_input, goals, treatment_plan, overall_experience, recommend) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissssssss", $patient_id, $session_num, $progress, $therapist_rating, $therapist_feedback, $trainer_input, $goals, $treatment_plan, $overall_experience, $recommend);
    $stmt->execute();

    // Redirect or display a confirmation message
    echo "<script>alert('Thank you for completing the survey!'); window.location.href='welcome.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Questionnaire</title>
    <link href="images/logo.png" rel="icon">
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

        /* Styling for the textarea (feedback box) */
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            line-height: 1.5;
            resize: vertical;
            /* Allow vertical resizing only */
        }

        textarea:focus {
            border-color: #66afe9;
            outline: none;
            box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.1), 0 0 8px rgba(102, 175, 233, 0.6);
        }

        .therapist-rating {
            display: flex;
            align-items: center;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
        }

        .star-rating label {
            font-size: 24px;
            color: #ccc;
            /* Default color */
            cursor: pointer;
        }

        .star-rating input {
            display: none;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #f5a623;
            /* Gold color for selected/hovered stars */
        }
    </style>
</head>
<?php include 'includes/header_patient.php'; ?>

<body>
    <h2>Follow-up Treatment Questionnaire</h2>
    <form action="survey_session.php" method="post">

        <h2>Follow-up Treatment Questionnaire</h2>
        <label>Are you happy with your progress?</label>
        <textarea name="progress" required></textarea><br><br>

        <label>Are you happy with your current therapist?</label>
        <div class="therapist-rating">
            <label for="therapist_rating">Therapist Rating:</label>
            <div class="star-rating">
                <input type="radio" name="therapist_rating" id="rating1" value="1"><label for="rating1">★</label>
                <input type="radio" name="therapist_rating" id="rating2" value="2"><label for="rating2">★</label>
                <input type="radio" name="therapist_rating" id="rating3" value="3"><label for="rating3">★</label>
                <input type="radio" name="therapist_rating" id="rating4" value="4"><label for="rating4">★</label>
                <input type="radio" name="therapist_rating" id="rating5" value="5"><label for="rating5">★</label>
            </div>
        </div>

        <textarea name="therapist_feedback" placeholder="Additional comments"></textarea><br><br>

        <label>Are you happy with the process with input from trainers?</label>
        <textarea name="trainer_input" required></textarea><br><br>

        <label>What are your goals?</label>
        <textarea name="goals" required></textarea><br><br>

        <label>Was your treatment plan clearly communicated to you?</label>
        <textarea name="treatment_plan" required></textarea><br><br>

        <label>What’s your overall experience (environment, customer service)?</label>
        <textarea name="overall_experience" required></textarea><br><br>

        <label>Would you recommend us to your friends?</label>
        <textarea name="recommend" required></textarea><br><br>

        <button type="submit">Submit</button>
    </form>
</body>

</html>