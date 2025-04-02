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
    header("Location: booking.php");
    exit();
}

// Validate required fields
if (empty($_POST["bookingDate"]) || empty($_POST["slot"]) || empty($_POST["location"])) {
    $errorMsg .= "Date, slot, and location are required.<br>";
    $success = false;
} else {
    // Sanitize inputs
    $bookingDate = sanitize_input($_POST["bookingDate"]);
    $slot = sanitize_input($_POST["slot"]);
    $location = sanitize_input($_POST["location"]);
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
    processBooking($member_id, $location, $bookingDate, $slot);
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
function processBooking($member_id, $location, $bookingDate, $slot) {
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

    // Insert statement (we assume 'created' and 'last_update' are auto-handled in the DB schema)
    $stmt = $conn->prepare(
        "INSERT INTO booking (member_id, loc_id, date, slot) 
         VALUES (?, ?, ?, ?)"
    );
    if (!$stmt) {
        $errorMsg .= "Prepare failed: " . $conn->error;
        $success = false;
        return;
    }

    // Bind parameters and execute
    $stmt->bind_param("iiss", $member_id, $location, $bookingDate, $slot);
    if (!$stmt->execute()) {
        $errorMsg .= "Error inserting booking: " . $stmt->error;
        $success = false;
    }

    $stmt->close();
    $conn->close();
}
?>
