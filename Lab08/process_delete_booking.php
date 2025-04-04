<?php
date_default_timezone_set('Asia/Singapore');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errorMsg = "";
$success = true;

// Redirect if not POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: booking.php");
    exit();
}

// Check user session
if (!isset($_SESSION['user_id'])) {
    $errorMsg .= "User not logged in.<br>";
    $success = false;
} else {
    $member_id = $_SESSION['user_id'];
}

// Validate booking ID
if (empty($_POST['booking_id'])) {
    $errorMsg .= "Booking ID is missing.<br>";
    $success = false;
} else {
    $booking_id = intval($_POST['booking_id']);
}

// Database connection
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

// Verify ownership
if ($success) {
    $query = "SELECT booking_id FROM booking WHERE booking_id = ? AND member_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$booking_id, $member_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $errorMsg .= "Booking not found or unauthorized access.<br>";
        $success = false;
    }
}

// Delete booking
if ($success) {
    $deleteQuery = "DELETE FROM booking WHERE booking_id = ? AND member_id = ?";
    $stmt = $pdo->prepare($deleteQuery);
    $stmt->execute([$booking_id, $member_id]);

    if ($stmt->rowCount() === 0) {
        $errorMsg .= "No booking deleted. It may have already been removed.<br>";
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Delete Booking Status</title>
    <?php include "inc/head.inc.php"; ?>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>

    <main class="container my-4">
        <h1>Booking Deletion</h1>

        <?php if ($success): ?>
            <div class="alert alert-success mt-4">
                <h2>Booking Deleted Successfully!</h2>
                <p>Your gym session booking has been removed.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-danger mt-4">
                <h2>Deletion Failed</h2>
                <p><?= $errorMsg ?></p>
            </div>
        <?php endif; ?>

        <p>
            <a class="btn btn-primary" href="booking.php">Back to My Bookings</a>
        </p>
    </main>

    <?php include "inc/footer.inc.php"; ?>
</body>
</html>
