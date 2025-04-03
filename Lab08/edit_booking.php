<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$member_id = $_SESSION['user_id'];

// Ensure a booking ID is provided via GET
if (!isset($_GET['id'])) {
    echo "Booking ID is missing.";
    exit();
}
$booking_id = intval($_GET['id']);

// Connect to the database
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the booking record (ensure it belongs to the current user)
$sql_booking = "SELECT booking_id, date, slot, loc_id FROM booking WHERE booking_id = ? AND member_id = ?";
$stmt = $conn->prepare($sql_booking);
$stmt->bind_param("ii", $booking_id, $member_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Booking not found or you are not authorized to edit this booking.";
    exit();
}
$booking = $result->fetch_assoc();
$stmt->close();

// Fetch locations for the dropdown
$sql_location = "SELECT loc_id, loc_name FROM location";
$locations_result = $conn->query($sql_location);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Booking - Gymbros</title>
    <?php include "inc/head.inc.php"; ?>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>
    <main class="container my-4">
        <h2>Edit Booking</h2>
        <form action="process_edit_booking.php" method="POST">
            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
            
            <div class="mb-3">
                <label for="bookingDate" class="form-label">Date</label>
                <input type="date" class="form-control" id="bookingDate" name="bookingDate" required 
                       value="<?php echo htmlspecialchars($booking['date']); ?>" min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="mb-3">
                <label for="slot" class="form-label">Slot</label>
                <select class="form-select" id="slot" name="slot" required>
                    <option value="">Choose a slot</option>
                    <option value="morning" <?php echo ($booking['slot'] === "morning") ? "selected" : ""; ?>>Morning</option>
                    <option value="afternoon" <?php echo ($booking['slot'] === "afternoon") ? "selected" : ""; ?>>Afternoon</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="loc_id" class="form-label">Location</label>
                <select class="form-select" id="loc_id" name="loc_id" required>
                    <option value="">Choose a location</option>
                    <?php
                    if ($locations_result && $locations_result->num_rows > 0) {
                        while ($loc = $locations_result->fetch_assoc()) {
                            $selected = ($loc['loc_id'] == $booking['loc_id']) ? "selected" : "";
                            echo "<option value='" . htmlspecialchars($loc['loc_id']) . "' $selected>" . htmlspecialchars($loc['loc_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Booking</button>
            <a href="booking.php" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
    <?php
    $conn->close();
    include "inc/footer.inc.php";
    ?>
</body>
</html>
