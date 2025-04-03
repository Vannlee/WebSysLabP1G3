<?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Function to check if user is logged in
    function isLoggedIn() {
        return isset($_SESSION['email']);
    }

    function sanitize_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        // Redirect to login page if not logged in
        header("Location: login.php");
        exit();
    }

    // Validate inputs
    if (empty($_POST["feedback_id"]) || empty($_POST["user_id"]) || empty($_POST["feedback_content"])) {
        $errorMsg = "All fields are required.<br>";
        $success = false;
    } else {
        $f_id = sanitize_input($_POST["feedback_id"]);
        $id = sanitize_input($_POST["user_id"]);
        $content = sanitize_input($_POST["feedback_content"]);
    }
    
    if (!empty($_POST["feedback_id"]) && !empty($_POST["user_id"]) && !empty($_POST["feedback_content"]) && isLoggedIn()) {
        $stmt = $conn->prepare("UPDATE Gymbros.membership_feedback SET content = ? WHERE feedback_id = ? && member_id = ?");
        $stmt->bind_param("sii", $content, $f_id, $id);
                
        if ($stmt->execute()) {
            include "inc/head.inc.php";
                include "inc/nav.inc.php";

                echo "<title>Update Success</title>";
                echo "<main class='container'>";
                echo '<div class="alert alert-success mt-4">
                        <h1>Feedback Update was Successful</h1>
                        <p>Thank you for your honest feedback!</p>
                        <a href="feedback.php" class="btn btn-sm btn-primary action-btn">Return to Feedback Records</a>
                      </div></main>';
        } else {
            echo "<title>Update Failed</title>";
            echo "<main class='container'>";
            echo '<div class="alert alert-danger mt-4">
                    <h1>Feedback Update was unsuccessful</h1>
                    <p>Thank you for your honest feedback!</p>
                    <a href="edit_feedback.php?id=' . $f_id . '" class="btn btn-sm btn-warning action-btn">Try Again</a>
                  </div></main>';
        }
    }
?>
