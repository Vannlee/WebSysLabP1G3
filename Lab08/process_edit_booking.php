<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: booking.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $errorMsg .= "User not logged in.<br>";
    $success = false;
} else {
    $member_id = $_SESSION['user_id'];
}

// Validate required fields
if (empty($_POST['booking_id']) || empty($_POST['bookingDate']) || empty($_POST['slot']) || empty($_POST['loc_id'])) {
    $errorMsg .= "Booking ID, date, slot, and location are required.<br>";
    $success = false;
} else {
    $booking_id  = intval($_POST['booking_id']);
    $bookingDate = sanitize_input($_POST['bookingDate']);
    $slot        = sanitize_input($_POST['slot']); // Expected to be "morning" or "afternoon"
    $loc_id      = intval($_POST['loc_id']);
}

// Check that the new booking date is not in the past
if ($bookingDate < date('Y-m-d')) {
    $errorMsg .= "Cannot update booking to a past date.<br>";
    $success = false;
}

if ($success) {
    $config = parse_ini_file('/var/www/private/db-config.ini');
    try {
        $pdo = new PDO("mysql:host={$config['servername']};dbname={$config['dbname']};charset=utf8",
                       $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $errorMsg .= "Database connection failed: " . $e->getMessage() . "<br>";
        $success = false;
    }
}

if ($success) {
    // Update the booking ensuring it belongs to the logged-in user
    $query = "UPDATE booking SET date = ?, slot = ?, loc_id = ? WHERE booking_id = ? AND member_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$bookingDate, $slot, $loc_id, $booking_id, $member_id]);
    if ($stmt->rowCount() === 0) {
        $errorMsg .= "No booking updated. Either the booking was not found or you are not authorized.<br>";
        $success = false;
    }
}

// Display feedback
echo "<main class='container'>";
if ($success) {
    echo "<div class='alert alert-success mt-4'><h3>Booking Updated Successfully!</h3></div>";
    echo "<p><a class='btn btn-primary' href='booking.php'>Back to My Bookings</a></p>";
} else {
    echo "<div class='alert alert-danger mt-4'><h3>Update Failed</h3><p>" . $errorMsg . "</p></div>";
    echo "<p><a class='btn btn-primary' href='edit_booking.php?id=" . htmlspecialchars($booking_id) . "'>Try Again</a></p>";
}
echo "</main>";

include "inc/footer.inc.php";

/**
 * Sanitize user input.
 */
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
