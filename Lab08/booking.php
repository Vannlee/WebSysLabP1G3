<?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $member_id = $_SESSION['user_id'];

    // Query to fetch bookings for the logged-in user (joining members and location for display)
    $sql_booking = "SELECT 
                        b.booking_id, 
                        CONCAT(gm.fname, ' ', gm.lname) AS member_name, 
                        l.loc_name, 
                        b.date, 
                        b.class, 
                        b.instructor 
                    FROM booking b 
                    JOIN gymbros_members gm ON b.member_id = gm.member_id
                    JOIN location l ON b.loc_id = l.loc_id
                    WHERE b.member_id = ?";
    $stmt = $conn->prepare($sql_booking);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $booking_result = $stmt->get_result();

    // Query to fetch locations for the dropdown in the booking form
    $sql_location = "SELECT loc_id, loc_name FROM location";
    $location_result = $conn->query($sql_location);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Fitness Gym</title>
        <?php
            include "inc/head.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
        ?>

        <main>
            <div class="container my-4">
                <div class="row">
                    <!-- Left Column: Placeholder Table -->
                    <div class="col-md-6">
                        <h2>My Bookings</h2>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Member Name</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                    <th>Class</th>
                                    <th>Instructor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($booking_result->num_rows > 0) {
                                    while ($row = $booking_result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['booking_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['member_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['loc_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['class']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['instructor']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No bookings found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Right Column: Booking Form -->
                    <div class="col-md-6">
                        <h2>Book a Class</h2>
                        <!-- The form submits to process_booking.php -->
                        <form id="bookingForm" action="process_booking.php" method="POST">
                            <div class="mb-3">
                            <label for="bookingDate" class="form-label">Select Date</label>
                            <input type="date" class="form-control" id="bookingDate" name="bookingDate" required>
                            </div>

                            <div class="mb-3">
                            <label for="location" class="form-label">Select Location</label>
                            <select class="form-select" id="location" name="location" required>
                                <option value="">Choose a location</option>
                                <?php
                                if ($location_result && $location_result->num_rows > 0) {
                                    while ($row = $location_result->fetch_assoc()) {
                                        echo '<option value="' . $row['loc_id'] . '">' . htmlspecialchars($row['loc_name']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No locations found</option>';
                                }
                                ?>
                            </select>
                            </div>

                            <!-- Hidden fields for static values (class & instructor) -->
                            <input type="hidden" name="class" value="Yoga">
                            <input type="hidden" name="instructor" value="Jane Doe">

                            <button type="submit" class="btn btn-primary">Book Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <?php
            $conn->close();
            include "inc/footer.inc.php";
        ?>
    </body>
</html>