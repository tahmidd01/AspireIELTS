<?php
// Assuming a database connection is already established
session_start();

// Get user ID (assuming you store it in the session after login)
$user_id = $_SESSION['user_id']; 

// Retrieve answers from the form
$question_1 = isset($_POST['question_1']) ? $_POST['question_1'] : '';
$question_2 = isset($_POST['question_2']) ? $_POST['question_2'] : '';
$question_3 = isset($_POST['question_3']) ? $_POST['question_3'] : '';

// Determine if the answers are correct (this will depend on your actual answers for each question)
$correct_answers = [
    'question_1' => 'A',  // Example correct answer for question 1
    'question_2' => 'B',  // Example correct answer for question 2
    'question_3' => 'A',  // Example correct answer for question 3
];

// Check correctness of answers
$score = 0;
if ($question_1 === $correct_answers['question_1']) $score++;
if ($question_2 === $correct_answers['question_2']) $score++;
if ($question_3 === $correct_answers['question_3']) $score++;

// Insert answers into the listening_test_responses table
$sql = "INSERT INTO listening_test_responses (TestID, UserID, question_1, question_2, question_3) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $test_id, $user_id, $question_1, $question_2, $question_3);

// Execute the statement
if ($stmt->execute()) {
    // Insert result into the listening_test_results table (optional)
    $sql_result = "INSERT INTO listening_test_results (UserID, TestID, score) VALUES (?, ?, ?)";
    $stmt_result = $conn->prepare($sql_result);
    $stmt_result->bind_param("iii", $user_id, $test_id, $score);
    $stmt_result->execute();

    // Redirect to the results or another page
    header('Location: result_page.php');  // Adjust this to the appropriate results page
    exit();
} else {
    echo "Error submitting test. Please try again.";
}
?>
