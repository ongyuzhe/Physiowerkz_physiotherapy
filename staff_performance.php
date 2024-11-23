<?php
include 'includes/header_admin.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

// Define variables to hold staff performance data
$staffData = [];
$staffNames = [];
$staffRatings = [];
$showMessage = false; // Flag to avoid showing message on initial load

// Handle the role filter submission
$roleFilter = isset($_POST['role']) ? $_POST['role'] : '';

if ($roleFilter) {
    // SQL query to retrieve full name (first_name + last_name) and average rating based on selected role
    $query = "SELECT CONCAT(s.first_name, ' ', s.last_name) AS full_name, s.role, COALESCE(AVG(r.star_rating), 0) AS avg_rating
              FROM staffs s
              LEFT JOIN reviews r ON s.username = r.staffName
              WHERE s.role = ?
              GROUP BY full_name, s.role";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $roleFilter);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $staffData[] = $row;
            $staffNames[] = $row['full_name'];
            $staffRatings[] = number_format($row['avg_rating'], 1);
        }
        $stmt->close();
    }

    // Show message only after search is performed
    if (empty($staffData)) {
        $showMessage = true;
    }
}

// Fetch reviews and join with patients and staff tables
$sql = "SELECT r.*, p.first_name AS patient_first_name, p.last_name AS patient_last_name, 
               s.first_name AS staff_first_name, s.last_name AS staff_last_name
        FROM reviews r
        LEFT JOIN patients p ON r.userName = p.username
        LEFT JOIN staffs s ON r.staffName = s.userName
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);

// Array to hold staff ratings
$staff_ratings = [];

// Loop through each review and organize ratings by staff
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $staff_name = $row['staff_first_name'] . ' ' . $row['staff_last_name'];

        // Initialize staff entry if not exists
        if (!isset($staff_ratings[$staff_name])) {
            $staff_ratings[$staff_name] = [
                'ratings' => [],
                'reviews' => []
            ];
        }

        // Add the rating and review to the staff
        $staff_ratings[$staff_name]['ratings'][] = $row['star_rating'];
        $staff_ratings[$staff_name]['reviews'][] = $row;
    }
}

