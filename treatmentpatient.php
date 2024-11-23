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

include 'includes/header_patient.php';

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

$patient_id = $_SESSION["id"]; // Ensure this session variable is set upon login
// Fetch the account creation date from the patients table
$created_at = '';
$days_with_physiowerkz = 0;
if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];

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
                $days_with_physiowerkz = $interval->days;
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

        $stmt->close();
    }
}

// Fetch doctors for the dropdown
$doctors = [];
$sql = "SELECT staff_id, CONCAT(first_name, ' ', last_name) AS name FROM staffs WHERE role = 'physiotherapist'";
if ($stmt = $conn->prepare($sql)) {
    if ($stmt->execute()) {
        $stmt->store_result();
        $stmt->bind_result($id, $name);
        while ($stmt->fetch()) {
            $doctors[] = ['id' => $id, 'name' => $name];
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}

$patient_id = $_SESSION["id"];
// Assume $patient_id is already set from session or passed via GET/POST
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
            'staff_id' => $row_appointment['staff_id'],  // Add staff_id (therapist)
            'status' => $row_appointment['status'],
            'patient_comments' => $row_appointment['patient_comments'],
            'staff_comments' => $row_appointment['staff_comments'],
        ];
    }
}
$stmt_appointments->close();

// Handle file upload
if (isset($_POST['submit'])) {
    $maxsize = 5242880; // 5MB in bytes

    if (isset($_FILES['video']['name']) && $_FILES['video']['name'] != '') {
        $name = $_FILES['video']['name'];
        $target_dir = "uploads/" . $_SESSION["username"] . "/";
        $target_file = $target_dir . $name;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $extensions_arr = array("mp4", "avi", "3gp", "mov", "mpeg");

        if (in_array($extension, $extensions_arr)) {
            if ($_FILES['video']['size'] >= $maxsize) {
                echo "<script>
                        alert('File too large. File must be less than 5MB.');
                        window.location.href='treatmentpatient.php';
                      </script>";
            } else {
                if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
                    $doctor_id = $_POST['doctor_id'];
                    $appointment_id = $_POST['appointment_session']; // Get appointment_id from the form


                    $sql = "INSERT INTO videos (username, name, location, doctor_id, appointment_id, patient_id) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("sssiii", $_SESSION["username"], $name, $target_file, $doctor_id, $appointment_id, $patient_id);

                        if ($stmt->execute()) {
                            echo "<script>
                                    alert('Upload successful.');
                                    window.location.href='treatmentpatient.php';
                                  </script>";
                        } else {
                            echo "<script>
                                    alert('Database error.');
                                    window.location.href='treatmentpatient.php';
                                  </script>";
                        }
                        $stmt->close();
                    }
                } else {
                    echo "<script>
                            alert('Upload failed.');
                            window.location.href='treatmentpatient.php';
                          </script>";
                }
            }
        } else {
            echo "<script>
                    alert('Invalid file extension.');
                    window.location.href='treatmentpatient.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Please select a file.');
                window.location.href='treatmentpatient.php';
              </script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment [Patient]</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        .main-content-container {
            max-width: 800px;
            width: 100%;
            background-color: white;
            padding: 40px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .welcome-message h1 {
            margin-top: 40px;
            color: #003366;
        }

        .video-item {
            margin-bottom: 20px;
        }

        .video-item video {
            width: 100%;
            max-width: 400px;
            /* Set a max width for the videos */
            height: auto;
            /* Maintain aspect ratio */
        }

        .video-preview video {
            width: 100%;
            max-width: 400px;
            height: auto;
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .upload-form label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .upload-form input[type="file"],
        .upload-form select {
            padding: 10px;
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .upload-form input[type="submit"] {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .upload-form input[type="submit"]:hover {
            background-color: #005f6b;
        }

        /* Back button styles */
        .back-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #008CBA;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }

        .back-button:hover {
            background-color: #005f6b;
        }

        .file-hint {
            font-size: 0.9em;
            color: #555;
            margin-top: -10px;
            margin-bottom: 15px;
            text-align: left;
        }
    </style>
</head>

<body>

    <main class="main-content-container">
        <div class="welcome-message">
            <h1>Hi <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <p>Thank you for choosing Physiowerkz for your physiotherapy journey.</p>
            <p>You're making great progress—it's been <strong><?php echo $days_with_physiowerkz; ?> days</strong> since you started. Please upload a video of your progress below to help us monitor your improvements.</p>
        </div>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>

        <form method="post" action="" enctype="multipart/form-data" class="upload-form">
            <label for="video">Choose video to upload:</label>
            <input type="file" name="video" id="video" accept="video/*" required onchange="previewVideo(event)">
            <p class="file-hint">Supported file types: MP4, MOV, AVI. Maximum size: 5MB.</p>
            <p class="file-hint">[If file size exceed limit, please trim/compress the video.]</p>
            <div class="video-preview" id="video-preview">
                <!-- Video preview will be shown here -->
            </div>

            <!-- Select appointment session based on patient -->
            <label for="appointment_session">Choose an Appointment Session:</label>
            <select name="appointment_session" id="appointment_session" required>
                <option value="" disabled selected>Select an Appointment Session</option>
                <?php foreach ($appointments as $appointment) : ?>
                    <option value="<?php echo htmlspecialchars($appointment['appointment_id']); ?>"
                        data-therapist-id="<?php echo htmlspecialchars($appointment['staff_id']); ?>">
                        Appointment #<?php echo htmlspecialchars($appointment['appointment_id']); ?> on
                        <?php echo htmlspecialchars($appointment['appointment_datetime']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Select therapist (prefilled based on session) -->
            <label for="doctor">Choose your Physiotherapist:</label>
            <select name="doctor_id" id="doctor" required>
                <option value="" disabled selected>Select a Physiotherapist</option>
                <?php foreach ($doctors as $doctor) : ?>
                    <option value="<?php echo htmlspecialchars($doctor['id']); ?>">
                        <?php echo htmlspecialchars($doctor['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            </br>
            <input type="submit" name="submit" value="Upload Video">
        </form>

        <!-- Display uploaded videos -->
        <div class="uploaded-videos">
            <h2>Your Uploaded Videos</h2>
            <?php
            $video_dir = "uploads/" . $_SESSION["username"] . "/";
            $videos = glob($video_dir . "*.mp4");
            if ($videos) {
                foreach ($videos as $video) {
                    echo '<div class="video-item">';
                    echo '<video controls>';
                    echo '<source src="' . $video . '" type="video/mp4">';
                    echo 'Your browser does not support the video tag.';
                    echo '</video>';
                    echo '</div>';
                }
            } else {
                echo '<p>No videos uploaded yet.</p>';
            }
            ?>
        </div>

        <!-- Back Button -->
        <button onclick="window.location.href='welcome.php'" class="back-button">Go Back</button>
    </main>

    <script>
        function previewVideo(event) {
            var file = event.target.files[0];

            // Check file size (in bytes)
            var maxSize = 100 * 1024 * 1024; // 100MB
            if (file.size > maxSize) {
                alert('File size exceeds 100MB. Please choose a smaller file.');
                event.target.value = ""; // Clear the input field
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                var videoPreview = document.getElementById('video-preview');
                videoPreview.innerHTML = '<video controls><source src="' + e.target.result + '" type="video/mp4">Your browser does not support the video tag.</video>';
            }
            reader.readAsDataURL(file);
        }
        // When the user selects an appointment session, prefill the therapist dropdown
        document.getElementById('appointment_session').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const therapistId = selectedOption.getAttribute('data-therapist-id');

            // Prefill the therapist dropdown
            const therapistSelect = document.getElementById('doctor');

            if (therapistId) {
                for (let i = 0; i < therapistSelect.options.length; i++) {
                    if (therapistSelect.options[i].value == therapistId) {
                        therapistSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>