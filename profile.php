<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

$username = $_SESSION["username"];
$role = $_SESSION["role"];
$table = ($role == 'patient') ? 'patients' : 'staffs';

// Fetch user data from the database
$sql = "SELECT username, email, gender, first_name, last_name, dob, contact_num";
if ($role == 'patient') {
    $sql .= ", personality, compliance, bodytype, focus, lifestyle, healthstatus, goal, outcome";
}
if ($role != 'patient') {
    $sql .= ", role";
}
$sql .= " FROM $table WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

if ($role == 'patient') {
    // Assuming 'outcome' field holds the comma-separated list of selected options
    // Trim each value to avoid matching issues
    $userOptions = array_map('trim', explode(',', $user['outcome']));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $contact_num = $_POST['contact_num'];

    // Only handle these fields if the user role is 'patient'
    if ($role == 'patient') {
        $personality = $_POST['personality'];
        $compliance = $_POST['compliance'];
        $bodytype = $_POST['bodytype'];
        $focus = $_POST['focus'];
        $lifestyle = $_POST['lifestyle'];
        $healthstatus = $_POST['healthstatus'];
        $goal = $_POST['goal'];

        // Handling the "outcome" checkbox selection
        if (isset($_POST['options'])) {
            // Convert the array of selected options into a comma-separated string
            $outcome = implode(', ', $_POST['options']);
        } else {
            $outcome = ''; // If no checkboxes were selected, store an empty string
        }

        // Add all fields including "outcome" if patient
        $sql = "UPDATE $table SET email=?, gender=?, first_name=?, last_name=?, dob=?, contact_num=?, 
                personality=?, compliance=?, bodytype=?, focus=?, lifestyle=?, healthstatus=?, goal=?, outcome=? 
                WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssssss",
            $email,
            $gender,
            $first_name,
            $last_name,
            $dob,
            $contact_num,
            $personality,
            $compliance,
            $bodytype,
            $focus,
            $lifestyle,
            $healthstatus,
            $goal,
            $outcome,
            $username
        );
    } else {
        // Update fields excluding the patient-specific ones
        $sql = "UPDATE $table SET email=?, gender=?, first_name=?, last_name=?, dob=?, contact_num=? WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssss",
            $email,
            $gender,
            $first_name,
            $last_name,
            $dob,
            $contact_num,
            $username
        );
    }

    if ($stmt->execute()) {
        echo "<script>
                alert('Profile updated successfully.');
                window.location.href='profile.php';
              </script>";
    } else {
        echo "<script>alert('Error updating profile: " . $stmt->error . "');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="images/logo.png" rel="icon">
    <style>
        footer {
            background: #F9FAFC;
            color: #000;
            padding: 20px 0;
            position: relative;
            width: 100%;
            bottom: 0;
        }

        .footer-container {
            width: 80%;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
        }

        .footer-container div {
            width: 30%;
        }

        .social-links a {
            color: #000;
            text-decoration: none;
            margin-right: 10px;
        }

        .social-links a:hover {
            text-decoration: underline;
        }

        .book-now-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .book-now-button:hover {
            background-color: #218838;
        }

        html,
        body {
            height: 100%;
            /* Full height for the html and body */
            margin: 0;
            /* Remove default margin */
            display: flex;
            flex-direction: column;
            /* Stack header, main, and footer vertically */
            font-family: Arial, sans-serif;
            padding: 0;
            box-sizing: border-box;
        }

        /* Profile Title */
        .profile-title {
            font-size: 28px;
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
            font-weight: bold;
        }

        /* Form Container */
        .profile-form {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Grid Layout for Form Fields */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .profile-form-group {
            display: flex;
            flex-direction: column;
        }

        .profile-form-group label {
            font-size: 16px;
            margin-bottom: 8px;
            color: #34495e;
        }

        .profile-form-group input,
        .profile-form-group select {
            padding: 10px;
            font-size: 16px;
            border: 2px solid #bdc3c7;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }

        /* Styling for Non-Editable Fields */
        .profile-non-editable {
            background-color: #ecf0f1;
            border-color: #bdc3c7;
        }

        /* Link Styling */
        .profile-link {
            font-size: 14px;
            color: #3498db;
            text-decoration: none;
        }

        .profile-link:hover {
            text-decoration: underline;
        }

        /* Responsive Design for Smaller Screens */
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Container for the checkbox group */
        .profile-form-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        /* Each option container */
        .profile-option {
            display: flex;
            align-items: center;
        }

        /* Checkbox styling */
        .profile-option input[type="checkbox"] {
            margin-right: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        /* Label styling */
        .profile-option label {
            font-size: 16px;
            color: #34495e;
            cursor: pointer;
        }

        /* Responsive layout for smaller screens */
        @media (max-width: 768px) {
            .profile-form-options {
                grid-template-columns: 1fr;
            }
        }


        /* Buttons */
        .profile-button {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease;
            margin-right: 10px;
        }

        .profile-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>
    <main>
        <h1 class="profile-title">Your Profile</h1>
        <form class="profile-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="profile-grid">
                <div class="profile-form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" value="<?php echo $user['username']; ?>" class="profile-non-editable" readonly>
                </div>
                <div class="profile-form-group">
                    <label for="password">Password:</label>
                    <a href="reset_password.php" class="profile-link">Change Password</a>
                </div>
                <div class="profile-form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                <div class="profile-form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="Male" <?php if ($user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div class="profile-form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                </div>
                <div class="profile-form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                </div>
                <div class="profile-form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?php echo $user['dob']; ?>" required>
                </div>
                <div class="profile-form-group">
                    <label for="contact_num">Contact Number:</label>
                    <input type="tel" id="contact_num" name="contact_num" value="<?php echo $user['contact_num']; ?>" required>
                </div>
            </div>

            <?php if ($role == 'patient') { ?>
                <br>
                <div class="profile-grid">
                    <div class="profile-form-group">
                        <label for="personality">Personality:</label>
                        <select id="personality" name="personality" required>
                            <option value="" disabled selected>Please select your Personality</option>
                            <option value="Type A" <?php if ($user['personality'] == 'Type A') echo 'selected'; ?>>Type A</option>
                            <option value="Type B" <?php if ($user['personality'] == 'Type B') echo 'selected'; ?>>Type B</option>
                            <option value="Type C" <?php if ($user['personality'] == 'Type C') echo 'selected'; ?>>Type C</option>
                        </select>
                    </div>
                    <div class="profile-form-group">
                        <label for="compliance">Compliance if convinced:</label>
                        <select id="compliance" name="compliance" required>
                            <option value="" disabled selected>Please select Compliance</option>
                            <option value="Yes" <?php if ($user['compliance'] == 'Yes') echo 'selected'; ?>>Yes</option>
                            <option value="No" <?php if ($user['compliance'] == 'No') echo 'selected'; ?>>No</option>
                        </select>
                    </div>
                    <div class="profile-form-group">
                        <label for="bodytype">Body Type:</label>
                        <select id="bodytype" name="bodytype" required>
                            <option value="" disabled selected>Please select Body Type</option>
                            <option value="Restricted" <?php if ($user['bodytype'] == 'Restricted') echo 'selected'; ?>>Restricted</option>
                            <option value="Meso/Muscular" <?php if ($user['bodytype'] == 'Meso/Muscular') echo 'selected'; ?>>Meso/Muscular</option>
                        </select>
                    </div>
                    <div class="profile-form-group">
                        <label for="focus">Focus:</label>
                        <select id="focus" name="focus" required>
                            <option value="" disabled selected>Please select Focus</option>
                            <option value="Open" <?php if ($user['focus'] == 'Open') echo 'selected'; ?>>Open</option>
                            <option value="Big" <?php if ($user['focus'] == 'Big') echo 'selected'; ?>>Big</option>
                            <option value="Mod speed" <?php if ($user['focus'] == 'Mod speed') echo 'selected'; ?>>Mod speed</option>
                        </select>
                    </div>
                    <div class="profile-form-group">
                        <label for="lifestyle">Lifestyle:</label>
                        <input type="text" id="lifestyle" name="lifestyle" value="<?php echo $user['lifestyle']; ?>" placeholder="e.g., Manager, Sit>4hrs, Exs++ (wts)" required>
                    </div>
                    <div class="profile-form-group">
                        <label for="healthstatus">Unhealthy, Risks, Clear:</label>
                        <select id="healthstatus" name="healthstatus" required>
                            <option value="" disabled selected>Please select Health Status</option>
                            <option value="Unhealthy" <?php if ($user['healthstatus'] == 'Unhealthy') echo 'selected'; ?>>Unhealthy</option>
                            <option value="Risks" <?php if ($user['healthstatus'] == 'Risks') echo 'selected'; ?>>Risks</option>
                            <option value="Clear" <?php if ($user['healthstatus'] == 'Clear') echo 'selected'; ?>>Clear</option>
                        </select>
                    </div>
                </div>
                <br>
                <div class="profile-form-group">
                    <label for="goal">Goals and Outcome:</label>
                    <textarea id="goal" name="goal" rows="4" placeholder="Describe your goals and expected outcomes"><?php echo $user['goal']; ?></textarea>
                </div>
                <br>
                <div class="profile-form-group">
                    <label>Choose your options:</label>
                    <div class="profile-form-options">
                        <div class="profile-option">
                            <input type="checkbox" id="breathe" name="options[]" value="Breathe"
                                <?php if (in_array('Breathe', $userOptions)) echo 'checked'; ?>>
                            <label for="breathe">Breathe</label>
                        </div>
                        <div class="profile-option">
                            <input type="checkbox" id="set-alarm" name="options[]" value="Set Alarm"
                                <?php if (in_array('Set Alarm', $userOptions)) echo 'checked'; ?>>
                            <label for="set-alarm">Set Alarm</label>
                        </div>
                        <div class="profile-option">
                            <input type="checkbox" id="hydrate" name="options[]" value="Hydrate"
                                <?php if (in_array('Hydrate', $userOptions)) echo 'checked'; ?>>
                            <label for="hydrate">Hydrate</label>
                        </div>
                        <div class="profile-option">
                            <input type="checkbox" id="meditate" name="options[]" value="Meditate"
                                <?php if (in_array('Meditate', $userOptions)) echo 'checked'; ?>>
                            <label for="meditate">Meditate</label>
                        </div>
                        <div class="profile-option">
                            <input type="checkbox" id="exercise" name="options[]" value="Exercise"
                                <?php if (in_array('Exercise', $userOptions)) echo 'checked'; ?>>
                            <label for="exercise">Exercise</label>
                        </div>
                        <div class="profile-option">
                            <input type="checkbox" id="stretch" name="options[]" value="Stretch"
                                <?php if (in_array('Stretch', $userOptions)) echo 'checked'; ?>>
                            <label for="stretch">Stretch</label>
                        </div>
                        <div class="profile-option">
                            <input type="checkbox" id="sleep" name="options[]" value="Sleep"
                                <?php if (in_array('Sleep', $userOptions)) echo 'checked'; ?>>
                            <label for="sleep">Sleep</label>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if (in_array($role, ['admin', 'hr', 'physiotherapist', 'trainer'])) { ?>
                <div class="profile-form-group">
                    <label for="role">Role:</label>
                    <input type="text" id="role" value="<?php echo $user['role']; ?>" class="profile-non-editable" readonly>
                </div>
            <?php } ?>
            <button type="submit" class="profile-button">Update Profile</button>
            <button type="button" class="profile-button" onclick="window.history.back();">Done</button>
        </form>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>