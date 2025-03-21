<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Fitness Gym</title>      
        <meta name="description" content="Fitness Gym - Landing Page"/>
        <?php
            include "inc/head.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
            include "inc/carousel.inc.php";
        ?>
        <main class="container" id="locations">
            <!-- Northeast section -->
            <section id="northeast">
                <h2>Southwest Locations</h2>
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
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/cck_branch.jpg" 
                            alt="Choa Chu Kang GymBro Branch" title="Click to make a booking"
                            location="Choa Chu Kang Central, #999" hours="05:00 - 21:00"
                            contact="66669999"/>
                        <figcaption>Choa Chu Kang GymBro Branch</figcaption>
                    </figure>
                </article>
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/batok_branch.jpg" 
                            alt="Bukit Batok GymBro Branch" title="Click to make a booking"
                            location="Bukit Batok West Ave 46, #009" hours="07:00 - 23:00"
                            contact="64447888"/>
                        <figcaption>Bukit Batok GymBro Branch</figcaption>
                    </figure>
                </article>
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/jr_west_branch.jpg" 
                            alt="jurong West GymBro Branch" title="Click to make a booking"
                            location="Joo Koon Road, #666" hours="04:00 - 21:00"
                            contact="90000001"/>
                        <figcaption>Jurong West GymBro Branch</figcaption>
                    </figure>
                </article>
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
</html>

<?php
    /**
     * Function to register user in the database.
     */
    function getLocations($location) {
        // Database connection
        $config = parse_ini_file('/var/www/private/db-config.ini');
        $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

        if ($conn->connect_error) {
            $errorMsg = "Database connection failed: " . $conn->connect_error;
            error_log("Debug: Connection failed - " . $conn->connect_error);
            $success = false;
            return;
        }

        $stmt = $conn->prepare("SELECT loc_name,loc_addr,loc_contact,morning_slot,afternoon_slot,image_path FROM Gymbros.location WHERE area=?;");
        $stmt->bind_param("s", $location);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<article class="col-sm">';
                echo '<figure>';
                echo '<img class="img-thumbnail" src="' . $row["image_path"] . '" 
                        alt="' . $row["loc_name"] . '" title="Click to make a booking"
                        location="' . $row["loc_addr"] . '" booking_hours="' . $row["morning_slot"] . ', ' . $row["afternoon_slot"] . '"
                        contact="' . $row["loc_contact"] . '"/>';
                echo '<figcaption>' . $row["loc_name"] . '</figcaption>';
                echo '</figure>';
                echo '</article>';
            }
        }

        $stmt->close();
        $conn->close();
    }
?>