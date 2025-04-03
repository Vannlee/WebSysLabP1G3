<?php
    session_start();

    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Function to check if user is logged in
    function isLoggedIn() {
        return isset($_SESSION['email']);
    } 
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        // Redirect to login page if not logged in
        header("Location: login.php");
        exit();
    } else {
        $id = $_SESSION['user_id'];
    }

    if (!isset($_GET['id'])) {
            header("Location: feedback.php");
            exit();
    }
    
    if (isset($_GET['id']) && isLoggedIn()) {
        // User is logged in and feedback id to be deleted is present
        $f_id = $_GET['id'];

        $stmt = $conn->prepare("SELECT member_id FROM Gymbros.membership_feedback WHERE feedback_id = ?");
        $stmt->bind_param("i",  $f_id);
        $stmt->execute();
        $result = $stmt->get_result();
                
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // if feedback is made by another user redirect user to feedback page
            if ($id != $row["member_id"]) {
                header("Location: feedback.php");
                exit();
            }
            else {
                $deleteStmt = $conn->prepare("DELETE FROM Gymbros.membership_feedback WHERE feedback_id = ?");
                $deleteStmt->bind_param("i", $f_id);

                if ($deleteStmt->execute()) {
                    include "inc/head.inc.php";
                    include "inc/nav.inc.php";

                    echo "<title>Delete Success</title>";
                    echo "<main class='container'>";
                    echo '<div class="alert alert-success mt-4">
                            <h1>Feedback Successfully Deleted</h1>
                            <p>Your feedback record has been removed.</p>
                            <a href="feedback.php" class="btn btn-sm btn-primary action-btn">Return to Feedback Records</a>
                          </div></main>';
                }
                else {
                    echo "<title>Delete Failed</title>";
                    echo "<main class='container'>";
                    echo '<div class="alert alert-danger mt-4">
                            <h1>Delete was Unsuccessful</h1>
                            <p>Kindly return to the Feedback Records page and try again</p>
                            <a href="feedback.php" class="btn btn-sm btn-primary action-btn">Return to Feedback Records</a>
                        </div></main>';
                }
            }
        }
        else {
            include "inc/head.inc.php";
            include "inc/nav.inc.php";
            
            echo "<title>No Record Found</title>";
            echo "<main class='container'>";
            echo "<div class='alert alert-danger mt-4'>";
            echo "<h3>No Feedback Record Found</h3>";
            echo "<p>This is no such record for this feedback, kindly return to the feedback page and try again.</p>";
            echo "</div>";
            echo "<p><a class='btn btn-primary' href='feedback.php'>Back to Feedback Records</a></p></main>";
        }
        
        $stmt->close();
        $conn->close();
    }

    include "inc/footer.inc.php";
?>