<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Setup variables
$errorMsg = "";
$warningMsg = "";
$success = true;
$showWarningConfirmation = false;

// Check login
if (!isset($_SESSION['user_id'])) {
    $errorMsg .= "You must be logged in to book a session.<br>";
    $success = false;
} else {
    $member_id = $_SESSION['user_id'];
}

// Validate POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: timetable.php");
    exit();
}

// Validate fields
if (empty($_POST["date"]) || empty($_POST["loc_id"]) || empty($_POST["slot"])) {
    $errorMsg .= "Date, location, and slot are required.<br>";
    $success = false;
} else {
    $bookingDate = sanitize_input($_POST["date"]);
    $location = sanitize_input($_POST["loc_id"]);
    $slot = sanitize_input($_POST["slot"]);

    if ($bookingDate < date('Y-m-d')) {
        $errorMsg .= "Cannot book sessions for a past date.<br>";
        $success = false;
    }
}

$confirmOverride = isset($_POST["confirm_override"]) && $_POST["confirm_override"] == "1";

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
    $locationQuery = "SELECT slots_availability, loc_name FROM Gymbros.location WHERE loc_id = ?";
    $stmt = $pdo->prepare($locationQuery);
    $stmt->execute([$location]);
    $locationData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$locationData) {
        $errorMsg .= "Location not found.<br>";
        $success = false;
    } else {
        $totalCapacity = $locationData['slots_availability'];
        $currentGymName = $locationData['loc_name'];

        $checkDuplicateQuery = "
            SELECT COUNT(*) as duplicate_count 
            FROM booking 
            WHERE member_id = ? AND loc_id = ? AND date = ? AND slot = ?
        ";
        $stmt = $pdo->prepare($checkDuplicateQuery);
        $stmt->execute([$member_id, $location, $bookingDate, $slot]);
        $duplicateData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($duplicateData['duplicate_count'] > 0) {
            $errorMsg .= "You have already booked this session at " . htmlspecialchars($currentGymName) . ".<br>";
            $success = false;
        } else {
            $countQuery = "
                SELECT COUNT(*) as booking_count 
                FROM booking 
                WHERE loc_id = ? AND date = ? AND slot = ?
            ";
            $stmt = $pdo->prepare($countQuery);
            $stmt->execute([$location, $bookingDate, $slot]);
            $countData = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentBookings = $countData['booking_count'];

            if ($currentBookings >= $totalCapacity) {
                $errorMsg .= "Sorry, this session is now full.<br>";
                $success = false;
            } else {
                $checkExistingQuery = "
                    SELECT b.loc_id, l.loc_name as gym_name
                    FROM booking b
                    JOIN Gymbros.location l ON b.loc_id = l.loc_id
                    WHERE b.member_id = ? AND b.date = ? AND b.slot = ? AND b.loc_id != ?
                ";
                $stmt = $pdo->prepare($checkExistingQuery);
                $stmt->execute([$member_id, $bookingDate, $slot, $location]);
                $existingBooking = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingBooking) {
                    $warningMsg = "You already have a booking for " . htmlspecialchars($bookingDate) .
                                " (" . htmlspecialchars($slot) . " session) at " .
                                htmlspecialchars($existingBooking['gym_name']) . ".";

                    if ($confirmOverride) {
                        processBooking($member_id, $location, $bookingDate, $slot);
                    } else {
                        $showWarningConfirmation = true;
                        $success = false;
                    }
                } else {
                    processBooking($member_id, $location, $bookingDate, $slot);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Status</title>
    <?php include "inc/head.inc.php"; ?>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>

    <main class="container">
        <h1 class="visually-hidden">GymBros Booking Status</h1>

        <?php if ($success): ?>
            <div class="alert alert-success mt-4">
                <h2>Session Booked Successfully!</h2>
                <p>You have successfully booked a session. Check your bookings for details.</p>
            </div>
            <p>
                <a class="btn btn-primary" href="timetable.php">Back to Timetable</a>
                <a class="btn btn-secondary" href="booking.php">View My Bookings</a>
            </p>
        <?php elseif ($showWarningConfirmation): ?>
            <div class="alert alert-warning mt-4">
                <h2>Booking Confirmation Needed</h2>
                <p><strong>Warning:</strong> <?= $warningMsg ?></p>
                <p>Do you want to proceed with this booking anyway?</p>
                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                    <input type="hidden" name="date" value="<?= htmlspecialchars($bookingDate) ?>">
                    <input type="hidden" name="loc_id" value="<?= htmlspecialchars($location) ?>">
                    <input type="hidden" name="slot" value="<?= htmlspecialchars($slot) ?>">
                    <input type="hidden" name="confirm_override" value="1">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-warning">Yes, Book Anyway</button>
                        <a href="timetable.php" class="btn btn-secondary">No, Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-danger mt-4">
                <h2>Booking Failed</h2>
                <p><?= $errorMsg ?></p>
            </div>
            <p><a class="btn btn-primary" href="timetable.php">Back to Timetable</a></p>
        <?php endif; ?>
    </main>

    <?php include "inc/footer.inc.php"; ?>
</body>
</html>

<?php
// Sanitize user input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Insert booking into database
function processBooking($member_id, $location, $bookingDate, $slot) {
    global $errorMsg, $success, $pdo;

    try {
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
