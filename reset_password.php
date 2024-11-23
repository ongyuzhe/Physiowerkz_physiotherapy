<?php
require 'includes/settings.php'; // Adjust as per your setup

// Initialize variables
$token = isset($_GET['token']) ? base64_decode($_GET['token']) : '';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Verify the token
        $sql_token = "SELECT email FROM password_resets WHERE token = ? AND token_expiry > NOW()";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->bind_param("s", $token);
        $stmt_token->execute();
        $result_token = $stmt_token->get_result();
        $stmt_token->close();

        if ($result_token->num_rows > 0) {
            $row = $result_token->fetch_assoc();
            $email = $row['email'];

            // Check if the email exists in patients table
            $sql_patients = "SELECT patient_id FROM patients WHERE email = ?";
            $stmt_patients = $conn->prepare($sql_patients);
            $stmt_patients->bind_param("s", $email);
            $stmt_patients->execute();
            $result_patients = $stmt_patients->get_result();
            $stmt_patients->close();

            // Check if the email exists in staffs table
            $sql_staffs = "SELECT staff_id FROM staffs WHERE email = ?";
            $stmt_staffs = $conn->prepare($sql_staffs);
            $stmt_staffs->bind_param("s", $email);
            $stmt_staffs->execute();
            $result_staffs = $stmt_staffs->get_result();
            $stmt_staffs->close();

            if ($result_patients->num_rows > 0) {
                // Update the password in the patients table
                $sql_update_patients = "UPDATE patients SET password = ? WHERE email = ?";
                $stmt_update_patients = $conn->prepare($sql_update_patients);
                $stmt_update_patients->bind_param("ss", $hashed_password, $email);
                $stmt_update_patients->execute();
                $stmt_update_patients->close();
            } elseif ($result_staffs->num_rows > 0) {
                // Update the password in the staffs table
                $sql_update_staffs = "UPDATE staffs SET password = ? WHERE email = ?";
                $stmt_update_staffs = $conn->prepare($sql_update_staffs);
                $stmt_update_staffs->bind_param("ss", $hashed_password, $email);
                $stmt_update_staffs->execute();
                $stmt_update_staffs->close();
            } else {
                $msg = "No user found with that email address.";
            }

            // Delete the used token
            $sql_delete = "DELETE FROM password_resets WHERE token = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("s", $token);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Redirect to login page after a successful reset
            $msg = "Your password has been reset successfully.";
            header("Location: login.php");
            exit(); // Make sure to exit after the redirect to stop further script execution
        } else {
            $msg = "Invalid or expired token.";
        }
    } else {
        $msg = "Passwords do not match.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <div class="form-container">
            <h2>Reset Password</h2>
            <form method="post">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="Reset Password">
                </div>
                <p class="error"><?php echo $msg; ?></p>
            </form>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
    <button class="contact-us-button" onclick="window.location.href='contact.php'">Contact Us</button>
</body>
</html>
