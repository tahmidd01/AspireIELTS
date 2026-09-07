
<?php 
session_start();
require_once "../db_connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? '';
$model = $_ENV['DEEPSEEK_MODEL'] ?? 'deepseek/deepseek-chat:free';

if (!$apiKey) {
    die("DeepSeek API key not configured.");
}

$UserID = $_SESSION['UserID'] ?? null;
if (!$UserID) {
    die("User not logged in.");
}

$TestType = "Writing";
$DateTaken = date('Y-m-d H:i:s');

// Step 1: Check taken sets
$takenSets = [];
$stmt = $conn->prepare("
    SELECT DISTINCT wt.SetNumber 
    FROM writingresponses wr 
    JOIN writingtasks wt ON wr.TaskID = wt.TaskID 
    WHERE wr.UserID = ?
");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $takenSets[] = (int)$row['SetNumber'];
}
$stmt->close();

// Step 2: Determine next set
$SetNumber = null;
for ($i = 1; $i <= 3; $i++) {
    if (!in_array($i, $takenSets)) {
        $SetNumber = $i;
        break;
    }
}
if ($SetNumber === null) {
    echo "<h2 style='text-align:center;'>You have already completed all Writing test sets.</h2>";
    echo "<div style='text-align:center;'><a href='../dashboard.php'>Go to Dashboard</a></div>";
    exit;
}

// Step 3: Fetch writing tasks
$stmt = $conn->prepare("SELECT * FROM writingtasks WHERE SetNumber = ? AND TaskType IN ('Letter Writing', 'Essay Writing')");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $SetNumber);
$stmt->execute();
$tasksResult = $stmt->get_result();
$tasks = [];
while ($row = $tasksResult->fetch_assoc()) {
    $tasks[] = $row;
}
$stmt->close();

if (empty($tasks)) die("No writing tasks found.");

// Step 4: Insert into tests table
$stmt = $conn->prepare("INSERT INTO tests (UserID, TestType, TestDate) VALUES (?, ?, ?)");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("iss", $UserID, $TestType, $DateTaken);
$stmt->execute();
$TestID = $conn->insert_id;
$stmt->close();

// Step 5: Evaluate using OpenRouter DeepSeek API
$TotalScore = 0;
$feedbackSummary = "";

if (!isset($_POST['answers']) || !is_array($_POST['answers'])) {
    die("No answers submitted.");
}

foreach ($tasks as $task) {
    $TaskID = $task['TaskID'];
    $Prompt = $task['Prompt'] ?? '';
    $UserResponse = trim($_POST['answers'][$TaskID] ?? '');
    if (empty($UserResponse)) $UserResponse = "No answer provided.";

    $payload = json_encode([
        "model" => $model,
        "messages" => [
            [
                "role" => "system",
                "content" => 'You are an IELTS examiner. Evaluate the writing based on Task Response, Coherence and Cohesion, Lexical Resource, and Grammatical Range and Accuracy. Return ONLY this exact JSON format WITHOUT any additional text or explanation: {"score": number, "feedback": string}'
            ],
            [
                "role" => "user",
                "content" => "Prompt:\n$Prompt\n\nUser Response:\n$UserResponse"
            ]
        ]
    ]);

    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
    ]);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        die("cURL Error: " . htmlspecialchars($error));
    }

    $decoded = json_decode($result, true);
    if (!$decoded || !isset($decoded['choices'][0]['message']['content'])) {
        die("Invalid response from DeepSeek API.");
    }

    $rawContent = trim($decoded['choices'][0]['message']['content']);

    // Decode JSON string inside the content
    $json = json_decode($rawContent, true);

    if (json_last_error() !== JSON_ERROR_NONE || !$json || !isset($json['score'])) {
        // Sometimes the API may return JSON inside a string wrapped in quotes - try to clean it
        $cleaned = trim($rawContent, "\"'");
        $json = json_decode($cleaned, true);
    }

    if (json_last_error() !== JSON_ERROR_NONE || !$json || !isset($json['score'])) {
        echo "<h3 style='color:red;'>DeepSeek returned invalid response:</h3>";
        echo "<pre>" . htmlspecialchars($rawContent) . "</pre>";
        exit;
    }

    $score = floatval($json['score']);
    $feedback = $json['feedback'] ?? 'No feedback available.';

    // Save response
    $stmt = $conn->prepare("INSERT INTO writingresponses (TestID, TaskID, UserID, ResponseText, Score, Feedback, SubmittedAt) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) die("Prepare failed for writingresponses: " . $conn->error);
    $stmt->bind_param("iiisdss", $TestID, $TaskID, $UserID, $UserResponse, $score, $feedback, $DateTaken);
    $stmt->execute();
    $stmt->close();

    $TotalScore += $score;
    $feedbackSummary .= "<div class='task-feedback'><strong>Task {$TaskID} Feedback:</strong><br>" . 
                        htmlspecialchars($feedback) . "</div><hr>";
}

