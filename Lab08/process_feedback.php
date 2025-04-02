<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Define functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
} else {
    $id = $_SESSION['user_id'];
}

// Validate inputs
if (empty($_POST["feedback_content"])) {
    $errorMsg = "All fields are required.<br>";
    $success = false;
} else {
    $content = sanitize_input($_POST["feedback_content"]);
}

// If validation is successful, register the user
if ($success) {
    submitFeedback();
}

// Display success or error message
if ($success) {
    echo "<title>Feedback Success</title>";
    echo "<main class='container'>";
    echo "<h3>Thank You for Your Feedback</h3>";
    echo "<p><a class='btn btn-success' href='index.php'>Back to Homepage</a></p></main>";
} else {
    echo "<title>Feedback Error</title>";
    echo "<main class='container'>";
    echo "<h3>Oops! Kindly resolve the following:</h3>";
    echo "<h4>Error: </h4><p>" . $errorMsg . "</p>";
    echo "<p><a class='btn btn-warning' href='leave_feedback.php'>Try Again</a></p></main>";
}

/**
 * Function to sanitize user input.
 */
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Function to register user in the database.
 */
function submitFeedback() {
    global $id, $content, $errorMsg, $success;

    // Database connection
    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        $errorMsg = "Database connection failed: " . $conn->connect_error;
        error_log("Debug: Connection failed - " . $conn->connect_error);
        $success = false;
        return;
    }

    // Insert new user into the database
    $stmt = $conn->prepare("INSERT INTO membership_feedback (member_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $content);

    if (!$stmt->execute()) {
        $errorMsg = "Error adding feedback: " . $stmt->error;
        error_log("Debug: Insert failed - " . $stmt->error);
        $success = false;
    }

    $stmt->close();
    $conn->close();
}

include "inc/footer.inc.php";
?>
