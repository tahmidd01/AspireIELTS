<?php
// process_writing.php

// Writing test is usually evaluated manually.
// Display the submitted responses for evaluation.

echo "<h2>Writing Test Submission</h2>";

if (!empty($_POST["task_1"])) {
    echo "<h3>Task 1: Describe visual data</h3>";
    echo "<p>" . nl2br(htmlspecialchars($_POST["task_1"])) . "</p>";
}

if (!empty($_POST["task_2"])) {
    echo "<h3>Task 2: Write an essay</h3>";
    echo "<p>" . nl2br(htmlspecialchars($_POST["task_2"])) . "</p>";
}

echo "<p>Scores for writing tests will be provided after manual evaluation.</p>";
?>
