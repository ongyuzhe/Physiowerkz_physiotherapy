<?php
session_start();

// Define roles array for flexibility
$valid_roles = ['admin', 'physiotherapist', 'hr'];
$role = $_SESSION['role'] ?? '';
// Check if the user is logged in and has the appropriate role, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($role, $valid_roles)) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

// Include different headers based on role
if ($role === 'admin') {
    include 'includes/header_admin.php';
} elseif ($role === 'physiotherapist') {
    include 'includes/header_staff.php';
}

// Initialize the search variable
$search = '';

// Check if the search form is submitted
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// SQL query to fetch records from health_questionnaire with search filter
$sql = "SELECT * FROM health_questionnaire";
if (!empty($search)) {
    $sql .= " WHERE full_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Questionnaire - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #495057;
        }

        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
            text-align: left;
        }

        th {
            background-color: #343a40;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e2e6ea;
        }

        .show-more {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-weight: 600;
        }

        .show-more:hover {
            color: #0056b3;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #ffffff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);

            position: fixed;
            top: 11%;
            left: 50%;
            transform: translateX(-50%);
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-body {
            padding-top: 20px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table th,
        .details-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 13px;
            text-align: left;
        }

        .signature {
            max-width: 100px;
            max-height: 100px;
            display: block;
            margin: auto;
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
            background-color: #2ecc71;
        }

        .search-container input[type="submit"]:hover,
        .search-container button:hover {
            background-color: #2980b9;
        }

        .search-container button[type="button"]:hover {
            background-color: #27ae60;
        }

        /* Reset Button Styling */
        .search-container form+form button {
            background-color: #95a5a6;
        }

        .search-container form+form button:hover {
            background-color: #7f8c8d;
        }

        @media (max-width: 768px) {

            table,
            th,
            td {
                display: block;
                width: 100%;
            }

            tr {
                margin-bottom: 15px;
                display: block;
            }

            th,
            td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }

            th::before,
            td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 50%;
                padding-left: 15px;
                font-weight: bold;
                text-align: left;
            }

            .form-group .search-container {
                flex-direction: column;
                gap: 5px;
            }

            .search-container {
                flex-direction: column;
                gap: 15px;
            }

            .search-container input[type="text"] {
                width: 100%;
            }
        }
    </style>
    <script>
        function showModal(id) {
            const modal = document.getElementById('modal_' + id);
            modal.style.display = 'block';
        }

        function closeModal(id) {
            const modal = document.getElementById('modal_' + id);
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            var modals = document.getElementsByClassName("modal");
            for (var i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = "none";
                }
            }
        }
    </script>
</head>

