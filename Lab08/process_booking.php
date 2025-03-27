<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include common head and nav sections if desired
include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Ensure the request is a POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect or handle the error
    header("Location: booking.php");
    exit();
}

// Validate required fields
if (empty($_POST["bookingDate"]) || empty($_POST["location"])) {
    $errorMsg .= "Booking date and location are required.<br>";
    $success = false;
} else {
    // Sanitize inputs
    $bookingDate = sanitize_input($_POST["bookingDate"]);
    $location = sanitize_input($_POST["location"]);

    // Hidden or default values for class and instructor
    $class = isset($_POST["class"]) ? sanitize_input($_POST["class"]) : "Yoga";
    $instructor = isset($_POST["instructor"]) ? sanitize_input($_POST["instructor"]) : "Jane Doe";
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $errorMsg .= "User not logged in.<br>";
    $success = false;
} else {
    $member_id = $_SESSION['user_id'];
}

// If everything is okay so far, process the booking
if ($success) {
    processBooking($member_id, $location, $bookingDate, $class, $instructor);
}

// Display success or error message
if ($success) {
    echo "<title>Booking Successful</title>";
    echo "<main class='container'>";
    echo "<h3>Booking Successful!</h3>";
    echo "<p><a class='btn btn-success' href='booking.php'>View Bookings</a></p>";
    echo "</main>";
} else {
    echo "<title>Booking Failed</title>";
    echo "<main class='container'>";
    echo "<h3>Booking Failed</h3>";
    echo "<h4>Error:</h4><p>" . $errorMsg . "</p>";
    echo "<p><a class='btn btn-warning' href='booking.php'>Try Again</a></p>";
    echo "</main>";
}

// Include the footer
include "inc/footer.inc.php";

/**
 * Sanitize user input.
 */
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Process the booking by inserting a new record into the database.
 */
function processBooking($member_id, $location, $bookingDate, $class, $instructor) {
    global $errorMsg, $success;

    // Read database credentials from a secure config file
    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        $errorMsg .= "Database connection failed: " . $conn->connect_error;
        $success = false;
        return;
    }

    // Prepare the INSERT statement
    $stmt = $conn->prepare("INSERT INTO booking (member_id, loc_id, date, class, instructor) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        $errorMsg .= "Prepare failed: " . $conn->error;
        $success = false;
        return;
    }

    // Bind parameters and execute
    $stmt->bind_param("iisss", $member_id, $location, $bookingDate, $class, $instructor);
    if (!$stmt->execute()) {
        $errorMsg .= "Error inserting booking: " . $stmt->error;
        $success = false;
    }

    $stmt->close();
    $conn->close();
}
?>
