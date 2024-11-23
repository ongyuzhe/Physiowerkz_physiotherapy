<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$valid_roles = ['admin', 'physiotherapist'];
$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($role, $valid_roles)) {
    header("location: login.php");
    exit;
}

require_once "includes/settings.php";

if ($role === 'admin') {
    include 'includes/header_admin.php';
} elseif ($role === 'physiotherapist') {
    include 'includes/header_staff.php';
}

date_default_timezone_set('Asia/Kuala_Lumpur');

$patient_id = $_POST['patient_id'] ?? '';
$appointment_id = $_POST['appointment_id'];
$full_name = '';
$treatmentData = [];

// Retrieve patient details and treatment data
if ($patient_id) {
    // Retrieve patient full name
    $sql = "SELECT first_name, last_name FROM patients WHERE patient_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $stmt->bind_result($first_name, $last_name);
        if ($stmt->fetch()) {
            $full_name = $first_name . ' ' . $last_name;
        }
        $stmt->close();
    }

    // Fetch treatment data for the patient
    $sql_treatment = "SELECT * FROM treatmentassessment3 WHERE patient_id = ? AND appointment_id = ? ORDER BY id ASC";
    if ($stmt = $conn->prepare($sql_treatment)) {
        $stmt->bind_param("ii", $patient_id, $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $treatmentData[] = $row;
        }
        $stmt->close();
    }

    // Fetch exercise suggestions for the patient
    $exerciseData = [];
    $sql_exercise = "SELECT * FROM exercise_suggestions WHERE patient_id = ? AND appointment_id = ? ORDER BY situation ASC";
    if ($stmt = $conn->prepare($sql_exercise)) {
        $stmt->bind_param("ii", $patient_id, $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Group exercises by situation
            $exerciseData[$row['situation']][] = $row;
        }
        $stmt->close();
    }
}

// Sanitize inputs
$patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
$tissue_types = isset($_POST['tissue_type']) ? array_map('trim', $_POST['tissue_type']) : [];
$tissue_findings = isset($_POST['tissue_findings']) ? array_map('trim', $_POST['tissue_findings']) : [];
$remarks = isset($_POST['remarks']) ? array_map('trim', $_POST['remarks']) : [];
$situations = isset($_POST['situation']) ? array_map('trim', $_POST['situation']) : [];
$dates = isset($_POST['date']) ? array_map('trim', $_POST['date']) : [];
$suggestions = isset($_POST['suggestion']) ? array_map('trim', $_POST['suggestion']) : [];

// Validate patient_id
if ($patient_id <= 0) {
    die("Invalid patient ID.");
}

// Validate tissue types and findings
foreach ($tissue_types as $index => $tissue_type) {
    if (empty($tissue_type)) {
        die("Tissue type at index $index is missing.");
    }
    if (strlen($tissue_findings[$index]) > 255) {
        die("Tissue findings at index $index are too long.");
    }
}

// Validate exercise data
foreach ($situations as $index => $situation) {
    if (empty($situation)) {
        die("Situation at index $index is missing.");
    }
    if (strlen($suggestions[$index]) > 255) {
        die("Suggestion at index $index is too long.");
    }

    // Validate date format
    $date = DateTime::createFromFormat('Y-m-d', $dates[$index]);
    if (!$date || $date->format('Y-m-d') !== $dates[$index]) {
        die("Invalid date format for exercise at index $index.");
    }
}

