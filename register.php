<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
// Include database connection settings
require_once 'includes/settings.php';

// Initialize variables for form validation and error handling
$username = $password = $confirm_password = $email = $first_name = $last_name = $dob = $contact_num = $gender = $area_code = $preferred_communication = $full_contact_number = $preferred_class= "";
$username_err = $password_err = $confirm_password_err = $email_err = $first_name_err = $last_name_err = $dob_err = $gender_err = $contact_num_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement to check if username exists
        $sql = "SELECT patient_id FROM patients WHERE username = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            $stmt->close();
        }
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate date of birth
    if (empty(trim($_POST["dob"]))) {
        $dob_err = "Please enter your date of birth.";
    } else {
        $dob = trim($_POST["dob"]);
        $age = date_diff(date_create($dob), date_create('now'))->y;
        if ($age < 18) {
            $dob_err = "You must be at least 18 years old.";
        }
    }

    // Validate gender
    if (empty(trim($_POST["gender"]))) {
        $gender_err = "Please select your gender.";
    } else {
        $gender = trim($_POST["gender"]);
    }

    // Validate contact number
    $contact_num = trim($_POST["contact_num"]);
    $area_code = trim($_POST["area_code"]);

    // Combine area code with contact number
    $full_contact_number = $area_code . $contact_num;

    if (!empty($contact_num)) {
        if (!preg_match('/^[1-9][0-9]{9,14}$/', $contact_num)) {
            $contact_num_err = "Contact number must be between 10 and 15 digits long, should not start with 0, and should only contain digits.";
        } else {
            $contact_num_err = "";
        }
    } else {
        $contact_num_err = "Please fill in your contact number.";
    }

    // Validate preferred communication 
    $preferred_communication = trim($_POST["preferred_communication"]);

    // Validate preferred class
    $preferred_class = trim($_POST["preferred_class"]);

    // Proceed with registration if no errors
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err) && empty($first_name_err) && empty($last_name_err) && empty($contact_num_err) && empty($dob_err) && empty($gender_err)) {
        $sql = "INSERT INTO patients (username, password, email, gender, first_name, last_name, dob, contact_num, preferred_communication, ques_status, class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
    
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters with 10 type definitions
            $stmt->bind_param(
                "ssssssssss",
                $param_username,
                $param_password,
                $param_email,
                $param_gender,
                $param_first_name,
                $param_last_name,
                $param_dob,
                $param_contact_num,
                $param_preferred_communication,
                $param_preferred_class
            );    

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Hashed password
            $param_email = $email;
            $param_gender = $gender;
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_dob = $dob;
            $param_contact_num = $full_contact_number; // Optional, can be NULL in database if not provided
            $param_preferred_communication = $preferred_communication;
            $param_preferred_class = $preferred_class;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page after successful registration
                echo "<script>
                        alert('Registration successful! Please log in.');
                        window.location.href='login.php';
                      </script>";
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        .toggle-password {
            cursor: pointer;
            color: #007bff;
            margin-left: 5px;
            position: relative;
            top: -5px;
        }

        .form-group {
            display: flex;
            justify-content: space-between;
        }

        .form-field {
            width: calc(50% - 10px);
            position: relative;
        }

        .form-field input,
        .form-field select {
            width: calc(100% - 30px);
        }

        .error {
            color: red;
            font-size: 12px;
        }

        .hint {
            font-size: 12px;
            color: #6c757d;
        }

        /* Tooltip container */
        .tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        /* Tooltip text */
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #f9f9f9;
            color: #333;
            text-align: center;
            border-radius: 5px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            /* Position the tooltip above the text */
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
            border: 1px solid #ccc;
        }

        /* Tooltip arrow */
        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #f9f9f9 transparent transparent transparent;
        }

        /* Show the tooltip text when you mouse over the tooltip container */
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
    <script>
        // Function to toggle password visibility
        function togglePasswordVisibility(id) {
            var passwordField = document.getElementById(id);
            var toggleIcon = document.getElementById(id + "-toggle");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.textContent = "Hide";
            } else {
                passwordField.type = "password";
                toggleIcon.textContent = "Show";
            }
        }

        // Function to reset the form
        function resetForm() {
            document.getElementById("myForm").reset();
        }

        // Instant validation for email
        function validateEmail() {
            var emailField = document.getElementById("email");
            var emailErr = document.getElementById("email_err");

            if (emailField.value === "") {
                emailErr.textContent = "Please enter an email.";
                emailField.focus();
            } else if (!emailField.value.match(/^[\w.%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/)) {
                emailErr.textContent = "Invalid email format.";
                emailField.focus();
            } else {
                emailErr.textContent = "";
            }
        }

        // Function to validate the form instantly
        function instantValidate(field, errElemId, validationFunc) {
            field.addEventListener("blur", validationFunc);
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Validate email on blur
            instantValidate(document.getElementById("email"), "email_err", validateEmail);
        });
    </script>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <div class="form-container">
            <h2>Register</h2>
            <p>Please fill this form to create an account.</p>
            <form id="myForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <div class="form-field">
                        <label class="tooltip">Username
                            <span class="tooltiptext">Choose a unique username that will be visible to others.</span>
                        </label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Choose a unique username">
                        <span class="error"><?php echo $username_err; ?></span>
                    </div>
                    <div class="form-field">
                        <label class="tooltip">Email
                            <span class="tooltiptext">Enter a valid email address.</span>
                        </label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
                        <span id="email_err" class="error"><?php echo $email_err; ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-field">
                        <label class="tooltip">Password
                            <span class="tooltiptext">Your password should be at least 8 characters long.</span>
                        </label>
                        <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($password); ?>">
                        <span class="error"><?php echo $password_err; ?></span>
                        <span id="password-toggle" class="toggle-password" onclick="togglePasswordVisibility('password')">Show</span>
                    </div>
                    <div class="form-field">
                        <label class="tooltip">Confirm Password
                            <span class="tooltiptext">Re-enter the same password to confirm.</span>
                        </label>
                        <input type="password" name="confirm_password" id="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>">
                        <span class="error"><?php echo $confirm_password_err; ?></span>
                        <span id="confirm_password-toggle" class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">Show</span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-field">
                        <label class="tooltip">First Name
                            <span class="tooltiptext">Enter your first name.</span>
                        </label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>">
                        <span class="error"><?php echo $first_name_err; ?></span>
                    </div>
                    <div class="form-field">
                        <label class="tooltip">Last Name
                            <span class="tooltiptext">Enter your last name.</span>
                        </label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>">
                        <span class="error"><?php echo $last_name_err; ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-field">
                        <label class="tooltip">Date of Birth
                            <span class="tooltiptext">Enter your date of birth in the format YYYY-MM-DD.</span>
                        </label>
                        <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
                        <span class="error"><?php echo $dob_err; ?></span>
                    </div>
                    <div class="form-field">
                        <label class="tooltip">Gender
                            <span class="tooltiptext">Select your gender.</span>
                        </label>
                        <select name="gender">
                            <option value="">Select</option>
                            <option value="male" <?php if ($gender === "male") echo "selected"; ?>>Male</option>
                            <option value="female" <?php if ($gender === "female") echo "selected"; ?>>Female</option>
                        </select>
                        <span class="error"><?php echo $gender_err; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-field">
                        <label class="tooltip">Contact Number
                            <span class="tooltiptext">Choose your country code and enter your phone number.</span>
                        </label>
                        <div style="display: flex;">
                            <select name="area_code" style="flex: 0 0 110px;" required>
                                <option value="+60" selected>+60 (Malaysia)</option>
                                <option value="+1">+1 (USA, Canada)</option>
                                <option value="+44">+44 (UK)</option>
                                <option value="+61">+61 (Australia)</option>
                                <option value="+91">+91 (India)</option>
                                <option value="+81">+81 (Japan)</option>
                                <option value="+49">+49 (Germany)</option>
                                <option value="+33">+33 (France)</option>
                                <option value="+86">+86 (China)</option>
                                <option value="+55">+55 (Brazil)</option>
                                <option value="+7">+7 (Russia)</option>
                                <option value="+39">+39 (Italy)</option>
                                <option value="+34">+34 (Spain)</option>
                                <option value="+52">+52 (Mexico)</option>
                                <option value="+27">+27 (South Africa)</option>
                                <option value="+82">+82 (South Korea)</option>
                                <option value="+62">+62 (Indonesia)</option>
                                <option value="+31">+31 (Netherlands)</option>
                                <option value="+47">+47 (Norway)</option>
                                <option value="+46">+46 (Sweden)</option>
                                <option value="+41">+41 (Switzerland)</option>
                                <option value="+65">+65 (Singapore)</option>
                                <option value="+63">+63 (Philippines)</option>
                                <option value="+64">+64 (New Zealand)</option>
                                <option value="+20">+20 (Egypt)</option>
                                <option value="+51">+51 (Peru)</option>
                                <option value="+54">+54 (Argentina)</option>
                                <option value="+56">+56 (Chile)</option>
                                <option value="+32">+32 (Belgium)</option>
                                <option value="+48">+48 (Poland)</option>
                                <option value="+30">+30 (Greece)</option>
                                <option value="+36">+36 (Hungary)</option>
                                <option value="+234">+234 (Nigeria)</option>
                                <option value="+254">+254 (Kenya)</option>
                                <option value="+378">+378 (San Marino)</option>
                                <option value="+421">+421 (Slovakia)</option>
                                <option value="+386">+386 (Slovenia)</option>
                                <option value="+66">+66 (Thailand)</option>
                                <option value="+672">+672 (Australian External Territories)</option>
                                <option value="+677">+677 (Solomon Islands)</option>
                                <option value="+686">+686 (Kiribati)</option>
                                <option value="+689">+689 (French Polynesia)</option>
                                <option value="+690">+690 (Tokelau)</option>
                                <option value="+691">+691 (Micronesia)</option>
                                <option value="+692">+692 (Marshall Islands)</option>
                            </select>
                            <input type="text" name="contact_num" class="input-phone-number" required style="flex: 1; margin-left: 10px;">
                        </div>
                        <span class="error"><?php echo $contact_num_err; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-field">
                        <label class="tooltip">Preferred Method of Communication
                            <span class="tooltiptext">Choose how you prefer to be contacted.</span>
                        </label>
                        <select name="preferred_communication">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-field">
                        <label class="tooltip">Preferred Payment
                            <span class="tooltiptext">Choose which class.</span>
                        </label>
                        <select name="preferred_class">
                            <option value="class1">Class 1 - Pay by cash</option>
                            <option value="class2">Class 2 - Pay by insurance </option>
                        </select>
                    </div>
                </div>

                <div class="form-group-buttons">
                    <input type="submit" value="Submit">
                    <input type="reset" value="Reset" onclick="resetForm()">
                </div>
                <p>Already have an account? <a href="login.php">Login here</a>.</p>
            </form>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
    <button class="contact-us-button" onclick="window.location.href='contact.php'">Contact Us</button>
</body>

</html>