<?php
session_start();
require_once "../db_connection.php";

if (!isset($_SESSION['UserID']) || !isset($_POST['answers']) || !is_array($_POST['answers'])) {
    header("Location: ../dashboard.php");
    exit;
}

$UserID = $_SESSION['UserID'];
$TestType = "Listening";
$DateTaken = date('Y-m-d H:i:s');

// Step 1: Determine next SetNumber
$takenSets = [];
$stmt = $conn->prepare("SELECT DISTINCT SetNumber FROM testresponses WHERE UserID = ? AND TestType = ?");
$stmt->bind_param("is", $UserID, $TestType);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $takenSets[] = (int)$row['SetNumber'];
}

$SetNumber = null;
for ($i = 1; $i <= 3; $i++) {
    if (!in_array($i, $takenSets)) {
        $SetNumber = $i;
        break;
    }
}

if (is_null($SetNumber)) {
    echo "<h2 style='text-align:center;'>You have already completed all Reading test sets.</h2>";
    echo "<div style='text-align:center;'><a href='../dashboard.php'>Go to Dashboard</a></div>";
    exit;
}

// Step 2: Fetch correct answers
$correctAnswers = [];
$stmt = $conn->prepare("SELECT QuestionID, CorrectAnswer FROM questions WHERE TestType = ? AND SetNumber = ?");
$stmt->bind_param("si", $TestType, $SetNumber);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $correctAnswers[$row['QuestionID']] = strtolower(trim($row['CorrectAnswer']));
}

// Step 3: Insert into tests table
$stmt = $conn->prepare("INSERT INTO tests (UserID, TestType, TestDate) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $UserID, $TestType, $DateTaken);
$stmt->execute();
$TestID = $conn->insert_id;

// Step 4: Evaluate and store responses
$Score = 0;
foreach ($_POST['answers'] as $QuestionID => $UserResponse) {
    $UserResponseClean = strtolower(trim($UserResponse));
    $CorrectAnswer = $correctAnswers[$QuestionID] ?? '';
    $IsCorrect = ($UserResponseClean === $CorrectAnswer) ? 1 : 0;
    if ($IsCorrect) $Score++;

    $stmt = $conn->prepare("INSERT INTO testresponses (TestID, QuestionID, UserResponse, IsCorrect, SetNumber, TestType, UserID) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisissi", $TestID, $QuestionID, $UserResponse, $IsCorrect, $SetNumber, $TestType, $UserID);
    $stmt->execute();
}

// Step 5: Calculate Band Score
switch (true) {
    case $Score >= 39: $BandScore = 9.0; break;
    case $Score >= 37: $BandScore = 8.5; break;
    case $Score >= 35: $BandScore = 8.0; break;
    case $Score >= 32: $BandScore = 7.5; break;
    case $Score >= 30: $BandScore = 7.0; break;
    case $Score >= 26: $BandScore = 6.5; break;
    case $Score >= 23: $BandScore = 6.0; break;
    case $Score >= 18: $BandScore = 5.5; break;
    case $Score >= 16: $BandScore = 5.0; break;
    case $Score >= 13: $BandScore = 4.5; break;
    case $Score >= 10: $BandScore = 4.0; break;
    case $Score >= 7:  $BandScore = 3.5; break;
    case $Score >= 5:  $BandScore = 3.0; break;
    case $Score >= 3:  $BandScore = 2.5; break;
    case $Score >= 1:  $BandScore = 2.0; break;
    default: $BandScore = 1.0; break;
}


// Step 6: Insert into testresults
$stmt = $conn->prepare("INSERT INTO testresults (TestID, UserID, TestType, Score, DateTaken, TestDate) 
                        VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisiss", $TestID, $UserID, $TestType, $BandScore, $DateTaken, $DateTaken);
$stmt->execute();

// Step 7: Update tests table with band and feedback
if ($BandScore >= 8) {
    $feedback = "Excellent! You’re ready for top universities.";
} elseif ($BandScore >= 7) {
    $feedback = "Great work! Try to improve accuracy for Band 8+.";
} elseif ($BandScore >= 6) {
    $feedback = "Good effort! Practice more to push above Band 7.";
} elseif ($BandScore >= 5) {
    $feedback = "You're getting there. Focus on key words and synonyms.";
} else {
    $feedback = "Keep practicing daily. Try to identify main ideas.";
}

$stmt = $conn->prepare("UPDATE tests SET BandScore = ?, Feedback = ? WHERE TestID = ?");
$stmt->bind_param("dsi", $BandScore, $feedback, $TestID);
$stmt->execute();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Listening Test Result</title>
    <style>
        body {
            margin: 0;
            background: linear-gradient(135deg, #f3e5f5, #ffffff);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            perspective: 1200px;
            overflow: hidden;
        }

        .result-container {
            background: #ffffff;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            text-align: center;
            transform-style: preserve-3d;
            animation: floatCard 6s ease-in-out infinite;
            transition: transform 0.3s ease;
        }

        @keyframes floatCard {
            0% { transform: rotateX(0deg) rotateY(0deg) translateY(0px); }
            50% { transform: rotateX(3deg) rotateY(-3deg) translateY(-10px); }
            100% { transform: rotateX(0deg) rotateY(0deg) translateY(0px); }
        }

        .result-container h1 {
            color: #4a148c;
            font-size: 36px;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .score {
            font-size: 50px;
            font-weight: bold;
            color: #8e24aa;
            margin: 20px 0;
        }

        .feedback {
            font-size: 20px;
            color: #6a1b9a;
            margin-bottom: 30px;
        }

        .btn {
    display: inline-block;
    text-decoration: none;
    padding: 14px 35px;
    background: linear-gradient(135deg, #6a1b9a, #ab47bc);
    color: white;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.btn:hover {
    background: linear-gradient(135deg, #ab47bc, #ce93d8);
    transform: scale(1.05);
    box-shadow: 0 12px 20px rgba(0,0,0,0.2);
}

    </style>
</head>
<body>
    <div class="result-container">
        <h1>Test Submitted!</h1>
        <div class="score">Band Score: <?= htmlspecialchars($BandScore) ?></div>
        <div class="feedback"><?= htmlspecialchars($feedback) ?></div>
        <a href="../dashboard.php" class="btn">Go to Dashboard</a>
</body>
</html>
