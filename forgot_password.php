<?php
// Include PHPMailer classes and database connection
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'includes/settings.php'; // Adjust as per your setup

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$email = $msg = $reset_link = "";

// Process form submission
if (isset($_POST['pwdrst'])) {
    $email = $_POST['email'];

    // Debug: Check if the email is received correctly
    error_log("Received email: " . $email);

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

    // Debug: Check if the email exists in either table
    if ($result_patients->num_rows > 0 || $result_staffs->num_rows > 0) {
        error_log("Email found in database.");

        // Generate a unique token
        $token = bin2hex(random_bytes(32)); // Generate a random 32-byte token
        // Calculate token expiry 1 hour from now in Malaysia timezone (UTC+8)
        date_default_timezone_set('Asia/Kuala_Lumpur'); // Set the timezone to Malaysia
        $token_expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $host = $_SERVER['HTTP_HOST']; // Get current host and port
        $reset_link = "http://$host/physiowerkz/reset_password.php?token=" . base64_encode($token);        

        // Store the token in the database
        $sql_token = "INSERT INTO password_resets (email, token, token_expiry) VALUES (?, ?, ?)";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->bind_param("sss", $email, $token, $token_expiry);
        $stmt_token->execute();
        $stmt_token->close();

        // Send email with reset link
        $message = '<div>
                        <p><b>Hello!</b></p>
                        <p>You are receiving this email because we received a password reset request for your account.</p>
                        <br>
                        <p>Please click on the link below to reset your password.</p>
                        <br>
                        <p><a href="' . $reset_link . '">Reset Password</a></p>
                        <br>
                        <p>If you did not request a password reset, please ignore this email.</p>
                        <br><br>
                        <p>Best regards,</p>
                        <br>
                        <p>The Physiowerkz Team</p>
                    </div>';

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // SMTP server
            $mail->SMTPAuth = true;         // Enable SMTP authentication
            $mail->Username = 'actionspheresup@gmail.com'; // SMTP username
            $mail->Password = 'zjkfwsajdbmqkioj';        // SMTP password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Enable verbose debug output
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';

            // Recipients
            $mail->setFrom('actionspheresup@gmail.com', 'Admin');
            $mail->addAddress($email); // Add a recipient

            // Content
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Subject = 'Password Reset Request';
            $mail->Body = $message;

            $mail->send();
            $msg = "An email has been sent to your email address with instructions to reset your password.";
        } catch (Exception $e) {
            $msg = "Oops! Something went wrong. Please try again later. Error: {$mail->ErrorInfo}";
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    } else {
        $msg = "We can't find a user with that email address.";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <div class="form-container">
            <h2>Forgot Password</h2>
            <p>Enter your email address below to receive a password reset link.</p>
            <form method="post">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-group">
                    <input type="submit" name="pwdrst" value="Send Password Reset Link">
                </div>
                <p class="error"><?php echo $msg; ?></p>
            </form>
            <?php if (!empty($reset_link)) { ?>
                <div class="form-group">
                    <a href="<?php echo $reset_link; ?>" class="btn btn-primary">Continue to Reset Password (For testing purposes, will remove)</a>
                </div>
            <?php } ?>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
    <button class="contact-us-button" onclick="window.location.href='contact.php'">Contact Us</button>
</body>

</html>