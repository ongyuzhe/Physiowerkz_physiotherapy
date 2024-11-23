<?php

session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

// Handle AJAX request to fetch names based on role
if (isset($_POST['role']) && !isset($_POST['review'])) {
    $role = $_POST['role'];

    // Fetch first_name, last_name, and userName from the "staffs" table based on the selected role
    $sql = "SELECT first_name, last_name, userName FROM staffs WHERE role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();

    // Create an array of staff data
    $staffs = array();
    while ($row = $result->fetch_assoc()) {
        $staffs[] = array(
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'userName' => $row['userName']
        );
    }

    // Return the staff data as a JSON response
    echo json_encode($staffs);
    exit;  // Stop execution after returning the JSON response
}

// Initialize message variable for the review submission
$message = "";

// Handle form submission for review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['review'])) {
    $role = $_POST["role"]; // Get the selected role
    $review = $_POST["review"];
    $suggestion = $_POST["suggestion"];
    $star_rating = $_POST["star_rating"]; // Get star rating
    $staffName = $_POST["name"]; // Get the selected username (which will be saved as staffName)

    // Get the logged-in user's username from the session
    $logged_in_user = $_SESSION["username"]; // This will be saved as userName in the reviews table

    // Insert the review into the database
    $sql = "INSERT INTO reviews (role, review, suggestion, star_rating, userName, staffName, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiss", $role, $review, $suggestion, $star_rating, $logged_in_user, $staffName);

    if ($stmt->execute()) {
        $message = "New review added successfully";

        // Check if the rating is 2 stars or below
        if ($star_rating <= 2) {
            // Prepare a notification message for the senior role
            $notification_message = "Patient " . htmlspecialchars($logged_in_user) . " left a low review rating of $star_rating stars for " . htmlspecialchars($staffName) . ".";
            
            // Insert the notification into the admin_notifications table
            $sql_notification = "INSERT INTO admin_notifications (patient_id, changed_by, change_type, log_id, message, notification_time, status, admin_id) 
                                 VALUES (?, ?, 'Review Alert', ?, ?, NOW(), 'Unread', 1)";
            
            // Assuming `patient_id` is stored in the session; replace with your logic if different
            $patient_id = $_SESSION['id'];
            $log_id = $stmt->insert_id; // Use the review ID as the log_id to link the notification to this review
            
            $stmt_notification = $conn->prepare($sql_notification);
            $stmt_notification->bind_param("isis", $patient_id, $logged_in_user, $log_id, $notification_message);

            if ($stmt_notification->execute()) {
                $message .= " Senior staff notified of low review rating.";
            } else {
                $message .= " Error sending notification: " . $stmt_notification->error;
            }

            $stmt_notification->close();
        }

    } else {
        $message = "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

?>
<?php include 'includes/header_patient.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Review</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #EEF2F6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        #review-main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 200px);
            background-color: #EEF2F6;
        }

        #review-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 835px;
            /* Width of the container */
        }

        #review-form {
            display: flex;
            flex-direction: column;
            /* Use column direction for the main form */
        }

        #form-columns {
            display: flex;
            /* Flexbox for horizontal layout */
            justify-content: space-between;
            /* Space between columns */
            margin-bottom: 20px;
            /* Spacing below the columns */
        }

        #review-left-column,
        #review-right-column {
            flex: 0 0 90%;
            /* Each column takes up 50% of the container width */
            padding: 10px;
            /* Optional padding */
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #495057;
            font-weight: bold;
            text-align: center;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 5px;
        }

        #role,
        #name {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
        }

        #review-text,
        #review-suggestion {
            width: 100%;
            /* Ensure width stays the same */
            height: 100px;
            /* Set a fixed height for the text area */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
        }


        #review-submit {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #review-submit:hover {
            background-color: #0056b3;
        }

        .star-rating {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        .star-rating .checked {
            color: #ffc107;
        }

        .success {
            color: #28a745;
            text-align: center;
            margin-top: 20px;
        }

        .error {
            color: #dc3545;
            text-align: center;
            margin-top: 20px;
        }

        #ratingValue {
            display: inline-block;
            margin-top: 10px;
            font-size: 16px;
            color: #495057;
        }

        @media (max-width: 576px) {
            #form-columns {
                flex-direction: column;
                /* Stack columns on small screens */
            }

            #review-left-column,
            #review-right-column {
                margin-right: 0;
                /* No margin in stacked mode */
                margin-bottom: 15px;
                /* Spacing between stacked items */
                flex: 1;
                /* Allow columns to grow equally in stacked mode */
            }
        }

        /* for suggestion when it's 2 star or below */
        #review-suggestion[required] {
            border-color: #dc3545;
        }
    </style>
    <link rel="stylesheet" href="styles/styles1.css">
    <!-- Include FontAwesome for star icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <main>
        <div id="review-main-content" class="main-content">
            <div id="review-container" class="container">
                <form id="review-form" action="add_review.php" method="POST">
                    <h1>Add Review</h1>
                    <div id="form-columns"> <!-- Wrapper for horizontal layout -->
                        <div id="review-left-column"> <!-- Left Column -->
                            <label for="role">Role:</label>
                            <select id="role" name="role" required>
                                <option value="" disabled selected>[Please select a role]</option>
                                <option value="HR">HR</option>
                                <option value="Therapist">Therapist</option>
                                <option value="Trainer">Trainer</option>
                            </select>

                            <label for="name">Name:</label>
                            <select id="name" name="name" required>
                                <option value="" disabled selected>-</option>
                            </select>

                            <label for="review">Review:</label>
                            <textarea id="review-text" name="review" required></textarea>
                        </div>

                        <div id="review-right-column"> <!-- Right Column -->
                            <label for="suggestion">Suggestion: (if any)</label>
                            <textarea id="review-suggestion" name="suggestion"></textarea>

                            <label>Star Rating:</label>
                            <div id="starRating" class="star-rating">
                                <span class="fa fa-star" data-value="1"></span>
                                <span class="fa fa-star" data-value="2"></span>
                                <span class="fa fa-star" data-value="3"></span>
                                <span class="fa fa-star" data-value="4"></span>
                                <span class="fa fa-star" data-value="5"></span>
                            </div>
                            <input type="hidden" id="starRatingValue" name="star_rating" value="3">
                            <span id="ratingValue">3</span> Stars
                            <br><br>
                            <input id="review-submit" type="submit" value="Submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <br><br>
    </main>
    <?php include 'includes/footer.php'; ?>
    <!-- JavaScript for dynamic name population and star rating functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stars = document.querySelectorAll('.star-rating .fa-star');
            const starRatingValue = document.getElementById('starRatingValue');
            const ratingValueDisplay = document.getElementById('ratingValue');
            const roleSelect = document.getElementById('role');
            const nameSelect = document.getElementById('name');
            const suggestionField = document.getElementById('review-suggestion');
            const suggestionLabel = document.querySelector("label[for='suggestion']");

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-value');
                    starRatingValue.value = rating; // Update hidden input
                    ratingValueDisplay.textContent = rating; // Update visible rating value
                    updateStarDisplay(rating); // Update stars visually

                    handleSuggestionRequirement(rating); // Update suggestion field requirement based on rating
                });

                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-value');
                    updateStarDisplay(rating); // Show hovered stars
                });

                star.addEventListener('mouseout', function() {
                    updateStarDisplay(starRatingValue.value); // Revert to selected rating on mouse out
                });
            });

            function updateStarDisplay(rating) {
                stars.forEach(star => {
                    star.classList.remove('checked');
                    if (star.getAttribute('data-value') <= rating) {
                        star.classList.add('checked');
                    }
                });
            }

            // Initial star display setup based on default or saved value
            updateStarDisplay(starRatingValue.value);
            handleSuggestionRequirement(starRatingValue.value); // Handle suggestion field requirement on page load

            // Handle suggestion field requirement based on star rating
            function handleSuggestionRequirement(rating) {
                if (rating <= 2) {
                    suggestionField.setAttribute('required', 'required'); // Only add required attribute
                    suggestionLabel.innerHTML = "Suggestion:<br>(Required for 2 stars or below)"; // Update label with line break
                } else {
                    suggestionField.removeAttribute('required'); // Remove required attribute
                    suggestionLabel.innerHTML = "Suggestion:(optional)"; // Update label with line break
                }
            }

            // Handle dynamic name selection based on role
            roleSelect.addEventListener('change', function() {
                const selectedRole = this.value;

                // Create a mapping for the role names in the database
                let roleToDatabase = {
                    'HR': 'hr',
                    'Therapist': 'physiotherapist',
                    'Trainer': 'trainer'
                };

                // Get the correct value to send to the database
                const roleValueForDatabase = roleToDatabase[selectedRole];

                // Send AJAX request to the same page to fetch names based on role
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'add_review.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        try {
                            const staffs = JSON.parse(this.responseText);

                            // Clear existing options
                            nameSelect.innerHTML = '<option value="" disabled selected>[Please select a person]</option>';

                            // Populate new options with first_name and last_name, using userName as the value
                            staffs.forEach(function(staff) {
                                const option = document.createElement('option');
                                option.value = staff.userName; // Set the value to username
                                option.textContent = staff.first_name + ' ' + staff.last_name; // Display first_name last_name
                                nameSelect.appendChild(option);
                            });
                        } catch (error) {
                            console.error('Error parsing JSON:', error);
                        }
                    }
                };

                // Send the role data in the request
                xhr.send('role=' + roleValueForDatabase);
            });

            <?php if (!empty($message)): ?>
                alert("<?php echo addslashes($message); ?>"); // Use addslashes to escape quotes
            <?php endif; ?>
        });
    </script>
</body>

</html>