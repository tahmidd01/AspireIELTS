<?php
// process_reading.php

// Define the correct answers
$correct_answers = [
    "question_1" => "A",
    "question_2" => "B"
];

// Initialize score
$score = 0;

// Check submitted answers
foreach ($correct_answers as $question => $correct) {
    if (isset($_POST[$question]) && $_POST[$question] === $correct) {
        $score++;
    }
}

// Calculate total questions and percentage
$total_questions = count($correct_answers);
$percentage = ($score / $total_questions) * 100;

// Display results
echo "<h2>Reading Test Results</h2>";
echo "<p>Your Score: $score / $total_questions</p>";
echo "<p>Percentage: " . number_format($percentage, 2) . "%</p>";
?>
