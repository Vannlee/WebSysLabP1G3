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

// Validate required fields
if (empty($_POST["date"]) || empty($_POST["loc_id"]) || empty($_POST["session_time"]) || empty($_POST["slot_time"])) {
    $errorMsg .= "All booking details are required.<br>";
    $success = false;
} else {
    // Sanitize inputs
    $bookingDate = sanitize_input($_POST["date"]);
    $location = sanitize_input($_POST["loc_id"]);
    $sessionTime = sanitize_input($_POST["session_time"]);
    $slotTime = sanitize_input($_POST["slot_time"]);

    // Set default values for the booking system
    $class = ($sessionTime === "morning") ? "Morning Workout" : "Afternoon Training";
    $instructor = ($sessionTime === "morning") ? "John Smith" : "Sarah Johnson";
}

// Check if the date is in the past
$bookingDateTime = new DateTime($bookingDate . ' ' . $slotTime);
$currentDateTime = new DateTime();
if ($bookingDateTime < $currentDateTime) {
    $errorMsg .= "Cannot book sessions in the past.<br>";
    $success = false;
}

// If everything is okay so far, check availability and process the booking
if ($success) {
    // Check if there are available slots
    $config = parse_ini_file('/var/www/private/db-config.ini');
    $pdo = new PDO("mysql:host={$config['servername']};dbname={$config['dbname']};charset=utf8", 
                  $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
        
        // Count existing bookings for this timeslot
        $countQuery = "
            SELECT COUNT(*) as booking_count 
            FROM booking 
            WHERE loc_id = ? AND date = ? AND 
            " . ($sessionTime === "morning" ? "HOUR(morning_slot)" : "HOUR(afternoon_slot)") . " = HOUR(?)
        ";
        $stmt = $pdo->prepare($countQuery);
        $stmt->execute([$location, $bookingDate, $slotTime]);
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
                WHERE member_id = ? AND loc_id = ? AND date = ? AND 
                " . ($sessionTime === "morning" ? "HOUR(morning_slot)" : "HOUR(afternoon_slot)") . " = HOUR(?)
            ";
            $stmt = $pdo->prepare($checkExistingQuery);
            $stmt->execute([$member_id, $location, $bookingDate, $slotTime]);
            $existingData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingData['existing_count'] > 0) {
                $errorMsg .= "You already have a booking for this session.<br>";
                $success = false;
            } else {
                // Process the booking
                processBooking($member_id, $location, $bookingDate, $class, $instructor, $sessionTime, $slotTime);
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
function processBooking($member_id, $location, $bookingDate, $class, $instructor, $sessionTime, $slotTime) {
    global $errorMsg, $success;

    try {
        // Read database credentials from a secure config file
        $config = parse_ini_file('/var/www/private/db-config.ini');
        $pdo = new PDO("mysql:host={$config['servername']};dbname={$config['dbname']};charset=utf8", 
                      $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Define slot field based on session time
        $slotField = ($sessionTime === "morning") ? "morning_slot" : "afternoon_slot";
        
        // Prepare the INSERT statement
        $query = "INSERT INTO booking (member_id, loc_id, date, class, instructor, {$slotField}) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$member_id, $location, $bookingDate, $class, $instructor, $slotTime]);
        
        // Check if the insert was successful
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