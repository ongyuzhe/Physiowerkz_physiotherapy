<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$valid_roles = ['admin', 'physiotherapist'];
$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($role, $valid_roles)) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

if ($role === 'admin') {
    include 'includes/header_admin.php';
} elseif ($role === 'physiotherapist') {
    include 'includes/header_staff.php';
}
// Ensure lock_status is set for current user session
$lock_status = $_SESSION['lock_status'] ?? 'No';
if ($lock_status === 'Yes') {
    echo "<script>alert('You are restricted from upload video due to your lock status.');</script>";
}

// Get the logged-in staff's ID from the session
$staff_id = $_SESSION["id"]; // Ensure this session variable is set upon login

// Initialize arrays for videos
$videos = [];  // For patient videos in 'uploads/'
$videosp = []; // For therapist videos in 'uploads/notpatient/'

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $username = $_POST['username'];
    $appointment_id = $_POST['appointment_id'];

    // First Query: Fetch Patient Videos (stored in 'uploads/')
    if ($role === 'admin') {
        // Admins get all patient videos for the given username and appointment_id
        $sql_patient = "SELECT id, username, name, location FROM videos 
                        WHERE appointment_id = ? AND username = ? AND location LIKE 'uploads/%'  AND location NOT LIKE 'uploads/notpatient/%'";
        $stmt_patient = $conn->prepare($sql_patient);
        $stmt_patient->bind_param("is", $appointment_id, $username);
    } else {
        // Physiotherapists get only their assigned patient's videos
        $sql_patient = "SELECT id, username, name, location FROM videos 
                        WHERE appointment_id = ? AND username = ? AND doctor_id = ? AND location LIKE 'uploads/%' AND location NOT LIKE 'uploads/notpatient/%'";
        $stmt_patient = $conn->prepare($sql_patient);
        $stmt_patient->bind_param("isi", $appointment_id, $username, $staff_id);
    }

    // Execute the patient query
    if ($stmt_patient && $stmt_patient->execute()) {
        $stmt_patient->store_result();
        $stmt_patient->bind_result($id, $username, $name, $location);
        while ($stmt_patient->fetch()) {
            $videos[] = [
                'id' => $id,
                'username' => $username,
                'name' => $name,
                'location' => $location
            ];
        }
    } else {
        echo "Error fetching patient videos. Please try again later.";
    }
    $stmt_patient->close();
    // Second Query: Fetch Physiotherapist Videos (stored in 'uploads/notpatient/')
    if ($role === 'admin') {
        // Admins get all physiotherapist videos for the given username and appointment_id
        $sql_therapist = "SELECT id, username, name, location FROM videos 
                      WHERE appointment_id = ? AND username = ? AND location LIKE 'uploads/notpatient/%'";
        $stmt_therapist = $conn->prepare($sql_therapist);
        $stmt_therapist->bind_param("is", $appointment_id, $username);
    } else {
        // Physiotherapists only get videos for their assigned patients
        $sql_therapist = "SELECT id, username, name, location FROM videos 
                      WHERE appointment_id = ? AND username = ? AND doctor_id = ? AND location LIKE 'uploads/notpatient/%'";
        $stmt_therapist = $conn->prepare($sql_therapist);
        $stmt_therapist->bind_param("isi", $appointment_id, $username, $staff_id);
    }

    // Execute the therapist query
    if ($stmt_therapist && $stmt_therapist->execute()) {
        $stmt_therapist->store_result();
        $stmt_therapist->bind_result($id, $username, $name, $location);
        while ($stmt_therapist->fetch()) {
            $videosp[] = [
                'id' => $id,
                'username' => $username,
                'name' => $name,
                'location' => $location
            ];
        }
    } else {
        echo "Error fetching therapist videos. Please try again later.";
    }
    $stmt_therapist->close();
}

// Handle form actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $video_id = $_POST['video_id'];
    $additional_info = $_POST['additional_info']; // New field for additional info

    // Request admin approval for deletion
    $sql = "UPDATE videos SET approved = 1, log = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $additional_info, $video_id);
        if ($stmt->execute()) {
            echo "<script>
                  alert('Video deletion request submitted for admin approval.');
                  window.location.href='view_treatment.php';
                  </script>";
        } else {
            echo "<script>
                  alert('Error requesting video deletion.');
                  window.location.href='view_treatment.php';
                  </script>";
        }
        $stmt->close();
    }
}

