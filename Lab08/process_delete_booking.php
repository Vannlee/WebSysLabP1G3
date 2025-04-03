<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $errorMsg .= "User not logged in.<br>";
    $success = false;
} else {
    $member_id = $_SESSION['user_id'];
}

// Validate booking ID from GET parameter
if (!isset($_GET['id'])) {
    $errorMsg .= "Booking ID is missing.<br>";
    $success = false;
} else {
    $booking_id = intval($_GET['id']);
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
    // Delete booking ensuring it belongs to the logged-in user
    $query = "DELETE FROM booking WHERE booking_id = ? AND member_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$booking_id, $member_id]);
    if ($stmt->rowCount() === 0) {
        $errorMsg .= "No booking deleted. Either the booking was not found or you are not authorized.<br>";
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
