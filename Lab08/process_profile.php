<?php
session_start();
include "inc/head.inc.php";
include "inc/nav.inc.php";

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMsg = "";
$success = true;
$email = $_SESSION["email"];

// Sanitize input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$fname = isset($_POST["fname"]) ? sanitize_input($_POST["fname"]) : "";
$lname = isset($_POST["lname"]) ? sanitize_input($_POST["lname"]) : "";
$pwd = isset($_POST["pwd"]) && !empty($_POST["pwd"]) ? password_hash($_POST["pwd"], PASSWORD_DEFAULT) : null;

// Validate name fields
if (empty($fname) || empty($lname)) {
    $errorMsg = "First and last name are required.";
    $success = false;
}

// Update database
if ($success) {
    if ($pwd) {
        $stmt = $conn->prepare("UPDATE world_of_pets_members SET fname=?, lname=?, password=? WHERE email=?");
        $stmt->bind_param("ssss", $fname, $lname, $pwd, $email);
    } else {
        $stmt = $conn->prepare("UPDATE world_of_pets_members SET fname=?, lname=? WHERE email=?");
        $stmt->bind_param("sss", $fname, $lname, $email);
    }

    if (!$stmt->execute()) {
        $errorMsg = "Database update failed: " . $stmt->error;
        $success = false;
    }

    $stmt->close();
}
$conn->close();

// Redirect or display message
if ($success) {
    echo "<main class='container'><h3>Profile updated successfully!</h3><a href='profile.php'>Return to Profile</a></main>";
} else {
    echo "<main class='container'><h3>Error updating profile:</h3><p>$errorMsg</p><a href='profile.php'>Try Again</a></main>";
}

include "inc/footer.inc.php";
?>
