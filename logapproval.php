<?php
include 'includes/header_admin.php';

// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

// Handle form submissions for approval or rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && isset($_POST['video_id'])) {
        $video_id = $_POST['video_id'];
        $action = $_POST['action'];

        // Prepare SQL to get video details
        $sql = "SELECT location FROM videos WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $video_id);
            if ($stmt->execute()) {
                $stmt->store_result();
                $stmt->bind_result($location);
                if ($stmt->fetch()) {
                    $video_path = $location; // Path to the video file
                }
                $stmt->close();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        if ($action === 'approve') {
            // Approve and delete the video from the database
            $sql = "DELETE FROM videos WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $video_id);
                if ($stmt->execute()) {
                    // Delete the video file from the server
                    if (file_exists($video_path)) {
                        unlink($video_path);
                    }
                    echo "<p>Video deleted successfully.</p>";
                } else {
                    echo "<p>Error deleting video from database.</p>";
                }
                $stmt->close();
            }
        } elseif ($action === 'reject') {
            // Reject the deletion request and update the status
            $sql = "UPDATE videos SET approved = 0 WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $video_id);
                if ($stmt->execute()) {
                    echo "<p>Deletion request rejected.</p>";
                } else {
                    echo "<p>Error rejecting deletion request.</p>";
                }
                $stmt->close();
            }
        }
    }
}

// Fetch pending videos with log
$pending_videos = [];
$sql = "SELECT id, username, name, location, log FROM videos WHERE approved = 1"; // Fetch only videos that are approved but pending deletion

if ($stmt = $conn->prepare($sql)) {
    if ($stmt->execute()) {
        $stmt->store_result();
        $stmt->bind_result($id, $username, $name, $location, $log);
        while ($stmt->fetch()) {
            $pending_videos[] = [
                'id' => $id,
                'username' => $username,
                'name' => $name,
                'location' => $location,
                'log' => $log
            ];
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Approval - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .video-container {
            max-width: 800px;
            margin: 0 auto;
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

        .video-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .video-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .video-item h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .video-item p {
            margin: 5px 0;
            color: #6c757d;
        }

        video {
            width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .video-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .video-actions button {
            padding: 10px 15px;
            font-size: 14px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .video-actions button:hover {
            background-color: #0056b3;
        }

        .video-actions button:nth-child(2) {
            background-color: #dc3545;
        }

        .video-actions button:nth-child(2):hover {
            background-color: #c82333;
        }

        @media (max-width: 600px) {
            .video-item h2 {
                font-size: 16px;
            }

            .video-actions button {
                padding: 8px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <main class="video-container">
        <br><br>
        <h1>Pending Videos for Deletion</h1>
        <?php if ($pending_videos): ?>
            <div class="video-list">
                <?php foreach ($pending_videos as $video): ?>
                    <div class="video-item">
                        <h2>Video from <?php echo htmlspecialchars($video['username']); ?></h2>
                        <p>Video Name: <?php echo htmlspecialchars($video['name']); ?></p>
                        <video controls>
                            <source src="<?php echo htmlspecialchars($video['location']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <p>Reason: <?php echo htmlspecialchars($video['log']); ?></p>
                        <form method="post" class="video-actions">
                            <input type="hidden" name="video_id" value="<?php echo htmlspecialchars($video['id']); ?>">

                            <!-- Approve & Delete button, disabled if lock_status is Yes -->
                            <button type="submit" name="action" value="approve"
                                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'disabled title="Your account is locked and cannot approve videos."' : ''; ?>>
                                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Approve & Delete'; ?>
                            </button>

                            <!-- Reject button, disabled if lock_status is Yes -->
                            <button type="submit" name="action" value="reject"
                                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'disabled title="Your account is locked and cannot reject videos."' : ''; ?>>
                                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Reject'; ?>
                            </button>

                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No pending videos available at the moment.</p>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>