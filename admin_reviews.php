<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

include 'includes/header_admin.php';

// Get the search term from the GET request, if it exists
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch reviews and join with patients and staffs tables, filtering by the search term if provided
$sql = "SELECT r.*, p.first_name AS patient_first_name, p.last_name AS patient_last_name, 
               s.first_name AS staff_first_name, s.last_name AS staff_last_name
        FROM reviews r
        LEFT JOIN patients p ON r.userName = p.username
        LEFT JOIN staffs s ON r.staffName = s.userName";

// If there's a search term, modify the query to filter by the patient's first or last name
if ($searchTerm != '') {
    $sql .= " WHERE p.first_name LIKE '%$searchTerm%' OR p.last_name LIKE '%$searchTerm%'";
}

$sql .= " ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reviews</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        h1 {
            text-align: center;
            color: #495057;
            margin: 20px 0;
            font-size: 2.5em;
            font-weight: bold;
        }

        .review-container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
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

        .review-header,
        .review-footer {
            font-weight: bold;
            margin-bottom: 10px;
        }

        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        /* Search Container Styling */
        .search-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-container form {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .search-container input[type="text"] {
            padding: 10px;
            font-size: 1em;
            width: 250px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        .search-container input[type="text"]:focus {
            border-color: #3498db;
        }

        .search-container input[type="submit"],
        .search-container button {
            padding: 10px 15px;
            font-size: 1em;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .search-container button[type="button"] {
            background-color: #95a5a6;
        }

        .search-container input[type="submit"]:hover,
        .search-container button:hover {
            background-color: #2980b9;
        }

        .search-container button[type="button"]:hover {
            background-color: #7f8c8d;
        }

    </style>
</head>

<body>
    <main>
        <h1>Customer Reviews</h1>

        <!-- Search Form -->
        <div class="search-container">
            <form action="admin_reviews.php" method="GET">
                <input type="text" name="search" placeholder="Search by patient name" value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit">Search</button>
                <!-- Reset Button -->
                <button type="button" onclick="window.location.href='admin_reviews.php'">
                    Reset
                </button>
            </form>
        </div>

        <div class="review-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="review">
                        <!-- Display the patient's first and last name -->
                        <div class="review-header">
                            Reviewed by: <?php echo htmlspecialchars($row["patient_first_name"] . " " . $row["patient_last_name"]); ?>
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

                        <!-- Display the staff's first and last name at the bottom -->
                        <div class="review-footer">
                            Staff: <?php echo htmlspecialchars($row["staff_first_name"] . " " . $row["staff_last_name"]); ?>
                        </div>
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