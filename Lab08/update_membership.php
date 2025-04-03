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
        return isset($_SESSION['user_id']);
    }

    // Function to get user's current membership type
    function getUserMembership() {
        if (isset($_SESSION['membership'])) {
            return $_SESSION['membership'];
        }
    
        // Fallback: fetch from DB if session variable isn't set
        if (isset($_SESSION['user_id'])) {
            global $conn;
            $membership = fetchUserMembershipFromDB($conn, $_SESSION['user_id']);
            if ($membership !== null) {
                $_SESSION['membership'] = $membership;
            }
            return $membership;
        }
    
        return null;
    }
    
    // Function to fetch user membership from database
    function fetchUserMembershipFromDB($conn, $userId) {
        $stmt = $conn->prepare("SELECT membership FROM Gymbros.gymbros_members WHERE member_id = ?;");
        $stmt->bind_param("i", $userId);
        
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            return $row['membership'];
        } else {
            return null;
        }
    }
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        // Redirect to login page if not logged in
        header("Location: login.php");
        exit();
    }
    
    if (isset($_GET['newplan']) && isLoggedIn()) {
        // User is logged in and selected a new membership
        $newPlan = $_GET['newplan'];

        if ($newPlan != 'Basic') {
            // Prevent updating of other membership types here
            header("Location: membership.php");
        } else {
            // Get current membership
            $currentMembership = getUserMembership();

            // If current and selected memberships are the same, redirect to membership page
            if ($currentMembership === $newPlan) {
                header("Location: membership.php");
                exit();
            } else {
                // Update user's membership in the database
                $membershipStmt = $conn->prepare("UPDATE Gymbros.gymbros_members SET membership = ? WHERE member_id = ?");
                $membershipStmt->bind_param("si", $newPlan, $_SESSION['user_id']);
                
                if ($membershipStmt->execute()) {
                    // Update session variables
                    $_SESSION['membership'] = $newPlan;
                    
                    // Set success message and redirect
                    $_SESSION['payment_success'] = true;
                    header("Location: leave_feedback.php");
                    exit();
                } else {
                    $errors[] = "Failed to update membership. Please try again.";
                }
            }
        }
    }
?>
