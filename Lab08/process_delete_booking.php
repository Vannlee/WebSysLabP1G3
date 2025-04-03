<?php
date_default_timezone_set('Asia/Singapore');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Ensure the request is POST
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

// Validate booking ID from POST
if (empty($_POST['booking_id'])) {
    $errorMsg .= "Booking ID is missing.<br>";
    $success = false;
} else {
    $booking_id = intval($_POST['booking_id']);
}

if ($success) {
    // Connect to database using PDO
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
    // Verify that the booking belongs to the current user
    $query = "SELECT booking_id FROM booking WHERE booking_id = ? AND member_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$booking_id, $member_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $errorMsg .= "Booking not found or you are not authorized to delete this booking.<br>";
        $success = false;
    }
}

if ($success) {
    // Proceed to delete the booking
    $deleteQuery = "DELETE FROM booking WHERE booking_id = ? AND member_id = ?";
    $stmt = $pdo->prepare($deleteQuery);
    $stmt->execute([$booking_id, $member_id]);
    
    if ($stmt->rowCount() === 0) {
        $errorMsg .= "No booking deleted. Either it was not found or you are not authorized.<br>";
        $success = false;
    }
}

// Display feedback
echo "<main class='container'>";
if ($success) {
    echo "<div class='alert alert-success mt-4'><h3>Booking Deleted Successfully!</h3></div>";
    echo "<p><a class='btn btn-primary' href='booking.php'>Back to My Bookings</a></p>";
} else {
    echo "<div class='alert alert-danger mt-4'><h3>Deletion Failed</h3><p>" . $errorMsg . "</p></div>";
    echo "<p><a class='btn btn-primary' href='booking.php'>Back to My Bookings</a></p>";
}
echo "</main>";

include "inc/footer.inc.php";
?>
