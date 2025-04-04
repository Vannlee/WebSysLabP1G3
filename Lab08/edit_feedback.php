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
        // User is logged in and feedback id to be updated is present
        $f_id = $_GET['id'];

        $stmt = $conn->prepare("SELECT member_id FROM Gymbros.membership_feedback WHERE feedback_id = ?");
        $stmt->bind_param("i",  $f_id);
        $stmt->execute();
        $result = $stmt->get_result();
                
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        
            if ($id != $row["member_id"]) {
                header("Location: feedback.php");
                exit();
            }
            else {
                echo "<html lang='en'>";
                include "inc/head.inc.php";
                include "inc/nav.inc.php";

                echo "<title>Update Feedback</title>";
                echo "<main class='container'>";
                echo '<form action="update_feedback.php" method="post">
                        <input type="hidden" name="feedback_id" value="' . htmlspecialchars($f_id) . '">
                        <input type="hidden" name="user_id" value="' . htmlspecialchars($id) . '">
                        <div class="alert alert-light mt-4">
                            <h1>Update Feedback</h1>
                            <label for="feedback_content" class="form-label">New Content:</label>
                            <textarea class="form-control" id="feedback_content" name="feedback_content" rows="5" required></textarea>
                        </div>
                        <button type="submit" name="update_feedback" class="btn btn-success" style="float: right;">Update</button>
                    </form>';
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