// Define an array for role options
$roles = ['physiotherapist', 'hr', 'trainer'];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Assessment</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        h1 {
            text-align: center;
            font-size: 1.25em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        /* Container and Page Title */
        .container11 {
            width: 80%;
            margin: 30px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            text-align: center;
            font-size: 32px;
            color: #333;
            margin-bottom: 40px;
            margin-top: 70px;
        }

        /* Staff Rating Section */
        .staff-rating {
            padding: 20px;
            margin-bottom: 40px;
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .staff-name {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 10px;
        }

        .average-rating {
            font-size: 18px;
            color: #444;
            margin-bottom: 15px;
        }

        /* Reviews Section */
        .reviews-title {
            font-size: 20px;
            color: #666;
            margin-bottom: 10px;
        }

        .reviews-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .review-item {
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f1f1f1;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }

        .review-rating {
            font-size: 16px;
            color: #333;
            font-weight: bold;
        }

        .review-text {
            font-size: 15px;
            color: #555;
            margin: 5px 0;
        }

        .review-suggestion {
            font-size: 14px;
            color: #777;
            margin-bottom: 10px;
        }

        .review-details {
            font-size: 13px;
            color: #999;
            font-style: italic;
        }

        /* Divider between staff reviews */
        .divider {
            margin: 20px 0;
            border: 0;
            height: 1px;
            background: #ddd;
        }

        /* No Reviews Found */
        .no-reviews {
            font-size: 18px;
            color: #999;
            text-align: center;
        }

        /* Chart Container Styling */
        .chart-container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            position: relative;
        }

        /* Adjusting Canvas for Responsiveness */
        .chart-container canvas {
            max-width: 100%;
            height: auto;
        }

        /* No Data Message Styling */
        .no-data-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 18px;
            color: #999;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Tooltip Styling - Optional */
        .chartjs-tooltip {
            opacity: 1;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 8px;
            border-radius: 4px;
            font-size: 13px;
        }

        #staffPlotChart {
            max-width: 100%;
            height: 500px;
            margin: 0 auto;
            display: block;
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-bar select,
        .search-bar button {
            padding: 10px;
            font-size: 16px;
            margin: 0 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .search-bar button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }

        .search-bar button:hover {
            background-color: #0056b3;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .container11 {
                width: 95%;
            }

            .page-title {
                font-size: 28px;
            }

            .staff-name {
                font-size: 22px;
            }

            .average-rating {
                font-size: 16px;
            }

            .reviews-title {
                font-size: 18px;
            }

            .review-rating,
            .review-text,
            .review-suggestion,
            .review-details {
                font-size: 14px;
            }

            .chart-container {
                height: 400px;
            }

            .search-bar select,
            .search-bar button {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="page-title">

        <h1>Staff Performance Ratings and Reviews</h1>
    </div>


    <div class="search-bar">
        <form method="post">
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role; ?>" <?php echo ($roleFilter == $role) ? 'selected' : ''; ?>>
                        <?php echo ucfirst($role); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- Staff Performance Plot -->
    <div class="chart-container">
        <canvas id="staffPlotChart"></canvas>

        <?php if ($showMessage): ?>
            <div class="no-data-message">
                No staff data available for the selected role.
            </div>
        <?php endif; ?>
    </div>

    <!-- Reviews Section -->
    <div class="container11">
        <h2>Staff Reviews</h2>

        <?php if (!empty($staff_ratings)): ?>
            <?php foreach ($staff_ratings as $staff_name => $data): ?>
                <div class="staff-rating">
                    <h3 class="staff-name"><?php echo htmlspecialchars($staff_name); ?></h3>

                    <!-- Calculate and display the average rating -->
                    <?php
                    $ratings = $data['ratings'];
                    $average_rating = array_sum($ratings) / count($ratings);
                    ?>
                    <p class="average-rating"><strong>Average Rating:</strong> <?php echo number_format($average_rating, 1); ?> / 5</p>

                    <!-- Display reviews for the staff -->
                    <h4 class="reviews-title">Reviews:</h4>
                    <ul class="reviews-list">
                        <?php foreach ($data['reviews'] as $review): ?>
                            <li class="review-item">
                                <p class="review-rating"><strong>Rating:</strong> <?php echo $review['star_rating']; ?>/5</p>
                                <p class="review-text"><strong>Review:</strong> <?php echo htmlspecialchars($review['review']); ?></p>
                                <p class="review-suggestion"><strong>Suggestion:</strong> <?php echo htmlspecialchars($review['suggestion']); ?></p>
                                <p class="review-details"><em>Reviewed by <?php echo htmlspecialchars($review['patient_first_name'] . ' ' . $review['patient_last_name']); ?> on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></em></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <hr class="divider">
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-reviews">No reviews found.</p>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Chart.js script for the performance chart -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var staffNames = <?php echo json_encode($staffNames); ?>;
            var staffRatings = <?php echo json_encode($staffRatings); ?>;

            if (staffNames.length > 0 && staffRatings.length > 0) {
                var scatterData = staffNames.map(function(name, index) {
                    return {
                        x: index + 1, // Starting the index from 1
                        y: staffRatings[index], // The rating goes to Y-axis
                        r: staffRatings[index] * 5 // Scale point size by rating
                    };
                });

                var ctx = document.getElementById('staffPlotChart').getContext('2d');
                var staffPlotChart = new Chart(ctx, {
                    type: 'bubble',
                    data: {
                        labels: [''], // Insert a blank entry at index 0
                        datasets: [{
                            label: 'Staff Ratings',
                            data: scatterData,
                            backgroundColor: scatterData.map(function() {
                                return 'rgba(' + (Math.floor(Math.random() * 255)) + ',' +
                                    (Math.floor(Math.random() * 255)) + ',' +
                                    (Math.floor(Math.random() * 255)) + ', 0.7)';
                            }),
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                type: 'linear', // Linear scale, to start at 0
                                min: 0, // Start from 0
                                title: {
                                    display: true,
                                    text: 'Staff Name'
                                },
                                ticks: {
                                    callback: function(value, index) {
                                        if (value === 0) {
                                            return ''; // Do not show anything at position 0
                                        }
                                        return staffNames[value - 1]; // Start displaying staff names from position 1
                                    },
                                    stepSize: 1
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Average Rating'
                                },
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    max: 5
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        var staffIndex = tooltipItem.dataIndex;
                                        return staffNames[staffIndex] + ': ' + staffRatings[staffIndex] + ' ★';
                                    }
                                }
                            },
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                // Trigger the fade-in effect for the chart
                var chartElement = document.getElementById('staffPlotChart');
                chartElement.classList.add('visible');
            }
        });
    </script>
</body>

</html>