// Step 6: Store final score and feedback in testresults and update tests
$BandScore = count($tasks) > 0 ? round($TotalScore / count($tasks), 1) : 0;

$stmt = $conn->prepare("INSERT INTO testresults (TestID, UserID, TestType, Score, DateTaken, TestDate) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) die("Prepare failed for testresults: " . $conn->error);
$stmt->bind_param("iisdss", $TestID, $UserID, $TestType, $BandScore, $DateTaken, $DateTaken);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("UPDATE tests SET BandScore = ?, Feedback = ? WHERE TestID = ?");
if (!$stmt) die("Prepare failed for tests update: " . $conn->error);
$stmt->bind_param("dsi", $BandScore, $feedbackSummary, $TestID);
$stmt->execute();
$stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Writing Test Results</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f0f4f8;
            color: #333;
            padding: 40px;
        }
        .result-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 800px;
            margin: auto;
        }
        .score {
            font-size: 28px;
            color: #0056b3;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .task-feedback {
            margin-top: 20px;
            background: #eef3f9;
            padding: 15px;
            border-left: 4px solid #007acc;
            border-radius: 6px;
        }
        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <h2>Your Writing Test Score</h2>
        <div class="score">Band Score: <?= htmlspecialchars($BandScore) ?></div>
        <div class="feedback"><?= $feedbackSummary ?></div>
    </div>
    <button id="quitBtn" style="
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 24px;
    background: linear-gradient(145deg, #ff4b4b, #c33232);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: bold;
    cursor: pointer;
    z-index: 1000;
    box-shadow: 0 5px 0 #991f1f;
    transition: all 0.2s ease-in-out;
"
onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 7px 0 #991f1f';"
onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 0 #991f1f';"
onmousedown="this.style.transform='translateY(2px)'; this.style.boxShadow='0 3px 0 #991f1f';"
onmouseup="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 7px 0 #991f1f';"
>Quit</button>


<!-- Quit Confirmation Modal -->
<div id="quitModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
    z-index: 1001;
    font-family: Arial, sans-serif;
    color: #000;
    flex-direction: column;
">
    <div style="
        background-color: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    ">
        <h2 style="margin-bottom: 15px;">Quit Test?</h2>
        <p style="margin-bottom: 25px;">Are you sure you want to quit the writing test?</p>
<button id="confirmQuitBtn" style="
    background: linear-gradient(145deg, #f44336, #d32f2f);
    color: white;
    margin-right: 10px;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 5px 0 #992222;
    transition: all 0.2s ease-in-out;
"
onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 7px 0 #992222';"
onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 0 #992222';"
onmousedown="this.style.transform='translateY(2px)'; this.style.boxShadow='0 3px 0 #992222';"
onmouseup="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 7px 0 #992222';"
>Yes</button>

<button id="cancelQuitBtn" style="
    background: linear-gradient(145deg, #e0e0e0, #bdbdbd);
    color: #333;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 5px 0 #888;
    transition: all 0.2s ease-in-out;
"
onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 7px 0 #888';"
onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 0 #888';"
onmousedown="this.style.transform='translateY(2px)'; this.style.boxShadow='0 3px 0 #888';"
onmouseup="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 7px 0 #888';"
>No</button>

    </div>
</div>

<script>
    function showQuitModal() {
        const modal = document.getElementById('quitModal');
        modal.style.display = 'flex';
    }

    function hideQuitModal() {
        document.getElementById('quitModal').style.display = 'none';
    }

    function confirmQuit() {
        window.location.href = "../dashboard.php"; // Adjust path if needed
    }

    window.onload = () => {
        document.getElementById('quitBtn').addEventListener('click', showQuitModal);
        document.getElementById('cancelQuitBtn').addEventListener('click', hideQuitModal);
        document.getElementById('confirmQuitBtn').addEventListener('click', confirmQuit);
    };
</script>

</body>
</html>
