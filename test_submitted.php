<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get test section from URL parameter (e.g., Listening, Reading, Writing, Speaking)
$section = isset($_GET['section']) ? ucfirst(htmlspecialchars($_GET['section'])) : "Test";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $section; ?> Submitted</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }
        h2 {
            color: #28a745;
        }
        p {
            font-size: 18px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 16px;
            color: white;
            background: #007bff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo $section; ?> Test Submitted Successfully!</h2>
        <p>Your test has been submitted. You can return to the dashboard now.</p>
        <a href="dashboard.php" class="btn">Go to Dashboard</a>
    </div>
</body>
</html>
