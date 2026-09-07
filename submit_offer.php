<?php
include 'db_connection.php';// Database connection - update credentials as needed
$host = "localhost";
$user = "root";
$password = "";
$dbname = "aspireielts";  // CHANGE this to your DB name

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("<div class='error-box'>Database connection failed: " . $conn->connect_error . "</div>");
}

// Helper: upload file and return relative path or null if optional and not uploaded
function uploadFile($fieldName, $fullName, $required = true) {
    if (!isset($_FILES[$fieldName])) {
        if ($required) die("<div class='error-box'>File upload error: $fieldName missing.</div>");
        return null;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) die("<div class='error-box'>Required file '$fieldName' not uploaded.</div>");
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("<div class='error-box'>Upload error on '$fieldName': " . $file['error'] . "</div>");
    }

    $uploadDir = 'Offer Letter/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Sanitize full name to use in filename
    $safeName = preg_replace("/[^A-Za-z0-9]/", "", $fullName);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg','jpeg','png','pdf'];
    if (!in_array($ext, $allowedExts)) {
        die("<div class='error-box'>Invalid file type for $fieldName. Allowed: jpg, jpeg, png, pdf</div>");
    }

    // Filename pattern: offerletter/FullName_fieldname.ext
    $filename = $uploadDir . $safeName . "_" . $fieldName . "." . $ext;

    if (!move_uploaded_file($file['tmp_name'], $filename)) {
        die("<div class='error-box'>Failed to move uploaded file for $fieldName</div>");
    }

    return $filename;
}

// Collect & sanitize POST data
// Collect & sanitize POST data
$full_name = trim($_POST['full_name']);
$father_name = trim($_POST['father_name']);
$mother_name = trim($_POST['mother_name']);
$dob = $_POST['dob'];
$nationality = trim($_POST['nationality']);
$religion = trim($_POST['religion']);
$marital_status = trim($_POST['marital_status']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$present_address = trim($_POST['present_address']);
$ssc_year = $_POST['ssc_year'];
$hsc_year = $_POST['hsc_year'];
$medium = $_POST['medium'];
$country_of_choice = trim($_POST['country_of_choice']);
$preferred_university = trim($_POST['preferred_university']);
$program_type = isset($_POST['program_type']) ? trim($_POST['program_type']) : ''; // optional or renamed field
$preferred_subject = trim($_POST['preferred_subject']);
$intake_season = $_POST['intake_season'];
$intake_year = $_POST['intake_year'];
$already_applied = $_POST['already_applied'];


// Upload files, respecting required or optional
$nid_card_path = uploadFile('nid_card', $full_name, true);
$ssc_certificate_path = uploadFile('ssc_certificate', $full_name, true);
$hsc_certificate_path = uploadFile('hsc_certificate', $full_name, true);
$passport_scan_path = uploadFile('passport_scan', $full_name, false);
$english_cert_path = uploadFile('english_cert', $full_name, true);
$father_nid_path = uploadFile('father_nid', $full_name, true);
$mother_nid_path = uploadFile('mother_nid', $full_name, true);
$lor_path = uploadFile('lor', $full_name, true);
$sop_path = uploadFile('sop', $full_name, false);
$cv_path = uploadFile('cv', $full_name, false);
$passport_photo_path = uploadFile('passport_photo', $full_name, true);

// Prepare SQL with placeholders for optional fields that may be null
$sql = "INSERT INTO offer_letter_requests 
(full_name, father_name, mother_name, date_of_birth, nationality, religion, marital_status, phone_number, email, present_address,
ssc_year, hsc_year, medium_of_study, nid_card_path, ssc_certificate_path, hsc_certificate_path, passport_scan_path,
english_cert_path, father_nid_path, mother_nid_path, lor_path, sop_path, cv_path, passport_photo_path,
country_of_choice, preferred_university, program_type, preferred_subject, intake_season, intake_year, already_applied, submitted_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssssssssssssssssssssssssssssss",
    $full_name, $father_name, $mother_name, $dob, $nationality, $religion, $marital_status, $phone, $email, $present_address,
    $ssc_year, $hsc_year, $medium, $nid_card_path, $ssc_certificate_path, $hsc_certificate_path, $passport_scan_path,
    $english_cert_path, $father_nid_path, $mother_nid_path, $lor_path, $sop_path, $cv_path, $passport_photo_path,
    $country_of_choice, $preferred_university, $program_type, $preferred_subject, $intake_season, $intake_year, $already_applied
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Submit Offer - AspireIELTS</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #667eea, #764ba2);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff;
    }
    .container {
        background: rgba(255, 255, 255, 0.1);
        padding: 30px 40px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        max-width: 480px;
        text-align: center;
        backdrop-filter: blur(10px);
    }
    h1 {
        font-weight: 600;
        margin-bottom: 20px;
    }
    .success {
        font-size: 1.3rem;
        margin: 20px 0;
        padding: 15px;
        border-radius: 8px;
        background-color: #28a745aa;
        color: #fff;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.4);
    }
    .error-box {
        background-color: #dc3545cc;
        padding: 15px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(220, 53, 69, 0.5);
    }
    .btn {
        background: #fff;
        color: #764ba2;
        font-weight: 600;
        padding: 12px 30px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        font-size: 1rem;
        transition: background 0.3s ease, color 0.3s ease;
        box-shadow: 0 4px 12px rgba(118, 75, 162, 0.4);
        text-decoration: none;
        display: inline-block;
        margin-top: 25px;
        user-select: none;
    }
    .btn:hover {
        background: #5a3382;
        color: #fff;
        box-shadow: 0 6px 15px rgba(90, 51, 130, 0.7);
    }
    .loading-spinner {
        border: 4px solid rgba(255,255,255,0.2);
        border-top: 4px solid #fff;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        margin: 30px auto;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        to {transform: rotate(360deg);}
    }
</style>
</head>
<body>
<div class="container">
<?php if ($stmt->execute()): ?>
    <h1>Thank you, <?= htmlspecialchars($full_name) ?>!</h1>
    <div class="success">Your offer letter request has been submitted successfully.</div>
    <a href="dashboard.php" class="btn">Back to Dashboard</a>
<?php else: ?>
    <h1>Oops! Something went wrong.</h1>
    <div class="error-box">Database insert error: <?= htmlspecialchars($stmt->error) ?></div>
    <a href="offerletter.php" class="btn">Try Again</a>
<?php endif; ?>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
