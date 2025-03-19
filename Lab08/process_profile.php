<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Ensure user is logged in
if (!isset($_SESSION["email"])) {
    die("Access denied. Please <a href='login.php'>log in</a> first.");
}

$email = $_SESSION["email"];

// Validate form input
if (empty($_POST["fname"]) || empty($_POST["lname"])) {
    $errorMsg .= "First name and last name are required.<br>";
    $success = false;
} else {
    $fname = sanitize_input($_POST["fname"]);
    $lname = sanitize_input($_POST["lname"]);
}

// Check if user wants to update password
if (!empty($_POST["pwd"])) {
    if (strlen($_POST["pwd"]) < 8) {
        $errorMsg .= "Password must be at least 8 characters long.<br>";
        $success = false;
    } else {
        $new_password = password_hash($_POST["pwd"], PASSWORD_DEFAULT);
    }
}

if ($success) {
    updateProfile();
}

if ($success) {
    echo "<title>Profile Updated</title>";
    echo "<main class='container'>";
    echo "<h3>Profile updated successfully!</h3>";
    echo "<p><a class='btn btn-success' href='profile.php'>Return to Profile</a></p></main>";
} else {
    echo "<title>Profile Update Failed</title>";
    echo "<main class='container'>";
    echo "<h3>Profile Update Failed</h3>";
    echo "<p>" . $errorMsg . "</p>";
    echo "<p><a class='btn btn-warning' href='profile.php'>Try Again</a></p></main>";
}

/**
 * Sanitize user input.
 */
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Update user profile in the database.
 */
function updateProfile() {
    global $fname, $lname, $email, $new_password, $errorMsg, $success;

    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        $errorMsg = "Database connection failed: " . $conn->connect_error;
        error_log("Debug: Connection failed - " . $conn->connect_error);
        $success = false;
        return;
    }

    // If the password is being updated
    if (!empty($new_password)) {
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, password=? WHERE email=?");
        $stmt->bind_param("ssss", $fname, $lname, $new_password, $email);
    } else {
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=? WHERE email=?");
        $stmt->bind_param("sss", $fname, $lname, $email);
    }

    if (!$stmt->execute()) {
        $errorMsg = "Error updating profile: " . $stmt->error;
        error_log("Debug: Profile update failed - " . $stmt->error);
        $success = false;
    }

    $stmt->close();
    $conn->close();
}

include "inc/footer.inc.php";
?>
