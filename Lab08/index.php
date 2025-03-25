<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Fitness Gym</title>      
        <meta name="description" content="Fitness Gym - Landing Page"/>
        <?php
            session_start();
            include "inc/head.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
            include "inc/enablejs.inc.php";
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
        <noscript>
            <p style="color: red;">JavaScript is disabled in your browser. Please enable it for the best experience.</p>
        </noscript>
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
                            alt="' . $row["loc_name"] . '" title="Click to make a booking"
                            location="' . $row["loc_addr"] . '" booking_slots="' . $row["morning_slot"] . ', ' . $row["afternoon_slot"] . '"
                            contact="' . $row["loc_contact"] . '"/>';
                    echo '<figcaption>' . $row["loc_name"] . '</figcaption>';
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
    ?>
</html>