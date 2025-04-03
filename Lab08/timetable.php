<?php
/**
 * Timetable page for Group 3's Gym website
 * Displays available gym sessions and allows users to book them
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in. If not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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

// Retrieve location data with all needed columns
$query = "
    SELECT loc_id, loc_name, morning_slot, afternoon_slot, slots_availability 
    FROM location 
    ORDER BY loc_id
";

$result = $conn->query($query);
$locations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
}

$morning_slots = [];
$afternoon_slots = [];

// Process each location to extract slot information
foreach ($locations as $location) {
    // Process morning slots if available
    if (!empty($location['morning_slot'])) {
        // Calculate slots left based on bookings
        $slots_query = "
            SELECT COUNT(*) as booked_count 
            FROM booking 
            WHERE loc_id = ? AND date = ? AND slot = ? 
        ";
        $stmt = $conn->prepare($slots_query);
        $stmt->bind_param("iss", $location['loc_id'], $selected_date, $location['morning_slot']);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking_data = $result->fetch_assoc();
        $booked_count = isset($booking_data['booked_count']) ? $booking_data['booked_count'] : 0;
        $slots_left = $location['slots_availability'] - $booked_count;
        
        // Add to morning slots array
        $morning_slots[] = [
            'loc_id' => $location['loc_id'],
            'loc_name' => $location['loc_name'],
            'slot_time' => $location['morning_slot'],
            'slots_availability' => $location['slots_availability'],
            'slots_left' => $slots_left
        ];
    }
    
    // Process afternoon slots if available
    if (!empty($location['afternoon_slot'])) {
        // Calculate slots left based on bookings
        $slots_query = "
            SELECT COUNT(*) as booked_count 
            FROM booking 
            WHERE loc_id = ? AND date = ? AND slot = ? 
        ";
        $stmt = $conn->prepare($slots_query);
        $stmt->bind_param("iss", $location['loc_id'], $selected_date, $location['afternoon_slot']);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking_data = $result->fetch_assoc();
        $booked_count = isset($booking_data['booked_count']) ? $booking_data['booked_count'] : 0;
        $slots_left = $location['slots_availability'] - $booked_count;
        
        // Add to afternoon slots array
        $afternoon_slots[] = [
            'loc_id' => $location['loc_id'],
            'loc_name' => $location['loc_name'],
            'slot_time' => $location['afternoon_slot'],
            'slots_availability' => $location['slots_availability'],
            'slots_left' => $slots_left
        ];
    }
}

// Sort locations by loc_id
usort($morning_slots, function($a, $b) {
    return $a['loc_id'] - $b['loc_id']; // Sort by loc_id
});

usort($afternoon_slots, function($a, $b) {
    return $a['loc_id'] - $b['loc_id']; // Sort by loc_id
});
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Fitness Gym - Timetable</title>
        <?php
            include "inc/head.inc.php";
            include "inc/enablejs.inc.php";
        ?>
        <style>
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
            th { background-color: #f5f5f5; }
            .book-btn { background-color: #4CAF50; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; }
            .book-btn:hover { background-color: #45a049; }
            .full { color: #ff0000; font-weight: bold; }
            .passed { color: #999; font-style: italic; }
        </style>
        <script>
            // Automatically submit the form when the date is changed
            function updateTimetable() {
                document.getElementById("date-form").submit();
            }
        </script>
    </head>
    <body>
        <?php include "inc/nav.inc.php"; ?>
        <main class="container">
            <h1>Gym Timetable</h1>
            <!-- Date selector form -->
            <form id="date-form" method="GET" action="">
                <label for="booking-date">Select Date:</label>
                <input type="date" id="booking-date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" min="<?php echo date('Y-m-d'); ?>" onchange="updateTimetable()">
            </form>
            
            <!-- Morning Slots -->
            <?php if (count($morning_slots) > 0): ?>
                <h2>Morning Sessions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Available Slots</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($morning_slots as $slot): 
                            // Extract the start time from the time range
                            $time_parts = explode(' - ', $slot['slot_time']);
                            $start_time = trim($time_parts[0]);  // Get the starting time only
                            $start_dt = new DateTime($selected_date . ' ' . $start_time);
                            
                            // For future dates, booking is available regardless of time passed today
                            $is_passed = ($selected_date === $current_date) && ($start_dt < $current_datetime);
                            
                            // Check if slot is full
                            $is_full = ($slot['slots_left'] <= 0);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($slot['slot_time']); ?></td>
                                <td><?php echo htmlspecialchars($slot['loc_name']); ?></td>
                                <td><?php echo htmlspecialchars($slot['slots_availability']); ?></td>
                                <td>
                                    <?php if ($selected_date < $current_date): ?>
                                        <span class="passed">Past Date</span>
                                    <?php elseif ($is_passed): ?>
                                        <span class="passed">Time Passed</span>
                                    <?php elseif ($is_full): ?>
                                        <span class="full">Full</span>
                                    <?php else: ?>
                                        <?php echo $slot['slots_left']; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    // Show booking button for future dates or if today's slots have not passed
                                    if (!$is_full && $selected_date >= $current_date && !$is_passed): 
                                    ?>
                                        <form action="process_booking.php" method="POST">
                                            <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                                            <input type="hidden" name="loc_id" value="<?php echo htmlspecialchars($slot['loc_id']); ?>">
                                            <input type="hidden" name="session_time" value="morning">
                                            <input type="hidden" name="slot_time" value="<?php echo htmlspecialchars($slot['slot_time']); ?>">
                                            <button type="submit" class="book-btn">Book Now</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No morning sessions available for <?php echo htmlspecialchars($selected_date); ?>.</p>
            <?php endif; ?>
            
            <!-- Afternoon Slots -->
            <?php if (count($afternoon_slots) > 0): ?>
                <h2>Afternoon Sessions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Available Slots</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($afternoon_slots as $slot): 
                        // Extract the start time from the time range
                        $time_parts = explode(' - ', $slot['slot_time']);
                        $start_time = trim($time_parts[0]);  // Get the starting time only
                        $start_dt = new DateTime($selected_date . ' ' . $start_time);
                        
                        // For future dates, booking is available regardless of time passed today
                        $is_passed = ($selected_date === $current_date) && ($start_dt < $current_datetime);
                        
                        // Check if slot is full
                        $is_full = ($slot['slots_left'] <= 0);
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($slot['slot_time']); ?></td>
                            <td><?php echo htmlspecialchars($slot['loc_name']); ?></td>
                            <td><?php echo htmlspecialchars($slot['slots_availability']); ?></td>
                            <td>
                                <?php if ($selected_date < $current_date): ?>
                                    <span class="passed">Past Date</span>
                                <?php elseif ($is_passed): ?>
                                    <span class="passed">Time Passed</span>
                                <?php elseif ($is_full): ?>
                                    <span class="full">Full</span>
                                <?php else: ?>
                                    <?php echo $slot['slots_left']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                // Show booking button for future dates or if today's slots have not passed
                                if (!$is_full && $selected_date >= $current_date && !$is_passed): 
                                ?>
                                    <form action="process_booking.php" method="POST">
                                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                                        <input type="hidden" name="loc_id" value="<?php echo htmlspecialchars($slot['loc_id']); ?>">
                                        <input type="hidden" name="session_time" value="afternoon">
                                        <input type="hidden" name="slot_time" value="<?php echo htmlspecialchars($slot['slot_time']); ?>">
                                        <button type="submit" class="book-btn">Book Now</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No afternoon sessions available for <?php echo htmlspecialchars($selected_date); ?>.</p>
            <?php endif; ?>
        </main>
        <?php include "inc/footer.inc.php"; ?>
    </body>
</html>
