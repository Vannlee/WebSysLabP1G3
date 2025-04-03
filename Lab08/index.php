<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Gymbros</title>      
        <meta name="description" content="Gymbros - Landing Page">
        <?php
            include "inc/head.inc.php";
            include "inc/enablejs.inc.php";
        ?>
        <link rel="stylesheet" href="css/jumbotron.css">

    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
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
                            location="' . htmlspecialchars($row["loc_addr"]) . '" 
                            booking-slots="' . htmlspecialchars($row["morning_slot"]) . ', ' . htmlspecialchars($row["afternoon_slot"]) . '"
                            contact="' . htmlspecialchars($row["loc_contact"]) . '">';
                    echo '</figure>';
                    echo '<h3 class="article-heading text-center h5">' . htmlspecialchars($row["loc_name"]) . '</h3>';
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

                echo '<div class="welcome-jumbotron text-center fade-in">
                <div class="container">
                    <h1 class="display-4">' . $greeting . ', <span class="text-warning">' . htmlspecialchars($fname) . '</span>!</h1>
                    <p class="lead">Ready to crush your fitness goals today?</p>
                    <hr class="my-4 bg-light">
                    </div>
                </div>
            </div>';

            }
        }
    ?>
</html>