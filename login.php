<?php
// Include database connection settings
require_once 'includes/settings.php';

// Initialize variables for form validation and error handling
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement for Patients table
        $sql_patients = "SELECT patient_id, username, password FROM patients WHERE username = ?";
        
        // Prepare a select statement for Staffs table
        $sql_staffs = "SELECT staff_id, username, password, role, lock_status FROM staffs WHERE username = ?";
        
        // Attempt to execute the prepared statement for Patients table
        if ($stmt = $conn->prepare($sql_patients)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                // Check if username exists in Patients table, if yes then verify password
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session and save the username to the session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = "patient";

                            // Update last login time
                            $update_lastlogin = "UPDATE patients SET lastlogin = NOW() WHERE username = ?";
                            if ($update_stmt = $conn->prepare($update_lastlogin)) {
                                $update_stmt->bind_param("s", $param_username);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }

                            // Redirect user to patients' welcome page
                            header("location: welcome.php");
                        } else {
                            // Password is not valid
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist in Patients table, check Staffs table
                    if ($stmt_staffs = $conn->prepare($sql_staffs)) {
                        // Bind variables to the prepared statement as parameters
                        $stmt_staffs->bind_param("s", $param_username);

                        // Set parameters
                        $param_username = $username;

                        // Attempt to execute the prepared statement
                        if ($stmt_staffs->execute()) {
                            // Store result
                            $stmt_staffs->store_result();

                            // Check if username exists in Staffs table, if yes then verify password
                            if ($stmt_staffs->num_rows == 1) {
                                // Bind result variables
                                $stmt_staffs->bind_result($id, $username, $hashed_password, $role, $lock_status);
                                if ($stmt_staffs->fetch()) {
                                    if (password_verify($password, $hashed_password)) {
                                        // Password is correct, so start a new session and save the username to the session
                                        session_start();

                                        // Store data in session variables
                                        $_SESSION["loggedin"] = true;
                                        $_SESSION["id"] = $id;
                                        $_SESSION["username"] = $username;
                                        $_SESSION["role"] = $role;
                                        $_SESSION["lock_status"] =$lock_status;

                                        // Update last login time
                                        $update_lastlogin = "UPDATE staffs SET lastlogin = NOW() WHERE username = ?";
                                        if ($update_stmt = $conn->prepare($update_lastlogin)) {
                                            $update_stmt->bind_param("s", $param_username);
                                            $update_stmt->execute();
                                            $update_stmt->close();
                                        }

                                        // Redirect user to the appropriate welcome page based on role
                                        if ($role == 'admin') {
                                            header("location: welcome_admin.php");
                                        }else if ($role == 'superadmin') {
                                            header("location: welcome_superadmin.php");
                                        }else {
                                            header("location: welcome_staff.php");
                                        } 
                                        
                                    } else {
                                        // Password is not valid
                                        $login_err = "Invalid username or password.";
                                    }
                                }
                            } else {
                                // Username doesn't exist in Staffs table
                                $login_err = "Invalid username or password.";
                            }
                        } else {
                            echo "Oops! Something went wrong. Please try again later.";
                        }

                        // Close statement
                        $stmt_staffs->close();
                    }
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <div class="form-container">
            <h2>Login</h2>
            <p>Please fill in your credentials to login.</p>
            <?php 
            if(!empty($login_err)){
                echo '<div class="error">' . $login_err . '</div>';
            }        
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <div class="form-field">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo $username; ?>">
                        <span class="error"><?php echo $username_err; ?></span>
                    </div>
                    <div class="form-field">
                        <label>Password</label>
                        <input type="password" name="password" value="<?php echo $password; ?>">
                        <span class="error"><?php echo $password_err; ?></span>
                    </div>
                </div>
                <div class="form-group-buttons">
                    <input type="submit" value="Login">
                    <input type="reset" value="Reset">
                </div>
                <p>Don't have an account? <a href="register.php">Register here</a>.</p>
                <p>Forgot your password? <a href="forgot_password.php">Reset here</a>.</p>
            </form>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
    <button class="contact-us-button" onclick="window.location.href='contact.php'">Contact Us</button>
</body>
</html>