// Check if the video upload form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["video_file"]) && isset($_POST['upload_therapist'])) {

    // Retrieve username and appointment_id from POST or session
    $username = $_POST['username'] ?? '';
    $appointment_id = $_POST['appointment_id'] ?? '';


    // Ensure the necessary data is available
    if ($username && $appointment_id) {

        // Fetch patient_id and therapist_id from the appointments table
        $sql_appointment = "SELECT patient_id, therapist_id FROM appointments WHERE appointment_id = ?";
        if ($stmt_appointment = $conn->prepare($sql_appointment)) {
            $stmt_appointment->bind_param("i", $appointment_id);
            $stmt_appointment->execute();
            $stmt_appointment->store_result();
            $stmt_appointment->bind_result($patient_id, $therapist_id);
            $stmt_appointment->fetch();
            $stmt_appointment->close();

            // Check if we retrieved valid patient_id and therapist_id
            if (!empty($patient_id) && !empty($therapist_id)) {

                // Set the target directory to "uploads/username"
                $target_dir = "uploads/notpatient/" . $username;

                // Ensure the directory exists
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true); // Creates directory if it doesn’t exist
                }

                // Generate a unique name for the uploaded video file
                $video_name = basename($_FILES["video_file"]["name"]);
                $target_file = $target_dir . "/" . uniqid() . "_" . $video_name;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {

                    // Insert video info into the videos table
                    $sql_video = "INSERT INTO videos (username, appointment_id, patient_id, doctor_id, name, location) VALUES (?, ?, ?, ?, ?, ?)";
                    if ($stmt_video = $conn->prepare($sql_video)) {
                        $stmt_video->bind_param("siisss", $username, $appointment_id, $patient_id, $therapist_id, $video_name, $target_file);

                        if ($stmt_video->execute()) {
                            echo "<script>
                                  alert('Video uploaded successfully.');
                                  window.location.href='view_treatment.php';
                                  </script>";
                        } else {
                            echo "<script>
                                  alert('Error saving video info to the database.');
                                  window.location.href='view_treatment.php';
                                  </script>";
                        }
                        $stmt_video->close();
                    }
                } else {
                    echo "<script>
                          alert('Error uploading the video.');
                          window.location.href='view_treatment.php';
                          </script>";
                }
            } else {
                echo "<script>
                      alert('Invalid appointment ID or missing patient/therapist information.');
                      window.location.href='view_treatment.php';
                      </script>";
            }
        }
    } else {
        echo "<script>
              alert('Missing required user information.');
              window.location.href='view_treatment.php';
              </script>";
    }
}
// patient
// Check if the video upload form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["video_file"]) && isset($_POST['upload_patient'])) {

    // Retrieve username and appointment_id from POST or session
    $username = $_POST['username'] ?? '';
    $appointment_id = $_POST['appointment_id'] ?? '';


    // Ensure the necessary data is available
    if ($username && $appointment_id) {

        // Fetch patient_id and therapist_id from the appointments table
        $sql_appointment = "SELECT patient_id, therapist_id FROM appointments WHERE appointment_id = ?";
        if ($stmt_appointment = $conn->prepare($sql_appointment)) {
            $stmt_appointment->bind_param("i", $appointment_id);
            $stmt_appointment->execute();
            $stmt_appointment->store_result();
            $stmt_appointment->bind_result($patient_id, $therapist_id);
            $stmt_appointment->fetch();
            $stmt_appointment->close();

            // Check if we retrieved valid patient_id and therapist_id
            if (!empty($patient_id) && !empty($therapist_id)) {

                // Set the target directory to "uploads/username"
                $target_dir = "uploads/" . $username;

                // Ensure the directory exists
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true); // Creates directory if it doesn’t exist
                }

                // Generate a unique name for the uploaded video file
                $video_name = basename($_FILES["video_file"]["name"]);
                $target_file = $target_dir . "/" . uniqid() . "_" . $video_name;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {

                    // Insert video info into the videos table
                    $sql_video = "INSERT INTO videos (username, appointment_id, patient_id, doctor_id, name, location) VALUES (?, ?, ?, ?, ?, ?)";
                    if ($stmt_video = $conn->prepare($sql_video)) {
                        $stmt_video->bind_param("siisss", $username, $appointment_id, $patient_id, $therapist_id, $video_name, $target_file);

                        if ($stmt_video->execute()) {
                            echo "<script>
                                  alert('Video uploaded successfully.');
                                  window.location.href='view_treatment.php';
                                  </script>";
                        } else {
                            echo "<script>
                                  alert('Error saving video info to the database.');
                                  window.location.href='view_treatment.php';
                                  </script>";
                        }
                        $stmt_video->close();
                    }
                } else {
                    echo "<script>
                          alert('Error uploading the video.');
                          window.location.href='view_treatment.php';
                          </script>";
                }
            } else {
                echo "<script>
                      alert('Invalid appointment ID or missing patient/therapist information.');
                      window.location.href='view_treatment.php';
                      </script>";
            }
        }
    } else {
        echo "<script>
              alert('Missing required user information.');
              window.location.href='view_treatment.php';
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Videos - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        .hidden-form {
            max-width: none;
            padding: 0;
            background-color: transparent;
            border: none;
            box-shadow: none;
        }

        .video-container {
            max-width: 800px;
            margin: 0 auto;
            margin-top: 100px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }

        .video-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .video-item {
            background-color: #f8f8f8;
            border-radius: 8px;
            padding: 15px;
            width: 100%;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .video-item h2 {
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .video-item video {
            width: 100%;
            border-radius: 8px;
        }

        .video-item button {
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .delete {
            background-color: #ff4d4d;
        }

        .butt {
            background-color: #0287d4;
        }

        .butt:hover {
            background-color: #0066a1;
        }

        .delete:hover {
            background-color: #e60000;
        }

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
    </style>
    <script>
        function showDeletePopup(videoId) {
            var reason = prompt("Please enter the reason for deletion:");
            if (reason !== null && reason.trim() !== "") {
                document.getElementById('video_id').value = videoId;
                document.getElementById('additional_info').value = reason;
                document.getElementById('delete_form').submit();
            }
        }

        function navigateToPage(page) {
            // Set the form action to the desired page
            document.getElementById('navigationForm').action = page;
            // Submit the form
            document.getElementById('navigationForm').submit();
        }
    </script>
</head>

<body>

    <main class="video-container">
        <h1>Physio Videos</h1>
        <!-- Video Upload Form -->
        <form class="video-item" method="post" enctype="multipart/form-data">
            <h2>Upload Video as Physiotherapist</h2>
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($_POST['appointment_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <label for="video_file">Choose Video:</label>
            <input type="file" name="video_file" accept="video/*" required>
            <button class="butt" type="submit" name="upload_therapist" <?php if ($_SESSION['lock_status'] === 'Yes')
                                                                            echo 'disabled title="Your account is locked and cannot upload videos."'; ?>>
                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Upload Video'; ?>
            </button>

        </form>

        <!-- Display existing videos -->
        <?php if ($videosp): ?>
            <?php foreach ($videosp as $video): ?>
                <div class="video-item">
                    <h2>Video for physiotherapist</h2>
                    <video controls>
                        <source src="<?php echo htmlspecialchars($video['location']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <form method="post" id="delete_form" style="display: none;">
                        <input type="hidden" id="video_id" name="video_id">
                        <input type="hidden" id="additional_info" name="additional_info">
                        <input type="hidden" name="action" value="<?php echo ($_SESSION['lock_status'] === 'Yes') ? 'locked' : 'delete'; ?>">
                    </form>
                    <?php if ($_SESSION['lock_status'] === 'Yes'): ?>
                        <button class="delete" type="button" disabled title="Account locked. Cannot request deletion.">Locked</button>
                    <?php else: ?>
                        <button class="delete" type="button" onclick="showDeletePopup(<?php echo htmlspecialchars($video['id']); ?>)">Request Deletion</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No videos available at the moment.</p>
        <?php endif; ?>

        <h1>Patient Videos</h1>
        <!-- Video Upload Form -->
        <form class="video-item" method="post" enctype="multipart/form-data">
            <h2>Upload Video for Patient</h2>
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($_POST['appointment_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <label for="video_file">Choose Video:</label>
            <input type="file" name="video_file" accept="video/*" required>
            <button class="butt" type="submit" name="upload_patient" <?php if ($_SESSION['lock_status'] === 'Yes')
                                                                            echo 'disabled title="Your account is locked and cannot upload videos."'; ?>>
                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Upload Video'; ?>
            </button>

        </form>

        <!-- Display existing videos -->
        <?php if ($videos): ?>
            <?php foreach ($videos as $video): ?>
                <div class="video-item">
                    <h2>Video from <?php echo htmlspecialchars($video['username']); ?></h2>
                    <video controls>
                        <source src="<?php echo htmlspecialchars($video['location']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <form method="post" id="delete_form" style="display: none;">
                        <input type="hidden" id="video_id" name="video_id">
                        <input type="hidden" id="additional_info" name="additional_info">
                        <input type="hidden" name="action" value="<?php echo ($_SESSION['lock_status'] === 'Yes') ? 'locked' : 'delete'; ?>">
                    </form>
                    <?php if ($_SESSION['lock_status'] === 'Yes'): ?>
                        <button class="delete" type="button" disabled title="Account locked. Cannot request deletion.">Locked</button>
                    <?php else: ?>
                        <button class="delete" type="button" onclick="showDeletePopup(<?php echo htmlspecialchars($video['id']); ?>)">Request Deletion</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No videos available at the moment.</p>
        <?php endif; ?>

        <button onclick="window.location.href='view_treatment.php'" class="back-button">Back to Treatments</button>

        <div>
            <!-- Hidden Form -->
            <form id="navigationForm" class="hidden-form" method="POST" action="">
                <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($_POST['patient_id']); ?>">
                <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($_POST['appointment_id']); ?>">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($_POST['username']); ?>">
            </form>

            <!-- Floating Buttons -->
            <div class="floating-button left"
                style="position: fixed; top: 50%; left: 10px; transform: translateY(-50%); cursor: pointer; z-index: 1000;"
                onclick="navigateToPage('treatmentdoctor3.php')">
                <img src="images/left-arrow.png" alt="Previous" style="width: 50px; height: 50px;">
            </div>

            <div class="floating-button right"
                style="position: fixed; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; z-index: 1000;"
                onclick="navigateToPage('treatmentdoctor1.php')">
                <img src="images/right-arrow.png" alt="Next" style="width: 50px; height: 50px;">
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>