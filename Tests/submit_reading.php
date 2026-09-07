<?php
session_start();
require_once "../db_connection.php";

$UserID = $_SESSION['UserID'];
$TestType = "Reading";
$DateTaken = date('Y-m-d H:i:s');

// STEP 0: Determine which reading SetNumber to assign
$checkSetsQuery = "SELECT DISTINCT SetNumber FROM testresponses WHERE UserID = ? AND TestType = ?";
$stmt = $conn->prepare($checkSetsQuery);
$stmt->bind_param("is", $UserID, $TestType);
$stmt->execute();
$result = $stmt->get_result();

$takenSets = [];
while ($row = $result->fetch_assoc()) {
    $takenSets[] = $row['SetNumber'];
}

$SetNumber = null;
for ($i = 1; $i <= 3; $i++) {
    if (!in_array($i, $takenSets)) {
        $SetNumber = $i;
        break;
    }
}

if ($SetNumber === null) {
    echo "<h2 style='text-align:center;'>You have already completed all Reading test sets.</h2>";
    echo "<div style='text-align:center;'><a href='../dashboard.php'>Go to Dashboard</a></div>";
    exit;
}

// STEP 1: Get correct answers from questions table
$query = "SELECT QuestionID, CorrectAnswer FROM questions WHERE TestType = ? AND SetNumber = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $TestType, $SetNumber);
$stmt->execute();
$result = $stmt->get_result();

$correctAnswers = [];
while ($row = $result->fetch_assoc()) {
    $correctAnswers[$row['QuestionID']] = strtolower(trim($row['CorrectAnswer']));
}

// STEP 2: Create a new TestID in tests table
$insertTestQuery = "INSERT INTO tests (UserID, TestType, TestDate) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insertTestQuery);
$stmt->bind_param("iss", $UserID, $TestType, $DateTaken);
$stmt->execute();
$TestID = $conn->insert_id;

// STEP 3: Store user answers and calculate score
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

// STEP 4: Convert score to band (adjust if needed)
$BandScore = 0;
if ($Score >= 40) $BandScore = 9;
else if ($Score == 39) $BandScore = 8.5;
else if ($Score >= 37) $BandScore = 8;
else if ($Score == 36) $BandScore = 7.5;
else if ($Score >= 34) $BandScore = 7;
else if ($Score >= 32) $BandScore = 6.5;
else if ($Score >= 30) $BandScore = 6;
else if ($Score >= 27) $BandScore = 5.5;
else if ($Score >= 23) $BandScore = 5;
else if ($Score >= 19) $BandScore = 4.5;
else if ($Score >= 15) $BandScore = 4;
else if ($Score >= 12) $BandScore = 3.5;
else if ($Score >= 9) $BandScore = 3;
else if ($Score >= 6) $BandScore = 2.5;
else if ($Score >= 4) $BandScore = 2;
else if ($Score == 3) $BandScore = 1.5;
else if ($Score >= 1) $BandScore = 1;
else $BandScore = 0;


// STEP 5: Insert into testresults
$stmt = $conn->prepare("INSERT INTO testresults (TestID, UserID, TestType, Score, DateTaken, TestDate)
                        VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisiss", $TestID, $UserID, $TestType, $BandScore, $DateTaken, $DateTaken);
$stmt->execute();

// STEP 6: Add feedback and update tests table
if ($BandScore >= 8.5) {
    $feedback = "Excellent! You're close to perfect. Keep it up!";
} elseif ($BandScore >= 7.5) {
    $feedback = "Great job! Just a little more effort to reach Band 9.";
} elseif ($BandScore >= 6.5) {
    $feedback = "Good work! Aim higher with consistent practice.";
} elseif ($BandScore >= 5.5) {
    $feedback = "You're on the right track. Focus on time and accuracy.";
} elseif ($BandScore >= 4.5) {
    $feedback = "Keep practicing. Work on understanding main ideas and keywords.";
} else {
    $feedback = "Don't worry. Start with easier passages and build vocabulary.";
}

$stmt = $conn->prepare("UPDATE tests SET BandScore = ?, Feedback = ? WHERE TestID = ?");
$stmt->bind_param("dsi", $BandScore, $feedback, $TestID);
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reading Test Result</title>
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

    </div>
</body>
</html>
