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

$user_id = $_POST["user_id"] ?? null;

// Validate form input
if (empty($_POST["fname"]) || empty($_POST["lname"]) || empty($_POST["email"])) {
    $errorMsg .= "First name, last name, and email are required.<br>";
    $success = false;
} else {
    $fname = sanitize_input($_POST["fname"]);
    $lname = sanitize_input($_POST["lname"]);
    $new_email = sanitize_input($_POST["email"]);

    // Validate new email format
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
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
    // Update session email if the user changed it
    $_SESSION["email"] = $new_email;
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
    global $fname, $lname, $new_email, $new_password, $user_id, $errorMsg, $success;

    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        $errorMsg = "Database connection failed: " . $conn->connect_error;
        error_log("Debug: Connection failed - " . $conn->connect_error);
        $success = false;
        return;
    }

    // Check if the new email already exists (to prevent duplicate emails)
    $stmt = $conn->prepare("SELECT id FROM gymbros_members WHERE email=? AND id != ?");
    $stmt->bind_param("si", $new_email, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errorMsg = "This email is already in use by another user.";
        $success = false;
        return;
    }
    $stmt->close();

    // ✅ If updating password
    if (!empty($new_password)) {
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $fname, $lname, $new_email, $new_password, $user_id);
    } else {
        // ✅ If NOT updating password
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, email=? WHERE id=?");
        $stmt->bind_param("sssi", $fname, $lname, $new_email, $user_id);
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
