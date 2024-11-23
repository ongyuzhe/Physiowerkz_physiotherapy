<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "includes/settings.php";

// Default value for full name
$full_name = '';

// Fetch user details from the database
if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];

    $sql = "SELECT first_name, last_name FROM patients WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            $stmt->bind_result($first_name, $last_name);
            $stmt->fetch();

            // Combine first name and last name
            $full_name = $first_name . ' ' . $last_name;
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

        $stmt->close();
    }
}

// Run the cleanup process every time the page is loaded
// Step 1: Find all usernames that have more than one record
$sql = "SELECT username, MAX(id) AS max_id 
        FROM health_questionnaire 
        GROUP BY username 
        HAVING COUNT(username) > 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Step 2: For each duplicate username, delete all records except the one with the largest ID
    while ($row = $result->fetch_assoc()) {
        $username = $row['username'];
        $max_id = $row['max_id'];

        // Step 3: Delete records for the same username where id < max_id
        $delete_sql = "DELETE FROM health_questionnaire WHERE username = ? AND id < ?";
        if ($stmt = $conn->prepare($delete_sql)) {
            $stmt->bind_param('si', $username, $max_id);
            $stmt->execute();  // Make sure to execute the deletion
            $stmt->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'] ?? 'user';
    $know_us = $_POST['know_us'] ?? '';
    $referrer = $_POST['referrer'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $nric_passport_no = $_POST['nric_passport_no'] ?? '';
    $profession = $_POST['profession'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $country = $_POST['country'] ?? ''; // Get the country if non-Malaysian
    $religion = $_POST['religion'] ?? '';
    $title = $_POST['title'] ?? ''; // Add this line to get the title value
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    $relationship = $_POST['relationship'] ?? '';
    $emergency_phone = $_POST['emergency_phone'] ?? '';
    $family_doctor = $_POST['family_doctor'] ?? '';
    $clinic_address = $_POST['clinic_address'] ?? '';
    $clinic_number = $_POST['clinic_number'] ?? '';
    $newsletter = $_POST['newsletter'] ?? '';
    $hospitalized = $_POST['hospitalized'] ?? '';
    $hospitalized_details = $_POST['hospitalized_details'] ?? '';
    $physical_activity = $_POST['physical_activity'] ?? '';
    $chest_pain_activity = $_POST['chest_pain_activity'] ?? '';
    $chest_pain_rest = $_POST['chest_pain_rest'] ?? '';
    $heart_disease = $_POST['heart_disease'] ?? '';
    $stroke = $_POST['stroke'] ?? '';
    $high_blood_pressure = $_POST['high_blood_pressure'] ?? '';
    $diabetes = $_POST['diabetes'] ?? '';
    $asthma = $_POST['asthma'] ?? '';
    $osteoporosis = $_POST['osteoporosis'] ?? '';
    $epilepsy = $_POST['epilepsy'] ?? '';
    $cancer = $_POST['cancer'] ?? '';
    $weight_loss = $_POST['weight_loss'] ?? '';
    $dizziness = $_POST['dizziness'] ?? '';
    $smoking = $_POST['smoking'] ?? '';
    $pregnant = $_POST['pregnant'] ?? '';
    $fracture_accident = $_POST['fracture_accident'] ?? '';
    $blood_disorder = $_POST['blood_disorder'] ?? '';
    $other_conditions = $_POST['other_conditions'] ?? '';
    $pain_killers = $_POST['pain_killers'] ?? '';
    $inhaler = $_POST['inhaler'] ?? '';
    $blood_thinners = $_POST['blood_thinners'] ?? '';
    $steroids = $_POST['steroids'] ?? '';
    $other_medications = $_POST['other_medications'] ?? '';
    $signature = $_POST['signature'] ?? '';

    // Retrieve phone number and country code
    $country_code = $_POST['country_code'] ?? '+60'; // Default to Malaysia if not set
    $phone_number = $_POST['phone_number'] ?? '';
    $full_phone_number = $country_code . $phone_number; // Combine country code and phone number

    $emergency_contact_full = $title . " " . $emergency_contact;

    // Ensure the signatures directory exists
    $signatures_dir = 'signatures/';
    if (!is_dir($signatures_dir)) {
        mkdir($signatures_dir, 0777, true);
    }

    // Save the signature image
    if ($signature) {
        $signature = str_replace('data:image/png;base64,', '', $signature);
        $signature = str_replace(' ', '+', $signature);
        $signatureData = base64_decode($signature);
        $signatureFileName = $signatures_dir . $username . '_' . uniqid() . '.png';
        file_put_contents($signatureFileName, $signatureData);
    } else {
        $signatureFileName = '';
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO health_questionnaire (username, know_us, referrer, full_name, nric_passport_no, profession, nationality, country, religion, emergency_contact, relationship, emergency_phone, family_doctor, clinic_address, clinic_number, newsletter, hospitalized, hospitalized_details, physical_activity, chest_pain_activity, chest_pain_rest, heart_disease, stroke, high_blood_pressure, diabetes, asthma, osteoporosis, epilepsy, cancer, weight_loss, dizziness, smoking, pregnant, fracture_accident, blood_disorder, other_conditions, pain_killers, inhaler, blood_thinners, steroids, other_medications, signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param(
            "ssssssssssssssssssssssssssssssssssssssssss",
            $username,
            $know_us,
            $referrer,
            $full_name,
            $nric_passport_no,
            $profession,
            $nationality,
            $country,
            $religion,
            $emergency_contact_full,
            $relationship,
            $full_phone_number,
            $family_doctor,
            $clinic_address,
            $clinic_number,
            $newsletter,
            $hospitalized,
            $hospitalized_details,
            $physical_activity,
            $chest_pain_activity,
            $chest_pain_rest,
            $heart_disease,
            $stroke,
            $high_blood_pressure,
            $diabetes,
            $asthma,
            $osteoporosis,
            $epilepsy,
            $cancer,
            $weight_loss,
            $dizziness,
            $smoking,
            $pregnant,
            $fracture_accident,
            $blood_disorder,
            $other_conditions,
            $pain_killers,
            $inhaler,
            $blood_thinners,
            $steroids,
            $other_medications,
            $signatureFileName
        );

        if ($stmt->execute()) {
            // Prepare the update statement
            $update_stmt = $conn->prepare("UPDATE patients SET ques_status = 1 WHERE username = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("s", $username);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                echo "<script>alert('Error preparing update statement: " . $conn->error . "');</script>";
            }

            echo "<script>
                alert('Questionnaire submitted successfully.');
                window.location.href='welcome.php';
              </script>";
        } else {
            echo "<script>
                alert('Error: " . $stmt->error . "');
              </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
            alert('Error preparing the statement: " . $conn->error . "');
          </script>";
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Questionnaire</title>
    <link href="images/logo.png" rel="icon">
</head>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
    }

    .health-form {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: left;
    }

    .form-group {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .form-group label {
        margin-right: 20px;
        width: 90px;
        font-weight: bold;
        color: #333;
    }

    .form-group input[type="text"] {
        flex: 1;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        margin-right: 20px;
    }

    .form-group input[type="text"]:last-child {
        margin-right: 0;
    }

    .form-options {
        display: flex;
        flex-wrap: wrap;
    }

    .form-options input[type="radio"] {
        margin-right: 5px;
    }

    .form-options label {
        margin-right: 15px;
    }

    input[type="text"] {
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    select {
        padding: 8px 30px 8px 10px;
        ;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        background-color: white;
        appearance: none;
        /* Removes default styling */
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><polygon points="0,0 10,0 5,5" fill="%23777"/></svg>');
        /* Custom arrow */
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 10px;
    }

    select:focus {
        border-color: #007BFF;
        outline: none;
    }

    .navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    button {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        background-color: #007BFF;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #0056b3;
    }

    .conditions-table {
        margin-top: 20px;
        max-width: 800px;
        margin: auto;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .conditions-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .conditions-table th,
    .conditions-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .conditions-table th {
        background-color: #f9f9f9;
        font-weight: bold;
    }

    .conditions-table tr:hover {
        background-color: #f1f1f1;
    }

    .conditions-table input[type="checkbox"] {
        margin-right: 5px;
    }

    .conditions-table input[type="text"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .conditions-table input[type="text"]:focus {
        border-color: #007BFF;
        outline: none;
    }

    .medications-table {
        margin-top: 20px;
        max-width: 800px;
        margin: auto;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .medications-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .medications-table th,
    .medications-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .medications-table th {
        background-color: #f9f9f9;
        font-weight: bold;
    }

    .medications-table tr:hover {
        background-color: #f1f1f1;
    }

    .medications-table input[type="checkbox"] {
        margin-right: 5px;
    }

    .medications-table input[type="text"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .medications-table input[type="text"]:focus {
        border-color: #007BFF;
        outline: none;
    }

    .signature {
        max-width: 400px;
        margin: 20px 0;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: left;
    }

    .signature label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
        color: #333;
        text-align: left;
    }

    .signature-pad {
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #f9f9f9;
        cursor: crosshair;
        display: block;
        margin-bottom: 10px;
    }

    button#clear {
        margin-top: 10px;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        background-color: #dc3545;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    #clear {
        margin-top: 10px;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        background-color: #dc3545;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    #clear:hover {
        background-color: #c82333;
    }

    .form-group p {
        text-align: justify;
        margin: 10px 0;
    }

    .form-group1 {
        margin-bottom: 20px;
    }

    .form-group label,
    .form-options label {
        font-size: 14px;
    }

    .referrer-table {
        margin: 20px auto;
        border-collapse: collapse;
        width: 100%;
    }

    .referrer-table td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: left;
        width: 25%;
    }

    .referrer-table input[type="radio"] {
        margin-right: 5px;
    }

    .referrer-table input[type="text"] {
        width: 100%;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const canvas = document.getElementById('signature-pad');
        const clearButton = document.getElementById('clear');
        const signatureInput = document.getElementById('signature');
        const signaturePad = new SignaturePad(canvas);

        clearButton.addEventListener('click', () => {
            signaturePad.clear();
        });

        document.querySelector('form').addEventListener('submit', (event) => {
            if (!signaturePad.isEmpty()) {
                signatureInput.value = signaturePad.toDataURL();
            } else {
                alert("Please provide a signature.");
                event.preventDefault();
            }
        });
    });
</script>
<?php include 'includes/header_patient.php'; ?>

<body class="health-form-body">
    <main class="health-form-main">
        <h1>Help Us to Know You Better</h1>
        <form class="health-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h3>How do you know us?</h3>
            <div class="form-group">
                <div class="form-options">
                    <div class="option">
                        <table class="referrer-table">
                            <tr>
                                <td>
                                    <input type="radio" id="internet" name="know_us" value="Internet">
                                    <label for="internet">Internet (which channel)</label>
                                </td>
                                <td>
                                    <input type="radio" id="friend" name="know_us" value="Referred by friend or family">
                                    <label for="friend">Referred by friend or family (name of referrer)</label>
                                </td>
                                <td>
                                    <input type="radio" id="doctor" name="know_us" value="Referred by doctor">
                                    <label for="doctor">Referred by doctor (Name of the doctor)</label>
                                </td>
                                <td>
                                    <input type="radio" id="others" name="know_us" value="Others">
                                    <label for="others">Others</label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <input type="text" name="referrer" placeholder="Please specify for the option you selected.">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>

                <label for="nric_passport_no">NRIC / Passport No:</label>
                <input type="text" id="nric_passport_no" name="nric_passport_no" required>
            </div>

            <div class="form-group">
                <label for="profession">Profession:</label>
                <input type="text" id="profession" name="profession" required>
                <label for="nationality">Nationality:</label>
                <div class="select-container-nationality">
                    <select id="nationality" name="nationality" class="dropdown-nationality" required>
                        <option value="" disabled selected>Select Nationality</option>
                        <option value="Malaysian">Malaysian</option>
                        <option value="Non-Malaysian">Non-Malaysian</option>
                    </select>
                    <select id="non-malaysian-country" name="country" class="dropdown-country" style="display:none;">
                        <option value="" disabled selected>Select Country</option>
                        <option value="Afghanistan">Afghanistan</option>
                        <option value="Albania">Albania</option>
                        <option value="Algeria">Algeria</option>
                        <option value="Andorra">Andorra</option>
                        <option value="Angola">Angola</option>
                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                        <option value="Argentina">Argentina</option>
                        <option value="Armenia">Armenia</option>
                        <option value="Australia">Australia</option>
                        <option value="Austria">Austria</option>
                        <option value="Azerbaijan">Azerbaijan</option>
                        <option value="Bahamas">Bahamas</option>
                        <option value="Bahrain">Bahrain</option>
                        <option value="Bangladesh">Bangladesh</option>
                        <option value="Barbados">Barbados</option>
                        <option value="Belarus">Belarus</option>
                        <option value="Belgium">Belgium</option>
                        <option value="Belize">Belize</option>
                        <option value="Benin">Benin</option>
                        <option value="Bhutan">Bhutan</option>
                        <option value="Bolivia">Bolivia</option>
                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                        <option value="Botswana">Botswana</option>
                        <option value="Brazil">Brazil</option>
                        <option value="Brunei">Brunei</option>
                        <option value="Bulgaria">Bulgaria</option>
                        <option value="Burkina Faso">Burkina Faso</option>
                        <option value="Burundi">Burundi</option>
                        <option value="Cabo Verde">Cabo Verde</option>
                        <option value="Cambodia">Cambodia</option>
                        <option value="Cameroon">Cameroon</option>
                        <option value="Canada">Canada</option>
                        <option value="Central African Republic">Central African Republic</option>
                        <option value="Chad">Chad</option>
                        <option value="Chile">Chile</option>
                        <option value="China">China</option>
                        <option value="Colombia">Colombia</option>
                        <option value="Comoros">Comoros</option>
                        <option value="Congo">Congo</option>
                        <option value="Costa Rica">Costa Rica</option>
                        <option value="Croatia">Croatia</option>
                        <option value="Cuba">Cuba</option>
                        <option value="Cyprus">Cyprus</option>
                        <option value="Czech Republic">Czech Republic</option>
                        <option value="Denmark">Denmark</option>
                        <option value="Djibouti">Djibouti</option>
                        <option value="Dominica">Dominica</option>
                        <option value="Dominican Republic">Dominican Republic</option>
                        <option value="Ecuador">Ecuador</option>
                        <option value="Egypt">Egypt</option>
                        <option value="El Salvador">El Salvador</option>
                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                        <option value="Eritrea">Eritrea</option>
                        <option value="Estonia">Estonia</option>
                        <option value="Eswatini">Eswatini</option>
                        <option value="Ethiopia">Ethiopia</option>
                        <option value="Fiji">Fiji</option>
                        <option value="Finland">Finland</option>
                        <option value="France">France</option>
                        <option value="Gabon">Gabon</option>
                        <option value="Gambia">Gambia</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Germany">Germany</option>
                        <option value="Ghana">Ghana</option>
                        <option value="Greece">Greece</option>
                        <option value="Grenada">Grenada</option>
                        <option value="Guatemala">Guatemala</option>
                        <option value="Guinea">Guinea</option>
                        <option value="Guinea-Bissau">Guinea-Bissau</option>
                        <option value="Guyana">Guyana</option>
                        <option value="Haiti">Haiti</option>
                        <option value="Honduras">Honduras</option>
                        <option value="Hungary">Hungary</option>
                        <option value="Iceland">Iceland</option>
                        <option value="India">India</option>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Iran">Iran</option>
                        <option value="Iraq">Iraq</option>
                        <option value="Ireland">Ireland</option>
                        <option value="Israel">Israel</option>
                        <option value="Italy">Italy</option>
                        <option value="Jamaica">Jamaica</option>
                        <option value="Japan">Japan</option>
                        <option value="Jordan">Jordan</option>
                        <option value="Kazakhstan">Kazakhstan</option>
                        <option value="Kenya">Kenya</option>
                        <option value="Kiribati">Kiribati</option>
                        <option value="Korea, North">Korea, North</option>
                        <option value="Korea, South">Korea, South</option>
                        <option value="Kosovo">Kosovo</option>
                        <option value="Kuwait">Kuwait</option>
                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                        <option value="Laos">Laos</option>
                        <option value="Latvia">Latvia</option>
                        <option value="Lebanon">Lebanon</option>
                        <option value="Lesotho">Lesotho</option>
                        <option value="Liberia">Liberia</option>
                        <option value="Libya">Libya</option>
                        <option value="Liechtenstein">Liechtenstein</option>
                        <option value="Lithuania">Lithuania</option>
                        <option value="Luxembourg">Luxembourg</option>
                        <option value="Madagascar">Madagascar</option>
                        <option value="Malawi">Malawi</option>
                        <option value="Malaysia">Malaysia</option>
                        <option value="Maldives">Maldives</option>
                        <option value="Mali">Mali</option>
                        <option value="Malta">Malta</option>
                        <option value="Marshall Islands">Marshall Islands</option>
                        <option value="Mauritania">Mauritania</option>
                        <option value="Mauritius">Mauritius</option>
                        <option value="Mexico">Mexico</option>
                        <option value="Micronesia">Micronesia</option>
                        <option value="Moldova">Moldova</option>
                        <option value="Monaco">Monaco</option>
                        <option value="Mongolia">Mongolia</option>
                        <option value="Montenegro">Montenegro</option>
                        <option value="Morocco">Morocco</option>
                        <option value="Mozambique">Mozambique</option>
                        <option value="Myanmar">Myanmar</option>
                        <option value="Namibia">Namibia</option>
                        <option value="Nauru">Nauru</option>
                        <option value="Nepal">Nepal</option>
                        <option value="Netherlands">Netherlands</option>
                        <option value="New Zealand">New Zealand</option>
                        <option value="Nicaragua">Nicaragua</option>
                        <option value="Niger">Niger</option>
                        <option value="Nigeria">Nigeria</option>
                        <option value="North Macedonia">North Macedonia</option>
                        <option value="Norway">Norway</option>
                        <option value="Oman">Oman</option>
                        <option value="Pakistan">Pakistan</option>
                        <option value="Palau">Palau</option>
                        <option value="Palestine">Palestine</option>
                        <option value="Panama">Panama</option>
                        <option value="Papua New Guinea">Papua New Guinea</option>
                        <option value="Paraguay">Paraguay</option>
                        <option value="Peru">Peru</option>
                        <option value="Philippines">Philippines</option>
                        <option value="Poland">Poland</option>
                        <option value="Portugal">Portugal</option>
                        <option value="Qatar">Qatar</option>
                        <option value="Romania">Romania</option>
                        <option value="Russia">Russia</option>
                        <option value="Rwanda">Rwanda</option>
                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                        <option value="Saint Lucia">Saint Lucia</option>
                        <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                        <option value="Samoa">Samoa</option>
                        <option value="San Marino">San Marino</option>
                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                        <option value="Saudi Arabia">Saudi Arabia</option>
                        <option value="Senegal">Senegal</option>
                        <option value="Serbia">Serbia</option>
                        <option value="Seychelles">Seychelles</option>
                        <option value="Sierra Leone">Sierra Leone</option>
                        <option value="Singapore">Singapore</option>
                        <option value="Slovakia">Slovakia</option>
                        <option value="Slovenia">Slovenia</option>
                        <option value="Solomon Islands">Solomon Islands</option>
                        <option value="Somalia">Somalia</option>
                        <option value="South Africa">South Africa</option>
                        <option value="South Sudan">South Sudan</option>
                        <option value="Spain">Spain</option>
                        <option value="Sri Lanka">Sri Lanka</option>
                        <option value="Sudan">Sudan</option>
                        <option value="Suriname">Suriname</option>
                        <option value="Sweden">Sweden</option>
                        <option value="Switzerland">Switzerland</option>
                        <option value="Syria">Syria</option>
                        <option value="Taiwan">Taiwan</option>
                        <option value="Tajikistan">Tajikistan</option>
                        <option value="Tanzania">Tanzania</option>
                        <option value="Thailand">Thailand</option>
                        <option value="Timor-Leste">Timor-Leste</option>
                        <option value="Togo">Togo</option>
                        <option value="Tonga">Tonga</option>
                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                        <option value="Tunisia">Tunisia</option>
                        <option value="Turkey">Turkey</option>
                        <option value="Turkmenistan">Turkmenistan</option>
                        <option value="Tuvalu">Tuvalu</option>
                        <option value="Uganda">Uganda</option>
                        <option value="Ukraine">Ukraine</option>
                        <option value="United Arab Emirates">United Arab Emirates</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="United States">United States</option>
                        <option value="Uruguay">Uruguay</option>
                        <option value="Uzbekistan">Uzbekistan</option>
                        <option value="Vanuatu">Vanuatu</option>
                        <option value="Vatican City">Vatican City</option>
                        <option value="Venezuela">Venezuela</option>
                        <option value="Vietnam">Vietnam</option>
                        <option value="Yemen">Yemen</option>
                        <option value="Zambia">Zambia</option>
                        <option value="Zimbabwe">Zimbabwe</option>
                    </select>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const nationalitySelect = document.getElementById('nationality');
                    const countrySelect = document.getElementById('non-malaysian-country');

                    nationalitySelect.addEventListener('change', function() {
                        if (this.value === 'Non-Malaysian') {
                            countrySelect.style.display = 'block';
                        } else {
                            countrySelect.style.display = 'none';
                            countrySelect.value = ''; // Clear the selected value
                        }
                    });
                });
            </script>

            <div class="form-group">
                <label for="religion">Religion:</label>
                <div class="form-options">
                    <input type="radio" id="buddhist" name="religion" value="Buddhist">
                    <label for="buddhist">Buddhist</label>
                    <input type="radio" id="islam" name="religion" value="Islam">
                    <label for="islam">Islam</label>
                    <input type="radio" id="christian" name="religion" value="Christian">
                    <label for="christian">Christian</label>
                    <input type="radio" id="hindu" name="religion" value="Hindu">
                    <label for="hindu">Hindu</label>
                    <input type="radio" id="religion-others" name="religion" value="Others">
                    <label for="religion-others">Other</label>
                </div>
            </div>

            <h3>In case of emergency please contact:</h3>
            <div class="form-group">
                <label for="title">Title:</label>
                <div class="select-container-title">
                    <select id="title" name="title" required class="dropdown-title">
                        <option value="" disabled selected>Select Title</option>
                        <option value="Mr">Mr</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Ms">Ms</option>
                    </select>
                </div>
                <input type="text" id="emergency_contact" name="emergency_contact" required>
                <label for="relationship">Relationship:</label>
                <div class="select-container-relationship">
                    <select id="relationship" name="relationship" required class="dropdown-relationship">
                        <option value="" disabled selected>Select Relationship</option>
                        <option value="Parent">Parent</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Friend">Friend</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <div class="phone-number-container">
                    <select id="country_code" name="country_code" class="dropdown-country-code" required>
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
                    <input type="text" id="phone_number" name="phone_number" class="input-phone-number" required>
                </div>
            </div>
            <div class="form-group">
                <label for="family_doctor">Family Doctor:</label>
                <input type="text" id="family_doctor" name="family_doctor">
            </div>
            <div class="form-group">
                <label for="clinic_address">Clinic Address:</label>
                <input type="text" id="clinic_address" name="clinic_address">
                <label for="clinic_number">Clinic Number:</label>
                <input type="text" id="clinic_number" name="clinic_number">
            </div>

            <div class="form-group">
                <p><strong>Appointment Policy:</strong> You may reschedule or cancel an appointment but it has to be made at least 48 hours in advance, failing which the session(s) will be chargeable.</p>
            </div>

            <div class="form-group">
                <p><strong>Sports & Clinical Massage Service Quality Policy:</strong> Should your case after assessment or 4 sessions seems to show no noticeable improvement, it may indicate more complexity in your case and as a result of that, in our internal service quality process, your case will be re-evaluated by our panel of senior physiotherapist and principal physiotherapist. They will also contact you to discuss on modifying your treatment process to ensure your recovery and safety, which is our top priority for you and all our patients.</p>
            </div>

            <label for="newsletter" class="custom-label"><strong>I would like to receive the latest promotion and newsletters:</strong></label><br><br>
            <div class="form-options1">
                <input type="radio" id="newsletter-yes" name="newsletter" value="Yes">
                <label for="newsletter-yes">Yes</label>
                <input type="radio" id="newsletter-no" name="newsletter" value="No">
                <label for="newsletter-no">No</label>
            </div>

            <h3>Medical History</h3>

            <label for="hospitalized" class="custom-label"><strong>Have you been hospitalized recently? If yes, please state:</strong></label><br><br>
            <div class="form-options1">
                <input type="radio" id="hospitalized-yes" name="hospitalized" value="Yes">
                <label for="hospitalized-yes">Yes</label>
                <input type="radio" id="hospitalized-no" name="hospitalized" value="No">
                <label for="hospitalized-no">No</label>
            </div>
            <input type="text" name="hospitalized_details" placeholder="Please specify if yes"><br><br>

            <div class="form-group1">
                <label for="physical_activity" class="custom-label"><strong>Has your doctor ever said you should only be doing physical activity recommended by a doctor?</strong></label><br><br>
                <div class="form-options1">
                    <input type="radio" id="physical_activity-yes" name="physical_activity" value="Yes">
                    <label for="physical_activity-yes">Yes</label>
                    <input type="radio" id="physical_activity-no" name="physical_activity" value="No">
                    <label for="physical_activity-no">No</label>
                </div>
            </div><br><br>

            <label for="chest_pain_activity"><strong>Do you feel pain in your chest when you undertake physical activity?</strong></label><br><br>
            <div class="form-options1">
                <input type="radio" id="chest_pain_activity-yes" name="chest_pain_activity" value="Yes">
                <label for="chest_pain_activity-yes">Yes</label>
                <input type="radio" id="chest_pain_activity-no" name="chest_pain_activity" value="No">
                <label for="chest_pain_activity-no">No</label>
            </div><br><br>

            <label for="chest_pain_rest"><strong>In the past month, have you had chest pain when you were not doing physical activity?</strong></label><br><br>
            <div class="form-options1">
                <input type="radio" id="chest_pain_rest-yes" name="chest_pain_rest" value="Yes">
                <label for="chest_pain_rest-yes">Yes</label>
                <input type="radio" id="chest_pain_rest-no" name="chest_pain_rest" value="No">
                <label for="chest_pain_rest-no">No</label>
            </div><br><br>

            <h3>Do you have the following conditions?</h3>
            <div class="conditions-table">
                <table>
                    <tr>
                        <td>Heart Disease</td>
                        <td><input type="checkbox" name="heart_disease" value="yes"></td>
                        <td>Stroke</td>
                        <td><input type="checkbox" name="stroke" value="yes"></td>
                    </tr>
                    <tr>
                        <td>High Blood Pressure</td>
                        <td><input type="checkbox" name="high_blood_pressure" value="yes"></td>
                        <td>Diabetes</td>
                        <td><input type="checkbox" name="diabetes" value="yes"></td>
                    </tr>
                    <tr>
                        <td>Asthma</td>
                        <td><input type="checkbox" name="asthma" value="yes"></td>
                        <td>Osteoporosis</td>
                        <td><input type="checkbox" name="osteoporosis" value="yes"></td>
                    </tr>
                    <tr>
                        <td>Epilepsy</td>
                        <td><input type="checkbox" name="epilepsy" value="yes"></td>
                        <td>Cancer</td>
                        <td><input type="checkbox" name="cancer" value="yes"></td>
                    </tr>
                    <tr>
                        <td>Weight Loss</td>
                        <td><input type="checkbox" name="weight_loss" value="yes"></td>
                        <td>Dizziness</td>
                        <td><input type="checkbox" name="dizziness" value="yes"></td>
                    </tr>
                    <tr>
                        <td>Smoking</td>
                        <td><input type="checkbox" name="smoking" value="yes"></td>
                        <td>Pregnant</td>
                        <td><input type="checkbox" name="pregnant" value="yes"></td>
                    </tr>
                    <tr>
                        <td>Fracture/Accident</td>
                        <td><input type="checkbox" name="fracture_accident" value="yes"></td>
                        <td>Blood Disorder</td>
                        <td><input type="checkbox" name="blood_disorder" value="yes"></td>
                    </tr>
                    <tr>
                        <td>Other Conditions</td>
                        <td colspan="3"><input type="text" name="other_conditions" placeholder="Please specify"></td>
                    </tr>
                </table>
            </div>

            <h3>Are you regularly taking the medications indicated below?</h3>
            <div class="medications-table">
                <table>
                    <tr>
                        <td>Pain Killers</td>
                        <td><input type="checkbox" name="pain_killers" value="yes"></td>
                        <td>Inhaler</td>
                        <td><input type="checkbox" name="inhaler" value="yes"></td>
                    </tr>
                    <tr>
                        <td>Blood Thinners</td>
                        <td><input type="checkbox" name="blood_thinners" value="yes"></td>
                        <td>Steroids</td>
                        <td><input type="checkbox" name="steroids" value="yes"></td>
                    </tr>
                    <tr>
                        <td>Other Medications</td>
                        <td colspan="3"><input type="text" name="other_medications" placeholder="Please specify"></td>
                    </tr>
                </table>
            </div>

            <div class="signature">
                <label for="signature">Signature:</label>
                <canvas id="signature-pad" class="signature-pad" width="300" height="100"></canvas>
                <input type="hidden" name="signature" id="signature"><br>
                <button type="button" id="clear">Clear</button>
            </div>

            <button type="submit">Submit</button>

        </form>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>