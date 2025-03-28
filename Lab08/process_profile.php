<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

if (!isset($_SESSION["email"])) {
    die("Access denied. Please <a href='login.php'>log in</a>.");
}

$member_id     = $_POST["member_id"] ?? null;
$action_type   = $_POST["action_type"] ?? "update";
$current_pwd   = $_POST["current_pwd"] ?? null;
$new_email     = $_POST["email"] ?? null;
$new_password  = $_POST["new_pwd"] ?? null;
$contact       = sanitize_input($_POST["contact"] ?? '');
$fname         = sanitize_input($_POST["fname"] ?? '');
$lname         = sanitize_input($_POST["lname"] ?? '');

if (empty($current_pwd)) {
    $errorMsg .= "Current password is required.<br>";
    $success = false;
}

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT password FROM gymbros_members WHERE member_id=?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($current_pwd, $user["password"])) {
    $errorMsg .= "Incorrect password.<br>";
    $success = false;
}

if ($success && $action_type === "delete") {
    $stmt = $conn->prepare("DELETE FROM gymbros_members WHERE member_id=?");
    $stmt->bind_param("i", $member_id);
    if ($stmt->execute()) {
        session_destroy();
        echo "<main class='container'><h3>Profile deleted successfully.</h3>
              <p><a href='register.php' class='btn btn-primary'>Register New Account</a></p></main>";
    } else {
        echo "<main class='container'><h3>Failed to delete account: " . $stmt->error . "</h3>
              <p><a class='btn btn-warning' href='profile.php'>Back to Profile</a></p></main>";
    }
    $stmt->close();
    $conn->close();
    include "inc/footer.inc.php";
    exit();
}

if ($success && $action_type === "update") {
    $stmt = $conn->prepare("SELECT member_id FROM gymbros_members WHERE email=? AND member_id != ?");
    $stmt->bind_param("si", $new_email, $member_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errorMsg .= "Email already in use.<br>";
        $success = false;
    }
    $stmt->close();
}

if ($success) {
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, email=?, contact=?, password=? WHERE member_id=?");
        $stmt->bind_param("sssssi", $fname, $lname, $new_email, $contact, $hashed_password, $member_id);
    } else {
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, email=?, contact=? WHERE member_id=?");
        $stmt->bind_param("ssssi", $fname, $lname, $new_email, $contact, $member_id);
    }

    if ($stmt->execute()) {
        $_SESSION["email"] = $new_email;
        echo "<main class='container'><h3>Profile updated successfully.</h3>
              <p><a class='btn btn-success' href='profile.php'>Return to Profile</a></p></main>";
    } else {
        $errorMsg .= "Update failed: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();

if (!$success) {
    echo "<main class='container'><h3>Action Failed</h3>
          <p>$errorMsg</p>
          <p><a class='btn btn-warning' href='profile.php'>Try Again</a></p></main>";
}

include "inc/footer.inc.php";

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
