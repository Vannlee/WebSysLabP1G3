<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Validate inputs
if (empty($_POST["fname"]) || empty($_POST["lname"]) || empty($_POST["email"]) || empty($_POST["pwd"])) {
    $errorMsg = "All fields are required.<br>";
    $success = false;
} else {
    $fname = sanitize_input($_POST["fname"]);
    $lname = sanitize_input($_POST["lname"]);
    $email = sanitize_input($_POST["email"]);
    $pwd = $_POST["pwd"];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
}

// If validation is successful, register the user
if ($success) {
    registerUser();
}

// Display success or error message
if ($success) {
    echo "<title>Registration Successful</title>";
    echo "<main class='container'>";
    echo "<h3>Registration successful!</h3>";
    echo "<p><a class='btn btn-success' href='login.php'>Proceed to Login</a></p></main>";
} else {
    echo "<title>Registration Failed</title>";
    echo "<main class='container'>";
    echo "<h3>Registration Failed</h3>";
    echo "<h4>Error: </h4><p>" . $errorMsg . "</p>";
    echo "<p><a class='btn btn-warning' href='register.php'>Try Again</a></p></main>";
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
function registerUser() {
    global $fname, $lname, $email, $pwd, $errorMsg, $success;

    // Database connection
    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        $errorMsg = "Database connection failed: " . $conn->connect_error;
        error_log("Debug: Connection failed - " . $conn->connect_error);
        $success = false;
        return;
    }

    // Check if the email is already registered
    $stmt = $conn->prepare("SELECT email FROM gymbros_members WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errorMsg = "This email is already registered.";
        $success = false;
        return;
    }
    $stmt->close();

    // Hash password before storing
    $hashed_password = password_hash($pwd, PASSWORD_DEFAULT);

    // Insert new user into the database
    $stmt = $conn->prepare("INSERT INTO gymbros_members (fname, lname, email, password, datejoin) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $fname, $lname, $email, $hashed_password);

    if (!$stmt->execute()) {
        $errorMsg = "Error inserting user: " . $stmt->error;
        error_log("Debug: Insert failed - " . $stmt->error);
        $success = false;
    }

    $stmt->close();
    $conn->close();
}

include "inc/footer.inc.php";
?>
