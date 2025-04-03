<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Gymbros</title>      
        <meta name="description" content="Gymbros - Landing Page"/>
        <?php
            include "inc/head.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
            include "inc/enablejs.inc.php";

            displayWelcome();
            include "inc/carousel.inc.php";
        ?>
        <main class="container" id="locations">
            <!-- Northeast section -->
            <section id="northeast">
                <h2>Northeast Locations</h2>
                <div class ="row">
                    <?php
                        getLocations("Northeast");
                    ?>
                </div>
            </section>

            <!-- Southwest section -->
            <section id="southwest">
                <h2>Southwest Locations</h2>
                <div class ="row">
                <?php
                    getLocations("Southwest");
                ?>
                </div>
            </section>
        </main>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
    <?php
        /**
         * Function to retrieve location records from database.
         */
        function getLocations($area) {
            // Database connection
            $config = parse_ini_file('/var/www/private/db-config.ini');
            $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

            if ($conn->connect_error) {
                $errorMsg = "Database connection failed: " . $conn->connect_error;
                error_log("Debug: Connection failed - " . $conn->connect_error);
                echo '<h3>' . $errorMsg . '</h3>';
                return;
            }

            $stmt = $conn->prepare("SELECT loc_name,loc_addr,loc_contact,morning_slot,afternoon_slot,image_path FROM location WHERE area=?");
            $stmt->bind_param("s", $area);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0){
                while ($row = $result->fetch_assoc()) {
                    echo '<article class="col-sm">';
                    echo '<figure>';
                    echo '<img class="img-thumbnail" src="' . $row["image_path"] . '" 
                            alt="' . htmlspecialchars($row["loc_name"]) . '" title="Click to make a booking"
                            location="' . htmlspecialchars($row["loc_addr"]) . '" booking_slots="' . 
                            htmlspecialchars($row["morning_slot"]) . ', ' . htmlspecialchars($row["afternoon_slot"]) . '"
                            contact="' . htmlspecialchars($row["loc_contact"]) . '"/>';
                    echo '<figcaption>' . htmlspecialchars($row["loc_name"]) . '</figcaption>';
                    echo '</figure>';
                    echo '</article>';
                }
            }
            else {
                echo '<h3>No records found</h3>';
            }

            $stmt->close();
            $conn->close();
        }

        function displayWelcome() {
            if (isset($_SESSION['email'])) {
                $config = parse_ini_file('/var/www/private/db-config.ini');
                $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

                if ($conn->connect_error) {
                    $errorMsg = "Database connection failed: " . $conn->connect_error;
                    error_log("Debug: Connection failed - " . $conn->connect_error);
                    echo '<h3>' . $errorMsg . '</h3>';
                    return;
                }

                $stmt = $conn->prepare("SELECT fname,lname FROM gymbros_members WHERE member_id=?");
                $stmt->bind_param("s", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $fname = $row["fname"];
                    $lname = $row["lname"];
                }

                // Time-based greeting
                $hour = date('H');
                if ($hour < 12) {
                $greeting = "Good morning";
                } else if ($hour < 18) {
                $greeting = "Good afternoon";
                } else {
                $greeting = "Good evening";
                }

                echo '<style>
                    .fade-in {
                        animation: fadeIn 1s ease-in;
                    }
                    @keyframes fadeIn {
                        0% { opacity: 0; transform: translateY(20px); }
                        100% { opacity: 1; transform: translateY(0); }
                    } 
                </style>';

                echo '<div class="welcome-message text-center my-4 fade-in">
                    <h1 class="mb-3">' . $greeting . ', <span class="text-primary">' . htmlspecialchars($fname) . '</span>!</h1>
                    <p class="text-muted">What would you like to do today?</p>
                    <div class="btn-group mt-3">
                        <a href="bookings.php" class="btn btn-outline-primary">Book a Session</a>
                        <a href="profile.php" class="btn btn-outline-secondary">View Profile</a>
                    </div>
                </div>';

            }
        }
    ?>
</html>