<?php
// Include the database connection file
include('db.php');

// Start the session to access logged-in user details
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];  // Assuming user ID is stored in session

// Fetch test results from the database for the logged-in user
$query = "SELECT * FROM results WHERE user_id = '$userId'";
$result = mysqli_query($conn, $query);

// Check if results exist for the user
if (mysqli_num_rows($result) > 0) {
    echo "<h2>Your Test Results</h2>";
    
    // Loop through the results and display them
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='result'>";
        echo "<p><strong>Test Type:</strong> " . $row['test_type'] . "</p>";
        echo "<p><strong>Score:</strong> " . $row['score'] . "</p>";
        echo "</div>";
    }
} else {
    // Display a message if no results are found
    echo "<p>You haven't completed any tests yet.</p>";
}
?>

<!-- Optional: Add a link to return to the dashboard or logout -->
<a href="dashboard.php">Back to Dashboard</a> | <a href="logout.php">Logout</a>
