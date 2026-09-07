<?php
session_start();
include 'db_connection.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login_form.html"); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['UserID']; // Get the UserID from session

// Query to fetch user's information from the database
$stmt = $conn->prepare("SELECT FullName, Email FROM Users WHERE UserID = ?");
$stmt->bind_param("i", $userID); // Bind the UserID to the query
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = htmlspecialchars($row['FullName']);
    $email = htmlspecialchars($row['Email']);
} else {
    echo "User not found.";
    exit();
}

// Query to fetch user's test scores
$stmt = $conn->prepare("SELECT TestType, Score, TestDate FROM TestResults WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$testResults = $stmt->get_result();

// Initialize variables for average score and section analysis
$totalScore = 0;
$totalTests = 0;
$sections = [
    'Listening' => [],
    'Reading' => [],
    'Writing' => [],
    'Speaking' => []
];

// Collect scores and prepare for section analysis
$testDates = [];
$testScores = [];
while ($row = $testResults->fetch_assoc()) {
    $score = $row['Score'];
    $testType = $row['TestType'];
    $testDate = $row['TestDate'];

    $totalScore += $score;
    $totalTests++;

    // Store section-wise scores
    if (in_array($testType, ['Listening', 'Reading', 'Writing', 'Speaking'])) {
        $sections[$testType][] = $score;
    }

    // Prepare data for the performance trends graph
    $testDates[] = $testDate;
    $testScores[] = $score;
}

// Calculate average score
$averageScore = $totalTests > 0 ? round($totalScore / $totalTests, 2) : 0;

// Prepare section-wise performance analysis
$sectionPerformance = [];
foreach ($sections as $section => $scores) {
    if (count($scores) > 0) {
        $averageSectionScore = round(array_sum($scores) / count($scores), 2);
        $sectionPerformance[$section] = $averageSectionScore;
    } else {
        $sectionPerformance[$section] = null; // No scores for this section
    }
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AspireIELTS</title>
		<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = trim($_POST['message']);
    $response = getChatbotResponse($userMessage);
    echo json_encode(['response' => $response]);
    exit;
}

function getChatbotResponse($message) {
    $message = strtolower($message);

    // Predefined responses
    $responses = [
    "hello": "Hi there! How can I assist you with your IELTS preparation today?",
    "hi": "Hello! Ready to improve your IELTS score?",
    "hey": "Hey! Need help with IELTS reading, writing, speaking or listening?",

    // Thanks and Farewells
    "thank you": "You're welcome! I'm always here to help you succeed.",
    "thanks": "No problem! Let me know if you have any other IELTS questions.",
    "bye": "Goodbye! Best of luck with your IELTS journey!",
    "see you": "See you! Don’t forget to keep practicing.",

    // Writing
    "writing tips": "Focus on task achievement, coherence, vocabulary, and grammar. Practice both Task 1 and Task 2 regularly.",
    "how to improve writing": "Practice structured essay writing, use linking words, and check grammar. Review band 9 sample answers to learn formatting.",
    "writing task 1": "For Task 1, describe the visual (chart, graph, etc.) clearly. Summarize key trends and avoid personal opinions.",
    "writing task 2": "For Task 2, present your opinion, support it with examples, and structure your essay with clear introduction, body, and conclusion.",
    "common writing mistakes": "Avoid contractions, informal language, and vague arguments. Always revise your writing for grammar and coherence.",

    // Speaking
    "speaking tips": "Practice speaking fluently without too many pauses. Use a range of vocabulary and correct grammar.",
    "how to improve speaking": "Speak English daily, record yourself, and practice with IELTS sample questions. Focus on pronunciation and fluency.",
    "part 1 speaking": "Part 1 is about familiar topics like hobbies or family. Keep answers short but clear.",
    "part 2 speaking": "Part 2 is a long turn. Prepare using cue cards. Speak for 1–2 minutes, and stay on topic.",
    "part 3 speaking": "Part 3 involves deeper discussion. Give detailed answers, reasons, and examples.",

    // Listening
    "listening tips": "Practice with official IELTS recordings. Focus on keywords and note-taking.",
    "how to improve listening": "Listen to English podcasts, news, and IELTS materials. Practice identifying synonyms and paraphrasing.",
    "common listening problems": "Many students miss answers due to distractions or unfamiliar accents. Stay focused and read questions ahead.",

    // Reading
    "reading tips": "Skim for general meaning, scan for specific info, and don’t spend too much time on one question.",
    "how to improve reading": "Read newspapers, articles, and practice IELTS reading tests daily. Learn to identify main ideas quickly.",
    "true false not given tips": "Carefully compare the passage and statement. Focus on what is said, not what you know.",
    "time management in reading": "Spend about 20 minutes per passage. Don’t get stuck. Mark difficult ones and return later if needed.",

    // IELTS General
    "what is ielts": "IELTS stands for International English Language Testing System. It tests your English proficiency in Listening, Reading, Writing, and Speaking.",
    "ielts full form": "IELTS stands for International English Language Testing System.",
    "how to prepare for ielts": "Set a study schedule, take mock tests, and improve vocabulary. Use reliable practice materials and track your progress.",
    "ielts band score": "IELTS is scored on a 0–9 band scale for each skill. An average is taken for your overall band score.",
    "how many sections in ielts": "There are 4 sections: Listening, Reading, Writing, and Speaking.",
    "difference between academic and general ielts": "Academic IELTS is for higher education, while General Training is for work or immigration purposes.",
    "minimum score for uk": "It depends on the institution, but generally 6.5 or above is required for most UK universities.",
    "minimum score for canada": "For Canada, most institutions require at least 6.0 in each band, but requirements vary.",
    "minimum score for australia": "Australian institutions usually ask for 6.5 overall, with no band less than 6.0.",

    // Vocabulary and Grammar
    "how to improve vocabulary": "Read widely, keep a vocabulary journal, and use new words in context. Use apps like Quizlet for revision.",
    "how to improve grammar": "Practice grammar exercises daily. Focus on common topics like tenses, articles, and sentence structure.",
    "band 9 vocabulary": "Band 9 vocabulary includes advanced and topic-specific terms. Read sample band 9 essays to learn them.",

    // Mock Tests and Practice
    "where to take mock tests": "You can take free mock tests online on IELTS.org, British Council, and EdAcademix (if available).",
    "best books for ielts": "Try Cambridge IELTS books, The Official Cambridge Guide to IELTS, and Barron’s IELTS Superpack.",
    "how many practice tests to take": "Aim to complete 10–15 full-length mock tests before your exam date.",

    // Motivation
    "i am nervous": "It’s normal to feel nervous. Stick to your study plan, practice daily, and you’ll gain confidence.",
    "how long to prepare for ielts": "Most students take 1–3 months to prepare effectively, depending on their current level.",
    "can i get band 8": "Yes! With daily focused practice and the right strategy, you can achieve band 8 or higher.",
    "i failed ielts": "Don’t give up. Review your weaknesses, take feedback seriously, and practice smarter this time.",

    "listening": "IELTS Listening has 4 sections. Make sure to practice with headphones in a quiet space.",
"reading": "IELTS Reading tests your ability to locate, understand, and analyze information in a text. Focus on time management!",
"writing": "Writing is divided into Task 1 and Task 2. Practice regularly and analyze high-band samples.",
"speaking": "Speaking has 3 parts. Practice fluency, pronunciation, and structured responses. You can book a mock speaking test with us!",

"mock test": "You can attempt Listening, Reading, and Writing mock tests on our platform. Speaking tests can be scheduled live.",
"mock tests": "Mock tests simulate the real exam. Take one regularly to track your progress.",
"test result": "Your latest results and performance graph can be viewed on your dashboard.",
"dashboard": "Visit your dashboard to track scores, review feedback, and continue your preparation.",

"speaking questions": "You can practice with common IELTS Part 1, 2, and 3 questions. Try answering with a timer!",
"reading passage tips": "Skim the passage first, then scan for answers. Time is your biggest challenge here.",
"listening audio source": "We use authentic-style recordings. Make sure your environment is quiet when practicing.",
"writing feedback": "After you submit a writing task, you'll receive automated feedback and a band estimate.",
"score analysis": "Your test score analysis includes band scores, feedback, and suggestions for improvement.",

"tips": "Sure! Just tell me which section you're interested in: Listening, Reading, Writing, or Speaking.",
"start test": "Go to your dashboard and select the section you want to begin with.",
"start speaking test": "Please schedule your speaking test through the dashboard. An examiner will call you via Jitsi.",
"video lectures": "You can access video lectures for each test section from the preparation menu on your dashboard.",
"preparation tips": "Consistency is key. Use mock tests, video lectures, and daily practice to improve steadily.",
"reset progress": "You can reset your test attempts from your profile settings. Be sure before you do this.",

"motivate me": "You're doing great! Every bit of practice brings you closer to your goal. Band 8 is within reach!",
"band 9 goal": "With focused practice, top resources, and guidance — yes, band 9 is possible!",
"feeling stuck": "Hit a wall? Take a short break, revisit your weak areas, and come back stronger.",
"how are you": "I’m great and ready to help you ace IELTS. How can I assist today?"
    ];

    foreach ($responses as $key => $response) {
        if (strpos($message, $key) !== false) {
            return $response;
        }
    }

    return 'I'm sorry, I didn't understand that. Can you ask something else about IELTS?';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Chatbot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .chat-container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .messages {
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        .messages .message {
            margin-bottom: 10px;
        }
        .messages .user {
            text-align: right;
            color: blue;
        }
        .messages .bot {
            text-align: left;
            color: green;
        }
        .input-container {
            display: flex;
        }
        .input-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        .input-container button {
            padding: 10px 20px;
            border: none;
            background-color:rgb(255, 0, 0);
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="messages" id="messages"></div>
        <div class="input-container">
            <input type="text" id="userMessage" placeholder="Type your message...">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        function sendMessage() {
            const userMessage = document.getElementById('userMessage').value;
            if (!userMessage.trim()) return;

            const messagesDiv = document.getElementById('messages');
            const userDiv = document.createElement('div');
            userDiv.className = 'message user';
            userDiv.textContent = userMessage;
            messagesDiv.appendChild(userDiv);

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `message=${encodeURIComponent(userMessage)}`,
            })
                .then(response => response.json())
                .then(data => {
                    const botDiv = document.createElement('div');
                    botDiv.className = 'message bot';
                    botDiv.textContent = data.response;
                    messagesDiv.appendChild(botDiv);
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                });

            document.getElementById('userMessage').value = '';
        }
    </script>
</body>
</html>
    <style>


        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color:rgb(218, 222, 240);
            color: #333;
        }

        h2, h3 {
            font-weight: 600;
            color:rgb(241, 5, 5);
        }

        h2 {
            font-size: 2.2em;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header .logo {
            font-size: 3em;
            font-weight: 700;
            color:rgb(71, 6, 248);
            letter-spacing: 5px;
            text-transform: uppercase;
            background: linear-gradient(to right,rgb(68, 0, 255),rgb(4, 88, 245));
            -webkit-background-clip: text;
            color: transparent;
            animation: text-fade 3s ease-in-out infinite;
        }

        @keyframes text-fade {
            0% { opacity: 0.8; }
            50% { opacity: 1; }
            100% { opacity: 0.8; }
        }

        .header .sub-title {
            font-size: 1.2em;
            margin-top: 10px;
            color: #555;
        }

        /* Dashboard Container */
        .container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        /* User Info Section */
        .user-info {
            margin-bottom: 30px;
            text-align: center;
        }

        .user-info p {
            font-size: 1.1em;
            margin-top: 10px;
        }

        /* Test Actions */
        .test-actions {
            margin-bottom: 40px;
            text-align: center;
        }

        .test-actions h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
        }

        .test-actions a {
            display: inline-block;
            padding: 12px 25px;
            background-color:rgb(89, 0, 255);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
            font-size: 1.1em;
            transition: all 0.3s ease;
        }

        .test-actions a:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        /* Test Results */
        .test-results {
            margin-bottom: 40px;
        }

        .test-results table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .test-results th, .test-results td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .test-results th {
            background-color:rgb(55, 0, 255);
            color: white;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9em;
            color: #777;
        }

        /* Button styling for Logout */
        .logout-btn {
            display: inline-block;
            background-color:rgb(228, 15, 15);
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 1.1em;
            margin-top: 20px;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color:rgb(221, 36, 15);
            transform: scale(1.05);
        }

        /* Performance Section */
        .overall-score, .performance-trends, .section-performance {
            margin-bottom: 40px;
        }

        /* Section-wise Performance Styling */
        .section-performance {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(95, 19, 19, 0);
            margin-bottom: 30px;
        }

        .section-performance h3 {
            font-size: 1.8em;
            color:rgb(76, 0, 255);
            margin-bottom: 15px;
        }

        .performance-item {
            font-size: 1.1em;
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
        }

        .performance-item:last-child {
            border-bottom: none;
        }

        .performance-item strong {
            font-weight: 600;
        }

        .badge {
            font-size: 0.9em;
            padding: 4px 8px;
            border-radius: 5px;
            display: inline-block;
            margin-left: 10px;
            font-weight: bold;
        }

        .badge.strong {
            background-color: #28a745;
            color: white;
        }

        .badge.improvement {
            background-color: #f39c12;
            color: white;
        }

        .no-data {
            color: #e74c3c;
            font-style: italic;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .test-actions a {
                font-size: 1em;
                padding: 10px 20px;
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header with AspireIELTS Name and Animation -->
        <div class="header">
            <div class="logo">AspireIELTS</div>
            <div class="sub-title">Your Pathway to IELTS Success</div>
        </div>

        <!-- User Info Section -->
        <div class="user-info">
            <h2>Welcome, <?php echo $fullName; ?>!</h2>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
        </div>

        <!-- Test Actions Section -->
        <div class="test-actions">
            <h3>Choose a Test Section</h3>
            <p>Select a test to begin your practice:</p>
            <a href="tests/listening_test.php">Listening Test</a>
            <a href="tests/reading_test.php">Reading Test</a>
            <a href="tests/writing_test.php">Writing Test</a>
            <a href="tests/speaking_test.php">Speaking Test</a>
        </div>

        <!-- Overall Score Section -->
        <div class="overall-score">
            <h3>Overall Average Band Score</h3>
            <p>Your average score based on all mock tests is: <strong><?php echo $averageScore; ?></strong></p>
        </div>

        <!-- Performance Trends (Graph) -->
        <div class="performance-trends">
            <h3>Your Performance Over Time</h3>
            <canvas id="performanceChart"></canvas>
        </div>

        <!-- Section-wise Strengths & Weaknesses -->
        <div class="section-performance">
            <h3>Section-wise Performance</h3>
            <ul>
                <?php foreach ($sectionPerformance as $section => $score): ?>
                    <li class="performance-item">
                        <strong><?php echo $section; ?>:</strong> 
                        <?php 
                            if ($score === null) {
                                echo '<span class="no-data">No Data</span>';
                            } else {
                                echo $score;
                                if ($score >= 7) { 
                                    echo ' <span class="badge strong">(Strong)</span>';
                                } else {
                                    echo ' <span class="badge improvement">(Needs Improvement)</span>';
                                }
                            }
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Test Results Section -->
        <div class="test-results">
            <h3>Your Test Results</h3>
            <table>
                <thead>
                    <tr>
                        <th>Test Type</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display the user's test results
                    while ($row = $testResults->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['TestType']) . "</td>
                                <td>" . htmlspecialchars($row['Score']) . "</td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Logout Button -->
        <div class="actions">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2024 AspireIELTS. All Rights Reserved. | <a href="#">Privacy Policy</a> | <a href="#">Contact</a></p>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($testDates); ?>,  // Test dates
                datasets: [{
                    label: 'Scores',
                    data: <?php echo json_encode($testScores); ?>,  // Test scores
                    borderColor: 'rgb(42, 20, 238)',
                    backgroundColor: 'rgb(112, 124, 146)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Test Dates'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Scores'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AspireIELTS</title>
    <style>
        /* Styling for the Overall Score Section */
        .overall-score {
            background: linear-gradient(135deg, #007bff,rgb(0, 39, 212));
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            font-size: 1.5em;
        }
        .overall-score strong {
            font-size: 2em;
            display: block;
            margin-top: 10px;
        }
    </style>
</head>