// Handle exercise submissions
if (isset($_POST['submit_exercise'])) {
    // Declare necessary variables
    $appointment_id = $_POST['appointment_id'];
    $patient_id = $_POST['patient_id'];
    $suggestion = $_POST['suggestion'] ?? [];
    $situation = $_POST['situation'] ?? [];
    $date = $_POST['date'] ?? [];
    $remarks = $_POST['remarks'] ?? [];

    foreach ($suggestion as $index => $exercise_suggestion) {
        // Retrieve individual fields for each exercise entry
        $exercise_situation = $situation[$index];
        $exercise_date = $date[$index];
        $exercise_remarks = $remarks[$index];

        // Check if an exact match already exists for the given fields
        $sql_check = "SELECT * FROM exercise_suggestions WHERE patient_id = ? AND appointment_id = ? 
                      AND situation = ? AND suggestion = ? AND date = ? AND remarks = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("iissss", $patient_id, $appointment_id, $exercise_situation, $exercise_suggestion, $exercise_date, $exercise_remarks);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows === 0) {
                // Insert new record since no exact match exists
                $sql_insert = "INSERT INTO exercise_suggestions (appointment_id, patient_id, patient_name, suggestion, situation, date, remarks) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param("iisssss", $appointment_id, $patient_id, $full_name, $exercise_suggestion, $exercise_situation, $exercise_date, $exercise_remarks);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
    echo "<script>alert('Exercise data successfully saved.'); window.location.href='view_treatment.php';</script>";
}


// Handle row deletion based on 'id' for tissue
if (isset($_POST['delete_tissue_id'])) {
    $delete_id = $_POST['delete_tissue_id'];
    $sql_delete = "DELETE FROM treatmentassessment3 WHERE id = ?";
    if ($stmt_delete = $conn->prepare($sql_delete)) {
        $stmt_delete->bind_param("i", $delete_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        echo "<script>alert('Tissue record deleted successfully.'); window.location.href='view_treatment.php';</script>";
    } else {
        echo "Error preparing delete statement: " . $conn->error;
    }
}

// Handle exercise submissions
if (isset($_POST['submit_exercise'])) {
    // Echo the values of situation array for debugging
    echo "<br><br><br><br><pre>";
    print_r($_POST['situation']); // Print the situation[] array
    echo "</pre>";

    $appointment_id = $_POST['appointment_id'];
    $patient_id = $_POST['patient_id'];
    $suggestion = $_POST['suggestion'] ?? [];
    $situation = $_POST['situation'] ?? [];
    $date = $_POST['date'] ?? [];
    $remarks = $_POST['remarks'] ?? [];

    foreach ($situation as $index => $situation_value) {
        echo "Situation at index $index: " . htmlspecialchars($situation_value) . "<br>"; // Check each situation value
    }

    foreach ($suggestion as $index => $exercise_suggestion) {
        $exercise_situation = $situation[$index];
        $exercise_date = $date[$index];
        $exercise_remarks = $remarks[$index];

        // Check if a record already exists for the same patient with the same suggestion, date, remarks, and situation
        $sql_check = "SELECT * FROM exercise_suggestions WHERE patient_id = ? AND appointment_id = ? AND situation = ? AND suggestion = ? AND date = ? AND remarks = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("iissss", $patient_id, $appointment_id, $exercise_situation, $exercise_suggestion, $exercise_date, $exercise_remarks);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
            } else {
                // If no record exists, check if only the situation matches
                $sql_check_situation = "SELECT * FROM exercise_suggestions WHERE patient_id = ? AND appointment_id = ? AND situation = ?";
                if ($stmt_check_situation = $conn->prepare($sql_check_situation)) {
                    $stmt_check_situation->bind_param("iis", $patient_id, $appointment_id, $exercise_situation);
                    $stmt_check_situation->execute();
                    $result_check_situation = $stmt_check_situation->get_result();

                    if ($result_check_situation->num_rows > 0) {
                        // If exists, update the record
                        $sql_update = "UPDATE exercise_suggestions SET suggestion = ?, date = ?, remarks = ? WHERE patient_id = ? AND appointment_id = ? AND situation = ?";
                        if ($stmt_update = $conn->prepare($sql_update)) {
                            $stmt_update->bind_param("sssiis", $exercise_suggestion, $exercise_date, $exercise_remarks, $patient_id, $appointment_id, $exercise_situation);
                            $stmt_update->execute();
                            $stmt_update->close();
                        }
                    } else {
                        // Insert new record
                        $sql_insert = "INSERT INTO exercise_suggestions (appointment_id, patient_id, patient_name, suggestion, situation, date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        if ($stmt_insert = $conn->prepare($sql_insert)) {
                            $stmt_insert->bind_param("iisssss", $appointment_id, $patient_id, $full_name, $exercise_suggestion, $exercise_situation, $exercise_date, $exercise_remarks);
                            $stmt_insert->execute();
                            $stmt_insert->close();
                        }
                    }
                    $stmt_check_situation->close();
                }
            }

            $stmt_check->close();
        }
    }

    echo "<script>alert('Exercise data successfully saved.'); window.location.href='view_treatment.php';</script>";
}