<body>
    <main>
        <h1>Patient Health Questionnaire</h1>

        <!-- Search Container -->
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search by Full Name" value="<?php echo htmlspecialchars($search); ?>">
                <input type="submit" value="Search">
            </form>
            <button type="button" onclick="window.location.href='?';">Reset</button> <!-- Reset button -->
        </div>


        <?php
        if ($result->num_rows > 0) {
            echo "<table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Action</th>
            </tr>";

            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                $id = $row["id"];
                echo "<tr>
                <td>" . $row["id"] . "</td>
                <td>" . $row["full_name"] . "</td>
                <td><span class='show-more' onclick='showModal($id)'>Show Details</span></td>
              </tr>
              <tr id='modal_$id' class='modal'>
                  <td colspan='3'>
                      <div class='modal-content'>
                          <div class='modal-header'>
                              <h2>Details of " . $row["full_name"] . "</h2>
                              <span class='close' onclick='closeModal($id)'>&times;</span>
                          </div>
                          <div class='modal-body'>
                              <table class='details-table'>
                                  <tr><th>Username</th><td>" . $row["username"] . "</td></tr>
                                  <tr><th>Know Us</th><td>" . $row["know_us"] . "</td></tr>
                                  <tr><th>Referrer</th><td>" . $row["referrer"] . "</td></tr>
                                  <tr><th>NRIC/Passport No</th><td>" . $row["nric_passport_no"] . "</td></tr>
                                  <tr><th>Profession</th><td>" . $row["profession"] . "</td></tr>
                                  <tr><th>Nationality</th><td>" . $row["nationality"] . "</td></tr>
                                  <tr><th>Country</th><td>" . $row["country"] . "</td></tr>
                                  <tr><th>Religion</th><td>" . $row["religion"] . "</td></tr>
                                  <tr><th>Emergency Contact</th><td>" . $row["emergency_contact"] . "</td></tr>
                                  <tr><th>Relationship</th><td>" . $row["relationship"] . "</td></tr>
                                  <tr><th>Emergency Phone</th><td>" . $row["emergency_phone"] . "</td></tr>
                                  <tr><th>Family Doctor</th><td>" . $row["family_doctor"] . "</td></tr>
                                  <tr><th>Clinic Address</th><td>" . $row["clinic_address"] . "</td></tr>
                                  <tr><th>Clinic Number</th><td>" . $row["clinic_number"] . "</td></tr>
                                  <tr><th>Newsletter</th><td>" . $row["newsletter"] . "</td></tr>
                                  <tr><th>Hospitalized</th><td>" . $row["hospitalized"] . "</td></tr>
                                  <tr><th>Hospitalized Details</th><td>" . $row["hospitalized_details"] . "</td></tr>
                                  <tr><th>Physical Activity</th><td>" . $row["physical_activity"] . "</td></tr>
                                  <tr><th>Chest Pain Activity</th><td>" . $row["chest_pain_activity"] . "</td></tr>
                                  <tr><th>Chest Pain Rest</th><td>" . $row["chest_pain_rest"] . "</td></tr>
                                  <tr><th>Heart Disease</th><td>" . $row["heart_disease"] . "</td></tr>
                                  <tr><th>Stroke</th><td>" . $row["stroke"] . "</td></tr>
                                  <tr><th>High Blood Pressure</th><td>" . $row["high_blood_pressure"] . "</td></tr>
                                  <tr><th>Diabetes</th><td>" . $row["diabetes"] . "</td></tr>
                                  <tr><th>Asthma</th><td>" . $row["asthma"] . "</td></tr>
                                  <tr><th>Osteoporosis</th><td>" . $row["osteoporosis"] . "</td></tr>
                                  <tr><th>Epilepsy</th><td>" . $row["epilepsy"] . "</td></tr>
                                  <tr><th>Cancer</th><td>" . $row["cancer"] . "</td></tr>
                                  <tr><th>Weight Loss</th><td>" . $row["weight_loss"] . "</td></tr>
                                  <tr><th>Dizziness</th><td>" . $row["dizziness"] . "</td></tr>
                                  <tr><th>Smoking</th><td>" . $row["smoking"] . "</td></tr>
                                  <tr><th>Pregnant</th><td>" . $row["pregnant"] . "</td></tr>
                                  <tr><th>Fracture Accident</th><td>" . $row["fracture_accident"] . "</td></tr>
                                  <tr><th>Blood Disorder</th><td>" . $row["blood_disorder"] . "</td></tr>
                                  <tr><th>Other Conditions</th><td>" . $row["other_conditions"] . "</td></tr>
                                  <tr><th>Pain Killers</th><td>" . $row["pain_killers"] . "</td></tr>
                                  <tr><th>Inhaler</th><td>" . $row["inhaler"] . "</td></tr>
                                  <tr><th>Blood Thinners</th><td>" . $row["blood_thinners"] . "</td></tr>
                                  <tr><th>Steroids</th><td>" . $row["steroids"] . "</td></tr>
                                  <tr><th>Other Medications</th><td>" . $row["other_medications"] . "</td></tr>
                                  <tr><th>Signature</th><td><img class='signature' src='" . $row["signature"] . "' alt='Signature'></td></tr>
                              </table>
                          </div>
                      </div>
                  </td>
              </tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='no-results'>No records found.</p>";
        }

        // Close connection
        $conn->close();
        ?>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>