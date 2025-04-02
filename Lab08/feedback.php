<?php
    session_start();

    // Define functions
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Check if user is logged in
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    } else {
        $id = $_SESSION["user_id"];
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>View Feedback Records - Gymbros</title>      
        <meta name="description" content="Gymbros - Feedback Page"/>
        <?php
            include "inc/head.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
            include "inc/enablejs.inc.php";
        ?>
        <main class="container">
            <h2>My Membership Feedback Records</h2>
            <?php 
                getFeedbackRecords();
            ?>
        </main>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
    <?php
        /**
         * Function to retrieve location records from database.
         */
        function getFeedbackRecords() {
            global $id;

            // Database connection
            $config = parse_ini_file('/var/www/private/db-config.ini');
            $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

            if ($conn->connect_error) {
                $errorMsg = "Database connection failed: " . $conn->connect_error;
                error_log("Debug: Connection failed - " . $conn->connect_error);
                echo '<h3>' . $errorMsg . '</h3>';
                return;
            }

            $stmt = $conn->prepare("SELECT feedback_id,content,datetime FROM membership_feedback WHERE member_id=?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0){
                echo '<table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Feedback ID</th>
                                <th>Content</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>
                              <td>'.  htmlspecialchars($row['feedback_id']) . '</td>
                              <td>'.  htmlspecialchars($row['content']) . '</td>
                              <td>'.  htmlspecialchars($row['datetime']) . '</td>
                          </tr>';
                }
                echo '</tbody>
                      </table>';
            }
            else {
                echo '<h5>No Membership Feedback records found</h5>';
            }

            $stmt->close();
            $conn->close();
        }
    ?>
</html>