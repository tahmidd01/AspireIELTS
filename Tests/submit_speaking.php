<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$fluency = $_POST['fluency'];
$pronunciation = $_POST['pronunciation'];
$grammar = $_POST['grammar'];
$lexical_resource = $_POST['lexical_resource'];

// Calculate average band score
$band_score = ($fluency + $pronunciation + $grammar + $lexical_resource) / 4;

$stmt = $conn->prepare("INSERT INTO testresults (UserID, TestType, Section, BandScore) VALUES (?, 'Speaking', 'Academic', ?)");
$stmt->bind_param("id", $user_id, $band_score);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: ../result_speaking.php");
exit;
?>