// Handle exercise row deletion
if (isset($_POST['delete_exercise_id'])) {
    $delete_id = $_POST['delete_exercise_id'];
    $sql_delete = "DELETE FROM exercise_suggestions WHERE id=?";
    if ($stmt_delete = $conn->prepare($sql_delete)) {
        $stmt_delete->bind_param("i", $delete_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        echo "<script>alert('Exercise record deleted successfully.'); window.location.href='view_treatment.php';</script>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Assessment</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        #exercise-form,
        #treatment-form {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .hidden-form {
            max-width: none;
            padding: 0;
            background-color: transparent;
            border: none;
            box-shadow: none;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        /* Input field styles */
        input[type="text"],
        input[type="date"],
        input[type="hidden"],
        select {
            width: 28%;
            padding: 10px;
            border: 1px solid #dcdcdc;
            border-radius: 4px;
            font-size: 14px;
            color: #555;
            background-color: #fff;
        }

        /* Common Button Styles */
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
            margin-left: 10px;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Specific button styles */
        button.delete-btn {
            background-color: #ff6b6b;
        }

        button.delete-btn:hover {
            background-color: #e60000;
        }

        .add-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .add-btn:hover {
            background-color: #27ae60;
        }

        .add-btn {
            margin-top: 10px;
        }

        /* Submit button styles */
        button.submit-btn {
            background-color: #27ae60;
            width: 100%;
            display: block;
            margin-top: 20px;
        }

        button.submit-btn:hover {
            background-color: #2ecc71;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                align-items: stretch;
            }

            input[type="text"],
            input[type="date"] {
                width: 100%;
                margin-bottom: 10px;
            }

            button {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>

</head>

<body>
    <main>
        <h1>Tissue and Exercise Assessment</h1>
        <h2>Tissue Assessment</h2>
        <form id="treatment-form" method="POST">
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment_id ?? ''); ?>">
            <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">

            <!-- Prefilled assessment rows -->
            <div id="assessment-rows">
                <?php if (!empty($treatmentData)) : ?>
                    <?php foreach ($treatmentData as $index => $row) : ?>
                        <div class="form-row" id="row-<?php echo $index; ?>">
                            <input type="text" name="tissue_type[]" value="<?php echo htmlspecialchars($row['tissue_type']); ?>" placeholder="Tissue Type">
                            <select name="tissue_findings[]" required>
                                <option value="Superficial Fascia" <?php if ($row['tissue_findings'] == 'Superficial Fascia') echo 'selected'; ?>>Superficial Fascia</option>
                                <option value="Deep Fascia" <?php if ($row['tissue_findings'] == 'Deep Fascia') echo 'selected'; ?>>Deep Fascia</option>
                                <option value="Muscle Fascia" <?php if ($row['tissue_findings'] == 'Muscle Fascia') echo 'selected'; ?>>Muscle Fascia</option>
                                <option value="Tone" <?php if ($row['tissue_findings'] == 'Tone') echo 'selected'; ?>>Tone</option>
                                <option value="Band" <?php if ($row['tissue_findings'] == 'Band') echo 'selected'; ?>>Band</option>
                                <option value="Fibrosis" <?php if ($row['tissue_findings'] == 'Fibrosis') echo 'selected'; ?>>Fibrosis</option>
                                <option value="Trigger point" <?php if ($row['tissue_findings'] == 'Trigger point') echo 'selected'; ?>>Trigger point</option>
                                <option value="Lump" <?php if ($row['tissue_findings'] == 'Lump') echo 'selected'; ?>>Lump</option>
                                <option value="Dead block" <?php if ($row['tissue_findings'] == 'Dead block') echo 'selected'; ?>>Dead block</option>
                                <option value="Flattened/Inhibited" <?php if ($row['tissue_findings'] == 'Flattened/Inhibited') echo 'selected'; ?>>Flattened/Inhibited</option>
                                <option value="Swelling" <?php if ($row['tissue_findings'] == 'Swelling') echo 'selected'; ?>>Swelling</option>
                                <option value="Lymph congestion" <?php if ($row['tissue_findings'] == 'Lymph congestion') echo 'selected'; ?>>Lymph congestion</option>
                                <option value="Cellulite" <?php if ($row['tissue_findings'] == 'Cellulite') echo 'selected'; ?>>Cellulite</option>
                            </select>
                            <input type="text" name="remarks[]" value="<?php echo htmlspecialchars($row['remarks']); ?>" placeholder="Remarks">
                            <button class="delete-btn"
                                type="submit"
                                name="delete_tissue_id"
                                value="<?php echo $row['id']; ?>"
                                <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot perform this action."'; ?>>
                                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Delete'; ?>
                            </button>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No assessment data found. Add new entries below:</p>
                <?php endif; ?>
            </div>
            <!-- Button to add more rows -->
            <button type="button" class="add-btn"
                onclick="addRowTissue()"
                <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot perform this action."'; ?>>
                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Add Tissue Assessment'; ?>
            </button>

            <!-- Submit button -->
            <div>
                <br>
                <button type="submit" name="submit_tissue"
                    <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot perform this action."'; ?>>
                    <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Submit Tissues'; ?>
                </button>
            </div>

        </form>

        <h2>Exercise Suggestions</h2>
        <form id="exercise-form" method="POST">
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment_id ?? ''); ?>">
            <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">

            <h3>Severe Situation</h3>
            <div id="severe-rows">
                <?php if (!empty($exerciseData) && isset($exerciseData['Severe']) && is_array($exerciseData['Severe'])) : ?>
                    <?php foreach ($exerciseData['Severe'] as $exercise) : ?>
                        <div class="form-row">
                            <input type="text" name="suggestion[]" value="<?php echo htmlspecialchars($exercise['suggestion']); ?>" placeholder="Exercise Suggestion">
                            <input type="date" name="date[]" value="<?php echo htmlspecialchars($exercise['date']); ?>" placeholder="Date">
                            <input type="text" name="remarks[]" value="<?php echo htmlspecialchars($exercise['remarks']); ?>" placeholder="Remarks">
                            <input type="hidden" name="situation[]" value="Severe">
                            <button class="delete-btn"
                                type="submit"
                                name="delete_exercise_id"
                                value="<?php echo $exercise['id']; ?>"
                                <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot perform this action."'; ?>>
                                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Delete'; ?>
                            </button>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No assessment data found. Add new entries below:</p>
                <?php endif; ?>
            </div>
            <button class="add-btn"
                type="button"
                onclick="addRowExercise('Severe')"
                <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot add exercises."'; ?>>
                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Add Severe Exercise'; ?>
            </button>

            <h3>Moderate Situation</h3>
            <div id="moderate-rows">
                <?php if (!empty($exerciseData) && isset($exerciseData['Moderate']) && is_array($exerciseData['Moderate'])) : ?>
                    <?php foreach ($exerciseData['Moderate'] as $exercise) : ?>
                        <div class="form-row">
                            <input type="text" name="suggestion[]" value="<?php echo htmlspecialchars($exercise['suggestion']); ?>" placeholder="Exercise Suggestion">
                            <input type="date" name="date[]" value="<?php echo htmlspecialchars($exercise['date']); ?>" placeholder="Date">
                            <input type="text" name="remarks[]" value="<?php echo htmlspecialchars($exercise['remarks']); ?>" placeholder="Remarks">
                            <input type="hidden" name="situation[]" value="Moderate">
                            <button class="delete-btn"
                                type="submit"
                                name="delete_exercise_id"
                                value="<?php echo $exercise['id']; ?>"
                                <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot delete exercises."'; ?>>
                                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Delete'; ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No assessment data found. Add new entries below:</p>
                <?php endif; ?>
            </div>
            <button class="add-btn"
                type="button"
                onclick="addRowExercise('Moderate')"
                <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot add exercises."'; ?>>
                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Add Moderate Exercise'; ?>
            </button>
            <h3>Low Situation</h3>
            <div id="low-rows">
                <?php if (!empty($exerciseData) && isset($exerciseData['Low']) && is_array($exerciseData['Low'])) : ?>
                    <?php foreach ($exerciseData['Low'] as $exercise) : ?>
                        <div class="form-row">
                            <input type="text" name="suggestion[]" value="<?php echo htmlspecialchars($exercise['suggestion']); ?>" placeholder="Exercise Suggestion">
                            <input type="date" name="date[]" value="<?php echo htmlspecialchars($exercise['date']); ?>" placeholder="Date">
                            <input type="text" name="remarks[]" value="<?php echo htmlspecialchars($exercise['remarks']); ?>" placeholder="Remarks">
                            <input type="hidden" name="situation[]" value="Low">
                            <button class="delete-btn"
                                type="submit"
                                name="delete_exercise_id"
                                value="<?php echo $exercise['id']; ?>"
                                <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot delete exercises."'; ?>>
                                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Delete'; ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No assessment data found. Add new entries below:</p>
                <?php endif; ?>
            </div>
            <button class="add-btn"
                type="button"
                onclick="addRowExercise('Low')"
                <?php if ($_SESSION['lock_status'] === 'Yes') echo 'disabled title="Your account is locked and cannot add exercises."'; ?>>
                <?php echo ($_SESSION['lock_status'] === 'Yes') ? 'Locked' : 'Add Low Exercise'; ?>
            </button>

            <div>
                <br>
                <?php if ($_SESSION['lock_status'] === 'Yes') : ?>
                    <!-- Change Submit button to Back -->
                    <button type="button" onclick="window.location.href='view_treatment.php'">Back</button>
                <?php else : ?>
                    <button type="submit" name="submit_exercise">Submit Exercises</button>
                <?php endif; ?>
            </div>

        </form>

        <div>
            <!-- Hidden Form -->
            <form id="navigationForm" class="hidden-form" method="POST" action="">
                <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($_POST['patient_id']); ?>">
                <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($_POST['appointment_id']); ?>">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($_POST['username']); ?>">
            </form>

            <!-- Floating Buttons -->
            <div
                class="floating-button left"
                style="position: fixed; top: 50%; left: 10px; transform: translateY(-50%); cursor: pointer; z-index: 1000;"
                onclick="navigateToPage('treatmentdoctor2.php')">
                <img src="images/left-arrow.png" alt="Previous" style="width: 50px; height: 50px;">
            </div>

            <div
                class="floating-button right"
                style="position: fixed; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; z-index: 1000;"
                onclick="navigateToPage('treatmentdoctor4.php')">
                <img src="images/right-arrow.png" alt="Next" style="width: 50px; height: 50px;">
            </div>
        </div>

        <script>
            // Function to add a new assessment row
            function addRowTissue() {
                const rowId = 'row-' + Date.now();
                const rowDiv = document.createElement('div');
                rowDiv.className = 'form-row';
                rowDiv.id = rowId;

                // Input for tissue_type (text input)
                const tissueInput = document.createElement('input');
                tissueInput.type = 'text';
                tissueInput.name = 'tissue_type[]';
                tissueInput.placeholder = 'Tissue Type';

                // Dropdown for tissue_findings (select input)
                const findingsSelect = document.createElement('select');
                findingsSelect.name = 'tissue_findings[]';
                findingsSelect.required = true;

                // Options including disabled 'Please select' option
                const optionElements = [{
                        value: '',
                        text: 'Please select Tissue Findings',
                        disabled: true,
                        selected: true
                    },
                    {
                        value: 'Superficial Fascia',
                        text: 'Superficial Fascia'
                    },
                    {
                        value: 'Deep Fascia',
                        text: 'Deep Fascia'
                    },
                    {
                        value: 'Muscle Fascia',
                        text: 'Muscle Fascia'
                    },
                    {
                        value: 'Tone',
                        text: 'Tone'
                    },
                    {
                        value: 'Band',
                        text: 'Band'
                    },
                    {
                        value: 'Fibrosis',
                        text: 'Fibrosis'
                    },
                    {
                        value: 'Trigger point',
                        text: 'Trigger point'
                    },
                    {
                        value: 'Lump',
                        text: 'Lump'
                    },
                    {
                        value: 'Dead block',
                        text: 'Dead block'
                    },
                    {
                        value: 'Flattened/Inhibited',
                        text: 'Flattened/Inhibited'
                    },
                    {
                        value: 'Swelling',
                        text: 'Swelling'
                    },
                    {
                        value: 'Lymph congestion',
                        text: 'Lymph congestion'
                    },
                    {
                        value: 'Cellulite',
                        text: 'Cellulite'
                    }
                ];

                // Append the option elements to the select dropdown
                optionElements.forEach(optionData => {
                    const optElement = document.createElement('option');
                    optElement.value = optionData.value;
                    optElement.textContent = optionData.text;
                    if (optionData.disabled) optElement.disabled = true;
                    if (optionData.selected) optElement.selected = true;
                    findingsSelect.appendChild(optElement);
                });

                // Input for remarks (text input)
                const remarksInput = document.createElement('input');
                remarksInput.type = 'text';
                remarksInput.name = 'remarks[]';
                remarksInput.placeholder = 'Remarks';

                // Delete button
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.textContent = 'Delete';
                deleteBtn.className = 'delete-btn';
                deleteBtn.onclick = () => document.getElementById(rowId).remove();

                // Append elements to the row
                rowDiv.appendChild(tissueInput);
                rowDiv.appendChild(findingsSelect);
                rowDiv.appendChild(remarksInput);
                rowDiv.appendChild(deleteBtn);

                // Append the row to the assessment-rows container (bottom of the list)
                document.getElementById('assessment-rows').appendChild(rowDiv);
            }

            function addRowExercise(situation) {
                const rowDiv = document.createElement('div');
                rowDiv.className = 'form-row';

                // Input for exercise suggestion (text input)
                const suggestionInput = document.createElement('input');
                suggestionInput.type = 'text';
                suggestionInput.name = 'suggestion[]';
                suggestionInput.placeholder = 'Exercise Suggestion';

                // Input for date (date input)
                const dateInput = document.createElement('input');
                dateInput.type = 'date';
                dateInput.name = 'date[]';
                dateInput.placeholder = 'Date';

                // Input for remarks (text input)
                const remarksInput = document.createElement('input');
                remarksInput.type = 'text';
                remarksInput.name = 'remarks[]';
                remarksInput.placeholder = 'Remarks';

                // Hidden input for situation
                const situationInput = document.createElement('input');
                situationInput.type = 'hidden';
                situationInput.name = 'situation[]';
                situationInput.value = situation;

                // Delete button
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.textContent = 'Delete';
                deleteBtn.className = 'delete-btn';
                deleteBtn.onclick = () => rowDiv.remove(); // Remove the row when clicked

                // Append elements to the row
                rowDiv.appendChild(suggestionInput);
                rowDiv.appendChild(dateInput);
                rowDiv.appendChild(remarksInput);
                rowDiv.appendChild(situationInput);
                rowDiv.appendChild(deleteBtn);

                // Append the new row to the appropriate situation section
                if (situation === 'Severe') {
                    document.getElementById('severe-rows').appendChild(rowDiv);
                } else if (situation === 'Moderate') {
                    document.getElementById('moderate-rows').appendChild(rowDiv);
                } else if (situation === 'Low') {
                    document.getElementById('low-rows').appendChild(rowDiv);
                }
            }

            function navigateToPage(page) {
                // Set the form action to the desired page
                document.getElementById('navigationForm').action = page;
                // Submit the form
                document.getElementById('navigationForm').submit();
            }
        </script>
    </main>
</body>
<?php include 'includes/footer.php'; ?>

</html>