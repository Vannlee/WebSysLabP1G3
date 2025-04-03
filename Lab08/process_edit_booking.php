<?php
// Set timezone so that all DateTime operations use the correct time
date_default_timezone_set('Asia/Singapore');

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
    $bookingDate = sanitize_input($_POST['bookingDate']);  // Expected in "Y-m-d" format
    $slot        = sanitize_input($_POST['slot']);         // "morning" or "afternoon"
    $loc_id      = intval($_POST['loc_id']);
}

// Check that the new booking date is not in the past
if ($bookingDate < date('Y-m-d')) {
    $errorMsg .= "Cannot update booking to a past date.<br>";
    $success = false;
}

// If booking is for today, validate that the session has not ended
if ($success && $bookingDate == date('Y-m-d')) {
    // Connect to DB via PDO for time validation
    $config = parse_ini_file('/var/www/private/db-config.ini');
    try {
        $pdo = new PDO("mysql:host={$config['servername']};dbname={$config['dbname']};charset=utf8",
                       $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $errorMsg .= "Database connection failed: " . $e->getMessage() . "<br>";
        $success = false;
    }
    
    if ($success) {
        // Retrieve the time range for the selected slot from the location table
        $locQuery = "SELECT morning_slot, afternoon_slot FROM location WHERE loc_id = ?";
        $stmt = $pdo->prepare($locQuery);
        $stmt->execute([$loc_id]);
        $locData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$locData) {
            $errorMsg .= "Location not found for time validation.<br>";
            $success = false;
        } else {
            if ($slot === "morning") {
                $time_range = $locData['morning_slot']; // e.g. "07:00 - 10:00"
            } elseif ($slot === "afternoon") {
                $time_range = $locData['afternoon_slot']; // e.g. "13:00 - 16:00"
            } else {
                $errorMsg .= "Invalid slot selection.<br>";
                $success = false;
            }
            
            if ($success) {
                $time_parts = explode(' - ', $time_range);
                if (count($time_parts) < 2) {
                    $errorMsg .= "Invalid time range format for the selected slot.<br>";
                    $success = false;
                } else {
                    // Use the end time from the range
                    $end_time_str = trim($time_parts[1]);
                    $end_dt = new DateTime($bookingDate . ' ' . $end_time_str);
                    $current_dt = new DateTime();
                    if ($current_dt > $end_dt) {
                        $errorMsg .= "Cannot update booking: the $slot session has already ended today.<br>";
                        $success = false;
                    }
                }
            }
        }
    }
}

// Now, perform capacity check to ensure there is room in the new slot
if ($success) {
    // Check if there are available slots (excluding the current booking)
    $capacityQuery = "SELECT slots_availability FROM location WHERE loc_id = ?";
    $stmt = $pdo->prepare($capacityQuery);
    $stmt->execute([$loc_id]);
    $locData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$locData) {
        $errorMsg .= "Location not found for capacity check.<br>";
        $success = false;
    } else {
        $totalCapacity = $locData['slots_availability'];
        $countQuery = "
            SELECT COUNT(*) as booking_count 
            FROM booking 
            WHERE loc_id = ? AND date = ? AND slot = ? AND booking_id != ?
        ";
        $stmt = $pdo->prepare($countQuery);
        $stmt->execute([$loc_id, $bookingDate, $slot, $booking_id]);
        $countData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($countData['booking_count'] >= $totalCapacity) {
            $errorMsg .= "Cannot update booking: no available slots for this session.<br>";
            $success = false;
        }
    }
}

if ($success) {
    // Update the booking ensuring it belongs to the logged-in user
    $updateQuery = "UPDATE booking SET date = ?, slot = ?, loc_id = ? WHERE booking_id = ? AND member_id = ?";
    $stmt = $pdo->prepare($updateQuery);
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
