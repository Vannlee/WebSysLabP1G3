<?php
/**
 * Timetable page for Group 3's Gym website
 * Displays available gym sessions and allows users to book them
 */
date_default_timezone_set('Asia/Singapore');
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

/**
 * Helper function to count how many bookings exist for a given loc_id, date, and slot ("morning"/"afternoon").
 * This version uses bind_result() to avoid issues if MySQLnd is not installed.
 */
function getBookingCount($conn, $loc_id, $selected_date, $slotValue) {
    $booked_count = 0;
    $sql = "
        SELECT COUNT(*) AS booked_count
        FROM booking
        WHERE loc_id = ?
          AND date = ?
          AND slot = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $loc_id, $selected_date, $slotValue);
    $stmt->execute();
    
    // bind_result approach:
    $stmt->bind_result($booked_count);
    $stmt->fetch();
    
    $stmt->close();
    return $booked_count;
}

// Process each location to build morning_slots / afternoon_slots
foreach ($locations as $location) {
    // If there's a morning time range, we store it for display, but we'll use slot='morning' in the booking table
    if (!empty($location['morning_slot'])) {
        // Count existing bookings for slot = 'morning'
        $booked_count = getBookingCount($conn, $location['loc_id'], $selected_date, 'morning');
        $slots_left = $location['slots_availability'] - $booked_count;
        
        $morning_slots[] = [
            'loc_id'            => $location['loc_id'],
            'loc_name'          => $location['loc_name'],
            'time_range'        => $location['morning_slot'],  
            'slots_availability'=> $location['slots_availability'],
            'slots_left'        => $slots_left
        ];
    }

    // If there's an afternoon time range, we store it for display, but we'll use slot='afternoon' in the booking table
    if (!empty($location['afternoon_slot'])) {
        $booked_count = getBookingCount($conn, $location['loc_id'], $selected_date, 'afternoon');
        $slots_left = $location['slots_availability'] - $booked_count;
        
        $afternoon_slots[] = [
            'loc_id'            => $location['loc_id'],
            'loc_name'          => $location['loc_name'],
            'time_range'        => $location['afternoon_slot'],
            'slots_availability'=> $location['slots_availability'],
            'slots_left'        => $slots_left
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
            .book-btn { background-color: #2E7D32; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; }
            .book-btn:hover { background-color: #27632a; }
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
            <input
                type="date"
                id="booking-date"
                name="date"
                value="<?php echo htmlspecialchars($selected_date); ?>"
                min="<?php echo date('Y-m-d'); ?>"
                onchange="updateTimetable()"
            >
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
                <?php
                foreach ($morning_slots as $slot) {
                    $time_range = $slot['time_range']; 
                    $time_parts = explode('-', $time_range); 
                    $end_time_str   = trim($time_parts[1]);  

                    $end_dt   = new DateTime($selected_date . ' ' . $end_time_str);

                    $is_passed = false;
                    
                    $is_full = ($slot['slots_left'] <= 0);

                    if ($selected_date < $current_date) {
                        // The date is in the past
                        $is_passed = true;
                    } elseif ($selected_date === $current_date) {
                        // If the *end* time is before now, the session has passed
                        if ($end_dt < $current_datetime) {
                            $is_passed = true;
                        }
                    }

                    if ($is_passed) {
                        $statusText = "<span class='passed'>Time Passed</span>";
                        $canBook = false;
                    } elseif ($is_full) {
                        $statusText = "<span class='full'>Full</span>";
                        $canBook = false;
                    } else {
                        $statusText = $slot['slots_left'];
                        $canBook = true;
                    }
                
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($time_range); ?></td>
                        <td><?php echo htmlspecialchars($slot['loc_name']); ?></td>
                        <td><?php echo htmlspecialchars($slot['slots_availability']); ?></td>
                        <td><?php echo $statusText; ?></td>
                        <td>
                            <?php if ($canBook): ?>
                                <form action="process_timetable.php" method="POST">
                                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                                    <input type="hidden" name="loc_id" value="<?php echo htmlspecialchars($slot['loc_id']); ?>">
                                    <!-- We unify the slot to "morning" -->
                                    <input type="hidden" name="slot" value="morning">
                                    <button type="submit" class="book-btn">Book Now</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
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
                <?php
                foreach ($afternoon_slots as $slot) {
                    $time_range = $slot['time_range']; 
                    $time_parts = explode('-', $time_range);  
                    $end_time_str   = trim($time_parts[1]);  

                    $end_dt   = new DateTime($selected_date . ' ' . $end_time_str);

                    $is_passed = false;

                    $is_full = ($slot['slots_left'] <= 0);

                    if ($selected_date < $current_date) {
                        // The date is in the past
                        $is_passed = true;
                    } elseif ($selected_date === $current_date) {
                        // If the *end* time is before now, the session has passed
                        if ($end_dt < $current_datetime) {
                            $is_passed = true;
                        }
                    }

                    if ($is_passed) {
                        $statusText = "<span class='passed'>Time Passed</span>";
                        $canBook = false;
                    } elseif ($is_full) {
                        $statusText = "<span class='full'>Full</span>";
                        $canBook = false;
                    } else {
                        $statusText = $slot['slots_left'];
                        $canBook = true;
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($time_range); ?></td>
                        <td><?php echo htmlspecialchars($slot['loc_name']); ?></td>
                        <td><?php echo htmlspecialchars($slot['slots_availability']); ?></td>
                        <td><?php echo $statusText; ?></td>
                        <td>
                            <?php if ($canBook): ?>
                                <form action="process_timetable.php" method="POST">
                                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                                    <input type="hidden" name="loc_id" value="<?php echo htmlspecialchars($slot['loc_id']); ?>">
                                    <!-- We unify the slot to "afternoon" -->
                                    <input type="hidden" name="slot" value="afternoon">
                                    <button type="submit" class="book-btn">Book Now</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No afternoon sessions available for <?php echo htmlspecialchars($selected_date); ?>.</p>
        <?php endif; ?>
        </main>
        <?php include "inc/footer.inc.php"; ?>
    </body>
</html>