<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database configuration file
require_once "includes/settings.php";

// Set session timeout and warning durations (in seconds)
$timeout_duration = 1800; // 30 minutes
$warning_duration = 60; // 1 minute warning

// Check if the user is logged in and session timeout is set
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Check if the last activity timestamp is set
    if (isset($_SESSION['last_activity'])) {
        // Calculate the session's lifetime
        $elapsed_time = time() - $_SESSION['last_activity'];

        // Check if the elapsed time is greater than the timeout duration
        if ($elapsed_time >= $timeout_duration) {
            // Destroy the session and redirect to the login page
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit;
        }
    }

    // Update the last activity timestamp
    $_SESSION['last_activity'] = time();
}

// Fetch the user ID from the session
$user_id = $_SESSION["id"] ?? null;
// Fetch the unread notification count
$unread_count = 0;
if ($user_id && isset($conn) && $conn instanceof mysqli) {
    $sql_unread_count = "SELECT COUNT(*) as unread_count FROM notificationstaff WHERE staff_id = ? AND read_status = 'unread'";

    if ($stmt = $conn->prepare($sql_unread_count)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($unread_count);
        $stmt->fetch();
        $stmt->close();
    } else {
        error_log("SQL Prepare Error: " . $conn->error);
    }
}

$notifications = [];
if ($user_id && isset($conn) && $conn instanceof mysqli) {
    // Prepare SQL to fetch notifications for the logged-in user
    $sql_notifications = "SELECT id, message, status, read_status, created_at FROM notificationstaff WHERE staff_id = ? ORDER BY created_at DESC";

    // Prepare and execute the query
    if ($stmt = $conn->prepare($sql_notifications)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch notifications
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Consider logging the error message
        error_log("SQL Prepare Error: " . $conn->error);
    }
} else {
    if (!$user_id) {
        // Handle the case where user_id is not set
        // You might want to redirect to login or show a message
    } else {
        // Handle the case where $conn is not valid
        error_log("Database connection is not valid.");
    }
}

// Get the number of notifications
$notification_count = count($notifications);

// Note: Do not close the database connection here, as this is a header file

// Handle the "Mark as Read" action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $notification_id = $_POST['id'];
    $user_id = $_SESSION["id"] ?? null;

    // Check if the user is logged in and the notification ID is valid
    if ($user_id && isset($conn) && $conn instanceof mysqli && $notification_id) {
        $sql_update = "UPDATE notificationstaff SET read_status = 'read' WHERE id = ? AND staff_id = ?";

        if ($stmt = $conn->prepare($sql_update)) {
            $stmt->bind_param("ii", $notification_id, $user_id);
            $stmt->execute();
            $stmt->close();
            echo "success";
        } else {
            error_log("SQL Prepare Error: " . $conn->error);
            echo "error";
        }
    } else {
        echo "error";
    }

    // Stop further script execution after handling the AJAX request
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physiowerkz - Staff</title>
    <link rel="stylesheet" href="styles/styles1.css">
