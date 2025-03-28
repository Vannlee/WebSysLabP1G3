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

$member_id = $_POST["member_id"] ?? null;
$current_pwd = $_POST["current_pwd"] ?? null;
$new_email = $_POST["email"] ?? null;
$new_password = $_POST["new_pwd"] ?? null;
$fname = sanitize_input($_POST["fname"]);
$lname = sanitize_input($_POST["lname"]);

// Validate email
if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $errorMsg .= "Invalid email format.<br>";
    $success = false;
}

// Validate current password
if (empty($current_pwd)) {
    $errorMsg .= "Current password is required.<br>";
    $success = false;
}

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch user details
$stmt = $conn->prepare("SELECT password FROM gymbros_members WHERE member_id=?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($current_pwd, $user["password"])) {
    $errorMsg .= "Current password is incorrect.<br>";
    $success = false;
}

// Check if new email is already taken
if ($success) {
    $stmt = $conn->prepare("SELECT member_id FROM gymbros_members WHERE email=? AND member_id != ?");
    $stmt->bind_param("si", $new_email, $member_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errorMsg .= "This email is already in use by another user.<br>";
        $success = false;
    }
    $stmt->close();
}

// Update email and password if everything is valid
if ($success) {
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, email=?, password=? WHERE member_id=?");
        $stmt->bind_param("ssssi", $fname, $lname, $new_email, $hashed_password, $member_id);
    } else {
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, email=? WHERE member_id=?");
        $stmt->bind_param("sssi", $fname, $lname, $new_email, $member_id);
    }

    if (!$stmt->execute()) {
        $errorMsg .= "Error updating profile: " . $stmt->error . "<br>";
        $success = false;
    }
    $stmt->close();
    $_SESSION["email"] = $new_email; // Update session email
}

$conn->close();

if ($success) {
    echo "<title>Profile Updated</title>";
    echo "<main class='container'>";
    echo "<h3>Profile updated successfully!</h3>";
    echo "<p><a class='btn btn-success' href='profile.php'>Return to Profile</a></p></main>";
} else {
    echo "<title>Update Failed</title>";
    echo "<main class='container'>";
    echo "<h3>Update Failed</h3>";
    echo "<p>" . $errorMsg . "</p>";
    echo "<p><a class='btn btn-warning' href='profile.php'>Try Again</a></p></main>";
}

include "inc/footer.inc.php";

// Helper function
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
