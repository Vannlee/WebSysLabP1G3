<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$current_datetime = new DateTime();

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Using the variables from the config
    $host = $config['servername'];
    $user = $config['username'];
    $pass = $config['password'];
    $dbname = $config['dbname'];
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Retrieve location data directly from the location table
$query = "
    SELECT loc_id, loc_name, morning_slot, afternoon_slot, slots_availability 
    FROM Gymbros.location 
    ORDER BY loc_id
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create morning and afternoon slots based on location data
$morning_slots = [];
$afternoon_slots = [];

foreach ($locations as $location) {
    // Create morning slot data if available
    if (!empty($location['morning_slot'])) {
        // Extract time from morning_slot field
        $morning_time = $location['morning_slot'];
        
        // Calculate slots left based on bookings
        $slots_query = "
            SELECT COUNT(*) as booked_count 
            FROM booking 
            WHERE loc_id = ? AND date = ? AND HOUR(slot) = HOUR(?)
        ";
        $stmt = $pdo->prepare($slots_query);
        $stmt->execute([$location['loc_id'], $selected_date, $morning_time]);
        $booking_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $booked_count = isset($booking_data['booked_count']) ? $booking_data['booked_count'] : 0;
        $slots_left = $location['slots_availability'] - $booked_count;
        
        $morning_slots[] = [
            'loc_id' => $location['loc_id'],
            'loc_name' => $location['loc_name'],
            'slot_time' => $morning_time,
            'morning_slot' => $location['morning_slot'],
            'slots_availability' => $location['slots_availability'],
            'slots_left' => $slots_left
        ];
    }
    
    // Create afternoon slot data if available
    if (!empty($location['afternoon_slot'])) {
        // Extract time from afternoon_slot field
        $afternoon_time = $location['afternoon_slot'];
        
        // Calculate slots left based on bookings
        $slots_query = "
            SELECT COUNT(*) as booked_count 
            FROM booking 
            WHERE loc_id = ? AND date = ? AND HOUR(slot) = HOUR(?)
        ";
        $stmt = $pdo->prepare($slots_query);
        $stmt->execute([$location['loc_id'], $selected_date, $afternoon_time]);
        $booking_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $booked_count = isset($booking_data['booked_count']) ? $booking_data['booked_count'] : 0;
        $slots_left = $location['slots_availability'] - $booked_count;
        
        $afternoon_slots[] = [
            'loc_id' => $location['loc_id'],
            'loc_name' => $location['loc_name'],
            'slot_time' => $afternoon_time,
            'afternoon_slot' => $location['afternoon_slot'],
            'slots_availability' => $location['slots_availability'],
            'slots_left' => $slots_left
        ];
    }
}

// Sort slots by time
usort($morning_slots, function($a, $b) {
    return strcmp($a['slot_time'], $b['slot_time']);
});

usort($afternoon_slots, function($a, $b) {
    return strcmp($a['slot_time'], $b['slot_time']);
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
    </head>
    <body>
        <?php include "inc/nav.inc.php"; ?>
        <main class="container">
            <h1>Gym Timetable</h1>
            <!-- Date selector form -->
            <form method="GET" action="">
                <label for="booking-date">Select Date:</label>
                <input type="date" id="booking-date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" min="<?php echo date('Y-m-d'); ?>">
                <input type="submit" value="Show Timetable">
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
                            // Parse the time, handling possible ranges like "04:00 - 12:00"
                            $time_str = $slot['slot_time'];
                            if (strpos($time_str, '-') !== false) {
                                $parts = explode('-', $time_str);
                                $start_time = trim($parts[0]); // use the first part as the start time
                            } else {
                                $start_time = trim($time_str);
                            }
                            $start_dt = new DateTime($selected_date . ' ' . $start_time);
                            $end_dt = clone $start_dt;
                            $end_dt->modify('+1 hour');
                            
                            // Handle past times and full slots
                            $is_passed = ($selected_date === date('Y-m-d') && $start_dt < $current_datetime);
                            $is_full = ($slot['slots_left'] <= 0);
                        ?>
                            <tr>
                                <td><?php echo $start_dt->format("g:i A") . " - " . $end_dt->format("g:i A"); ?></td>
                                <td><?php echo htmlspecialchars($slot['loc_name']); ?></td>
                                <td><?php echo htmlspecialchars($slot['slots_availability']); ?></td>
                                <td>
                                    <?php if ($is_passed): ?>
                                        <span class="passed">Passed</span>
                                    <?php elseif ($is_full): ?>
                                        <span class="full">Full</span>
                                    <?php else: ?>
                                        <?php echo $slot['slots_left']; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$is_passed && !$is_full): ?>
                                        <form action="process_timetable.php" method="POST">
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
                        // Parse the time, handling possible ranges like "04:00 - 12:00"
                        $time_str = $slot['slot_time'];
                        if (strpos($time_str, '-') !== false) {
                            $parts = explode('-', $time_str);
                            $start_time = trim($parts[0]);
                        } else {
                            $start_time = trim($time_str);
                        }
                        $start_dt = new DateTime($selected_date . ' ' . $start_time);
                        $end_dt = clone $start_dt;
                        $end_dt->modify('+1 hour');
                        
                        $is_passed = ($selected_date === date('Y-m-d') && $start_dt < $current_datetime);
                        $is_full = ($slot['slots_left'] <= 0);
                    ?>
                        <tr>
                            <td><?php echo $start_dt->format("g:i A") . " - " . $end_dt->format("g:i A"); ?></td>
                            <td><?php echo htmlspecialchars($slot['loc_name']); ?></td>
                            <td><?php echo htmlspecialchars($slot['slots_availability']); ?></td>
                            <td>
                                <?php if ($is_passed): ?>
                                    <span class="passed">Passed</span>
                                <?php elseif ($is_full): ?>
                                    <span class="full">Full</span>
                                <?php else: ?>
                                    <?php echo $slot['slots_left']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$is_passed && !$is_full): ?>
                                    <form action="process_timetable.php" method="POST">
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