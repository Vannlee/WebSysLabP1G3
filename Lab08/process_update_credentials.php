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
$current_pwd = $_POST["current_pwd"] ?? null;
$new_email = $_POST["email"] ?? null;
$new_password = $_POST["new_pwd"] ?? null;

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
$stmt = $conn->prepare("SELECT password FROM gymbros_members WHERE id=?");
$stmt->bind_param("i", $user_id);
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
    $stmt = $conn->prepare("SELECT id FROM gymbros_members WHERE email=? AND id != ?");
    $stmt->bind_param("si", $new_email, $user_id);
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
        $stmt = $conn->prepare("UPDATE gymbros_members SET email=?, password=? WHERE id=?");
        $stmt->bind_param("ssi", $new_email, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE gymbros_members SET email=? WHERE id=?");
        $stmt->bind_param("si", $new_email, $user_id);
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
    echo "<title>Credentials Updated</title>";
    echo "<main class='container'>";
    echo "<h3>Email and/or password updated successfully!</h3>";
    echo "<p><a class='btn btn-success' href='update_credentials.php'>Back to Update Page</a></p></main>";
} else {
    echo "<title>Update Failed</title>";
    echo "<main class='container'>";
    echo "<h3>Update Failed</h3>";
    echo "<p>" . $errorMsg . "</p>";
    echo "<p><a class='btn btn-warning' href='update_credentials.php'>Try Again</a></p></main>";
}

include "inc/footer.inc.php";
?>
