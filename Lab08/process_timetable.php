<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include common head and nav sections
include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Ensure the request is a POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: timetable.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $errorMsg .= "You must be logged in to book a session.<br>";
    $success = false;
} else {
    $member_id = $_SESSION['user_id'];
}

// Validate required fields: date, loc_id, and slot (expected to be "morning" or "afternoon")
if (empty($_POST["date"]) || empty($_POST["loc_id"]) || empty($_POST["slot"])) {
    $errorMsg .= "Date, location, and slot are required.<br>";
    $success = false;
} else {
    // Sanitize inputs
    $bookingDate = sanitize_input($_POST["date"]);
    $location    = sanitize_input($_POST["loc_id"]);
    $slot        = sanitize_input($_POST["slot"]); // "morning" or "afternoon"
}

// Check if the booking date is in the past
if ($bookingDate < date('Y-m-d')) {
    $errorMsg .= "Cannot book sessions for a past date.<br>";
    $success = false;
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
    // Get location details
    $locationQuery = "SELECT slots_availability FROM Gymbros.location WHERE loc_id = ?";
    $stmt = $pdo->prepare($locationQuery);
    $stmt->execute([$location]);
    $locationData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$locationData) {
        $errorMsg .= "Location not found.<br>";
        $success = false;
    } else {
        $totalCapacity = $locationData['slots_availability'];
        
        // Count existing bookings for this timeslot (using unified slot value)
        $countQuery = "
            SELECT COUNT(*) as booking_count 
            FROM booking 
            WHERE loc_id = ? AND date = ? AND slot = ?
        ";
        $stmt = $pdo->prepare($countQuery);
        $stmt->execute([$location, $bookingDate, $slot]);
        $countData = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentBookings = $countData['booking_count'];
        
        // Check if there's space available
        if ($currentBookings >= $totalCapacity) {
            $errorMsg .= "Sorry, this session is now full.<br>";
            $success = false;
        } else {
            // Check if user already has a booking for this slot
            $checkExistingQuery = "
                SELECT COUNT(*) as existing_count 
                FROM booking 
                WHERE member_id = ? AND loc_id = ? AND date = ? AND slot = ?
            ";
            $stmt = $pdo->prepare($checkExistingQuery);
            $stmt->execute([$member_id, $location, $bookingDate, $slot]);
            $existingData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingData['existing_count'] > 0) {
                $errorMsg .= "You already have a booking for this session.<br>";
                $success = false;
            } else {
                // Process the booking
                processBooking($member_id, $location, $bookingDate, $slot);
            }
        }
    }
}

// Display success or error message
echo "<main class='container'>";
if ($success) {
    echo "<title>Booking Successful</title>";
    echo "<div class='alert alert-success mt-4'>";
    echo "<h3>Session Booked Successfully!</h3>";
    echo "<p>You have successfully booked a session. Check your bookings for details.</p>";
    echo "</div>";
    echo "<p><a class='btn btn-primary' href='timetable.php'>Back to Timetable</a> ";
    echo "<a class='btn btn-secondary' href='booking.php'>View My Bookings</a></p>";
} else {
    echo "<title>Booking Failed</title>";
    echo "<div class='alert alert-danger mt-4'>";
    echo "<h3>Booking Failed</h3>";
    echo "<h4>Error:</h4><p>" . $errorMsg . "</p>";
    echo "</div>";
    echo "<p><a class='btn btn-primary' href='timetable.php'>Back to Timetable</a></p>";
}
echo "</main>";

include "inc/footer.inc.php";

// Sanitize user input.
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Process the booking by inserting a new record into the database.
 * This function inserts into the unified booking schema using the $slot value.
 */
function processBooking($member_id, $location, $bookingDate, $slot) {
    global $errorMsg, $success;

    try {
        $config = parse_ini_file('/var/www/private/db-config.ini');
        $pdo = new PDO("mysql:host={$config['servername']};dbname={$config['dbname']};charset=utf8", 
                      $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare the INSERT statement into the unified booking table
        $query = "INSERT INTO booking (member_id, loc_id, date, slot) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$member_id, $location, $bookingDate, $slot]);
        
        if ($stmt->rowCount() === 0) {
            $errorMsg .= "Error inserting booking into database.<br>";
            $success = false;
        }
        
    } catch (PDOException $e) {
        $errorMsg .= "Database error: " . $e->getMessage() . "<br>";
        $success = false;
    }
}
?>