</head>
<style>
    .welcome-username {
        font-weight: bold;
        color: #000000;
        background-color: #F0F0F0;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 250px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1;
        max-height: 300px;
        overflow-y: auto;
        border-radius: 5px;
        font-size: 14px;
        font-family: Arial, sans-serif;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown-content a:hover {
        background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown:hover .dropbtn {
        background-color: #3e8e41;
    }

    .notification-icon {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .notification-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: red;
        color: white;
        border-radius: 50%;
        padding: 3px 7px;
        font-size: 12px;
        font-weight: bold;
        display: <?php echo $notification_count > 0 ? 'inline-block' : 'none'; ?>;
    }

    .notification-dropdown {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        min-width: 250px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1;
        max-height: 300px;
        overflow-y: auto;
        border-radius: 5px;
        font-size: 14px;
        font-family: Arial, sans-serif;
    }

    .notification-dropdown {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        min-width: 300px;
        max-height: 400px;
        /* Set a maximum height */
        overflow-y: auto;
        /* Enable vertical scrolling */
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .notification-dropdown ul {
        display: block;
        list-style-type: none;
        margin: 0;
        padding: 10px;
        counter-reset: list-counter;
        /* Initialize counter */
    }

    .notification-dropdown ul li {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        font-size: 14px;
        color: #000;
        display: block;
        line-height: 1.6;
        position: relative;
        /* Ensure positioning for pseudo-element */
        text-align: left;
        /* Align text to the left */
    }

    .notification-dropdown ul li::before {
        content: counter(list-counter) ". ";
        /* Display the counter value */
        counter-increment: list-counter;
        /* Increment the counter */
        position: absolute;
        left: -20px;
        /* Adjust the position as needed */
        top: 20%;
        transform: translateY(-50%);
        /* Vertically center the number */
        font-weight: bold;
        /* Style the number */
    }

    .notification-dropdown ul li:last-child {
        border-bottom: none;
    }

    .notification-dropdown ul li .notification-message {
        font-weight: bold;
    }

    .notification-dropdown ul li .notification-status,
    .notification-dropdown ul li .notification-timestamp {
        display: block;
        font-size: 12px;
        color: #888;
        margin-top: 5px;
    }

    .notification-dropdown ul li .notification-status {
        color: #888;
    }

    .notification-dropdown ul li .notification-timestamp {
        color: #666;
    }

    /* Optional: Add a smooth transition effect */
    .notification-dropdown,
    .dropdown-content {
        transition: opacity 0.3s ease;
    }
</style>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="Physiowerkz Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="welcome_staff.php">Home</a></li>
                    <li><a href="view_appointments.php">Appointments</a></li>
                    <li><a href="manage_patient.php">Patient</a></li>
                    <li><a href="view_treatment.php">Treatment</a></li>
                    <li><a href="view_health_questionnaire.php">View Health Questionnaire</a></li>
                    <li><a href="staff_reviews.php">Review</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    <li class="welcome-username">
                        <?php
                        if (isset($_SESSION["username"])) {
                            echo 'Welcome, ' . htmlspecialchars($_SESSION["username"]);
                        }
                        ?>
                    </li>
                    <li>
                        <div id="notification" class="notification-icon">
                            <img src="images/notification.png" alt="Notifications" style="width:25px;height:25px;" onclick="toggleDropdown()">
                            <span id="notification-count" class="notification-count"><?php echo $unread_count; ?></span>
                            <div id="notification-dropdown" class="notification-dropdown">
                                <ul id="notification-list">
                                    <?php if (!empty($notifications)): ?>
                                        <?php foreach ($notifications as $notification): ?>
                                            <li data-id="<?php echo htmlspecialchars($notification['id']); ?>" style="background-color: <?php echo $notification['read_status'] === 'unread' ? '#ffcccc' : '#f0f0f0'; ?>;">
                                                <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                                <?php if (!empty($notification['status'])): ?>
                                                    <div class="notification-status">Status: <?php echo htmlspecialchars($notification['status']); ?></div>
                                                <?php endif; ?>
                                                <div class="notification-timestamp">Created At: <?php echo htmlspecialchars($notification['created_at']); ?></div>
                                                <?php if ($notification['read_status'] !== 'read'): ?>
                                                    <button class="mark-as-read-button">Mark as Read</button>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li>No notifications</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</body>
<script>
    // Function to toggle the notification dropdown
    function toggleDropdown() {
        var dropdown = document.getElementById("notification-dropdown");

        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        } else {
            dropdown.style.display = "block";
        }
    }

    // Mark as read and update count
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.mark-as-read-button').forEach(function(button) {
            button.addEventListener('click', function() {
                var notificationId = this.closest('li').getAttribute('data-id');

                if (notificationId) {
                    console.log('Marking notification as read: ID = ' + notificationId);

                    // Send an AJAX request to mark the notification as read
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "", true); // Posting to the same PHP file
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200 && xhr.responseText === "success") {
                                // Update the UI for read notification
                                button.closest('li').style.backgroundColor = '#f0f0f0';
                                button.disabled = true;
                                button.textContent = "Marked as Read";

                                // Update the notification count on the icon
                                var notificationCountElem = document.getElementById("notification-count");
                                var currentCount = parseInt(notificationCountElem.textContent, 10) || 0;

                                // Decrease the count if it's greater than 0
                                if (currentCount > 0) {
                                    currentCount -= 1;
                                    notificationCountElem.textContent = currentCount > 0 ? currentCount : '';

                                    // Only hide the count if all notifications are read
                                    if (currentCount === 0) {
                                        notificationCountElem.style.display = 'none';
                                    }
                                }
                            } else {
                                console.error('Failed to mark notification as read.');
                            }
                        }
                    };
                    xhr.send("id=" + notificationId);
                } else {
                    console.error('Notification ID not found!');
                }
            });
        });
    });

    // Function to reset the notification count
    function resetNotificationCount() {
        var notificationCount = document.getElementById("notification-count");

        // Reset the count to 0 and hide the notification count element
        notificationCount.innerText = '';
        notificationCount.style.display = 'none';
    }

    // Close the dropdown if the user clicks outside of it
    window.onclick = function(event) {
        if (!event.target.closest('.notification-icon')) {
            var dropdown = document.getElementById("notification-dropdown");
            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            }
        }
    }

    // Set the session timeout and warning durations in milliseconds
    const timeoutDuration = 1800000; // 30 minutes in milliseconds
    const warningDuration = 60000; // 1 minute in milliseconds

    let logoutTimer;
    let warningTimer;

    // Function to show the warning popup
    function showWarningPopup() {
        const popup = document.createElement('div');
        popup.id = 'session-warning-popup';
        popup.style.position = 'fixed';
        popup.style.top = '50%';
        popup.style.left = '50%';
        popup.style.transform = 'translate(-50%, -50%)';
        popup.style.backgroundColor = 'white';
        popup.style.border = '1px solid black';
        popup.style.padding = '20px';
        popup.style.zIndex = '1000';
        popup.style.textAlign = 'center';
        popup.style.fontFamily = 'Arial, sans-serif';
        popup.style.fontSize = '16px';

        const message = document.createElement('p');
        message.textContent = 'You will be logged out in 1 minute due to inactivity. Do you want to extend your session?';
        popup.appendChild(message);

        const extendButton = document.createElement('button');
        extendButton.textContent = 'Yes, extend my session';
        extendButton.onclick = extendSession;
        popup.appendChild(extendButton);

        document.body.appendChild(popup);
    }

    // Function to remove the warning popup
    function removeWarningPopup() {
        const popup = document.getElementById('session-warning-popup');
        if (popup) {
            document.body.removeChild(popup);
        }
    }

    // Function to reset the session timeout
    function extendSession() {
        resetSessionTimeout();
        removeWarningPopup();
        clearTimeout(logoutTimer);
        clearTimeout(warningTimer);
        startTimers();
    }

    // Function to reset the session timeout on user activity
    function resetSessionTimeout() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "reset_session_timeout.php", true);
        xhr.send();
    }

    // Function to start the logout and warning timers
    function startTimers() {
        warningTimer = setTimeout(showWarningPopup, timeoutDuration - warningDuration);
        logoutTimer = setTimeout(() => {
            window.location.href = 'logout.php';
        }, timeoutDuration);
    }

    // Reset session timeout on any user activity
    window.onload = resetSessionTimeout;
    document.onmousemove = resetSessionTimeout;
    document.onkeypress = resetSessionTimeout;

    // Start the timers
    startTimers();

    // Close the popup if the user clicks outside of it
    window.onclick = function(event) {
        const popup = document.getElementById('session-warning-popup');
        if (popup && !popup.contains(event.target)) {
            removeWarningPopup();
        }
    }
</script>

</html>