<?php

include 'includes/header_admin.php';
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: login.php");
    exit;
}


$sql_counts = "SELECT status, COUNT(*) as count
                FROM appointments
                GROUP BY status";

// Execute the query
$result_counts = $conn->query($sql_counts);

// Prepare data for Chart.js
$labels = [];
$data = [];

while ($row = $result_counts->fetch_assoc()) {
    $labels[] = $row['status'];
    $data[] = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Admin - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas {
            max-width: 100%;
            height: 400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    
    <main>
        <div class="container">
            <h1>Welcome to Physiowerkz, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <p>Manage your staff and monitor performance effectively.</p>
        </div>
        <h1>Appointments Overview</h1>
    <div>
        <canvas id="appointmentsChart"></canvas>
    </div>

    <script>
        // Data from PHP
        const labels = <?php echo json_encode($labels); ?>;
        const data = <?php echo json_encode($data); ?>;

        const ctx = document.getElementById('appointmentsChart').getContext('2d');
        const appointmentsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Appointments',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
