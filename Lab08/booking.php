<?php
date_default_timezone_set('Asia/Singapore');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$member_id = $_SESSION['user_id'];

// Get the selected date from URL parameters or default to today
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$current_date = date('Y-m-d');
$current_datetime = new DateTime();

// Get database configuration
$config = parse_ini_file('/var/www/private/db-config.ini');

// Connect to database using mysqli
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Updated query: Retrieve booking and location details including time ranges
$sql_booking = "SELECT 
                    b.booking_id, 
                    CONCAT(gm.fname, ' ', gm.lname) AS member_name, 
                    l.loc_name, 
                    b.date, 
                    b.slot,
                    l.morning_slot,
                    l.afternoon_slot
                FROM booking b 
                JOIN gymbros_members gm ON b.member_id = gm.member_id
                JOIN location l ON b.loc_id = l.loc_id
                WHERE b.member_id = ?";
$stmt = $conn->prepare($sql_booking);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$booking_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Bookings - Gymbros</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background-color: #f5f5f5; }
        .action-btn { margin-right: 5px; }
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>
    <main class="container my-4">
        <h2>My Bookings</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Member Name</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Slot</th>
                    <th>Status</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($booking_result->num_rows > 0) {
                    while ($row = $booking_result->fetch_assoc()) {
                        // Determine status based on the booking date and slot
                        $status = "";
                        $bookingDate = $row['date'];
                        if ($bookingDate > $current_date) {
                            $status = "Upcoming";
                        } elseif ($bookingDate < $current_date) {
                            $status = "Over";
                        } else {
                            // Booking date is today; determine based on session time range
                            if ($row['slot'] === "morning") {
                                $time_range = $row['morning_slot']; // e.g. "07:00 - 10:00"
                            } else {
                                $time_range = $row['afternoon_slot']; // e.g. "13:00 - 16:00"
                            }
                            // Parse start and end times from the time range
                            $time_parts = explode(' - ', $time_range);
                            $start_time_str = trim($time_parts[0]);
                            $end_time_str = trim($time_parts[1]);
                            $start_dt = new DateTime($bookingDate . ' ' . $start_time_str);
                            $end_dt = new DateTime($bookingDate . ' ' . $end_time_str);
                            $current_dt = new DateTime();
                            
                            if ($current_dt < $start_dt) {
                                $status = "Upcoming";
                            } elseif ($current_dt >= $start_dt && $current_dt <= $end_dt) {
                                $status = "In Session";
                            } else {
                                $status = "Over";
                            }
                        }
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['booking_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['member_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['loc_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['slot']) . "</td>";
                        echo "<td>" . htmlspecialchars($status) . "</td>";
                        if ($status === "Upcoming") {
                            echo "<td><a href='edit_booking.php?id=" . htmlspecialchars($row['booking_id']) . "' class='btn btn-sm btn-warning action-btn'>Edit</a></td>";
                            echo "<td><a href='process_delete_booking.php?id=" . htmlspecialchars($row['booking_id']) . "' class='btn btn-sm btn-danger action-btn' onclick='return confirm(\"Are you sure you want to delete this booking?\");'>Delete</a></td>";
                        } else {
                            echo "<td><span class='btn btn-sm btn-secondary action-btn disabled'>Edit</span></td>";
                            echo "<td><span class='btn btn-sm btn-secondary action-btn disabled'>Delete</span></td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No bookings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
    <?php
    $stmt->close();
    $conn->close();
    include "inc/footer.inc.php";
    ?>
</body>
</html>
