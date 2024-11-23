<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

include 'includes/header_patient.php';

// Get the username from the session
$username = $_SESSION["username"]; // Adjust this based on your session variable

// Fetch reviews for the logged-in user and join with the staffs table to get the staff's first and last name
$sql = "SELECT r.*, s.first_name AS staff_first_name, s.last_name AS staff_last_name
        FROM reviews r
        LEFT JOIN staffs s ON r.staffName = s.userName
        WHERE r.userName = '$username'
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reviews</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #495057;
            margin: 20px 0;
            font-size: 2.5em;
            font-weight: bold;
        }

        .container123 {
            width: 90%;
            max-width: 1200px; /* Adjusted max width for larger screens */
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Responsive grid */
            gap: 20px; /* Space between reviews */
        }

        .review {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s;
        }

        .review:hover {
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.15);
        }

        .review p {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }

        .review strong {
            color: #007bff;
        }

        .review small {
            color: #999;
            font-style: italic;
        }

        .review hr {
            margin: 15px 0;
            border: 0;
            border-top: 1px solid #e9ecef;
        }

        .star-rating {
            color: #ffc107;
            font-size: 20px;
        }

        .star-rating .star {
            display: inline-block;
        }

        .star-rating .star.empty {
            color: #ddd;
        }

        .review-header {
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
    <link rel="stylesheet" href="styles/styles1.css">
</head>

<body>
    <main>
        <h1>Your Reviews</h1>
        <div class="container123">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="review">
                        <!-- Display the staff's first and last name at the top of the review -->
                        <div class="review-header">
                            Staff Reviewed: <?php echo htmlspecialchars($row["staff_first_name"] . " " . $row["staff_last_name"]); ?>
                        </div>
                        
                        <p><?php echo htmlspecialchars($row["review"]); ?></p>
                        <p><strong>Suggestion:</strong> <?php echo htmlspecialchars($row["suggestion"]); ?></p>
                        <p><strong>Star Rating:</strong>
                            <span class="star-rating">
                                <?php echo str_repeat('<span class="star">★</span>', $row["star_rating"]) . str_repeat('<span class="star empty">☆</span>', 5 - $row["star_rating"]); ?>
                            </span>
                        </p>
                        <small><?php echo htmlspecialchars($row["created_at"]); ?></small>
                        <hr>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews found.</p>
            <?php endif; ?>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>
