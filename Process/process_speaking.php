<?php
// process_speaking.php

// This section usually involves manual evaluation by an examiner
// Display the submitted responses for evaluation

echo "<h2>Speaking Test Submission</h2>";

if (!empty($_POST["part_1"])) {
    echo "<h3>Part 1: Introduction and familiar questions</h3>";
    echo "<p>" . nl2br(htmlspecialchars($_POST["part_1"])) . "</p>";
}

if (!empty($_POST["part_2"])) {
    echo "<h3>Part 2: Talk on a given topic</h3>";
    echo "<p>" . nl2br(htmlspecialchars($_POST["part_2"])) . "</p>";
}

if (!empty($_POST["part_3"])) {
    echo "<h3>Part 3: Discussion on abstract topics</h3>";
    echo "<p>" . nl2br(htmlspecialchars($_POST["part_3"])) . "</p>";
}

echo "<p>Scores for speaking tests will be provided after manual evaluation.</p>";
?>
