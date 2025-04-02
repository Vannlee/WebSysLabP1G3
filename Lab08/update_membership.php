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
    
    if (isset($_GET['newplan']) && !isLoggedIn()) {
        // User clicked a membership but isn't logged in, store selection in session
        $_SESSION['selected_membership'] = $_GET['newplan'];
        // Redirect to login page
        header("Location: login.php");
        exit();
    } elseif (isset($_GET['newplan']) && isLoggedIn()) {
        // User is logged in and selected a new plan
        $newPlan = $_GET['newplan'];

        // Get current and selected membership
        $currentMembership = getUserMembership();
        $selectedMembership = $_SESSION['selected_membership'];
        
    }
    
    
    
    // If current and selected memberships are the same, redirect to membership page
    if ($currentMembership === $selectedMembership) {
        header("Location: membership.php");
        exit();
    }
    
    // Get membership details
    $memberships = [
        'basic' => ['name' => 'Basic', 'price' => 0],
        'Premium' => ['name' => 'Premium', 'price' => 40],
        'Ultimate' => ['name' => 'Ultimate', 'price' => 90]
    ];
    
    // Handle payment submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
        // Validate payment form
        $errors = [];
        
        if (empty($_POST['card_name'])) {
            $errors[] = "Name on card is required";
        }
        
        if (empty($_POST['card_number']) || !preg_match('/^[0-9]{16}$/', $_POST['card_number'])) {
            $errors[] = "Valid 16-digit card number is required";
        }
        
        if (empty($_POST['expiry_date']) || !preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $_POST['expiry_date'])) {
            $errors[] = "Valid expiry date (MM/YY) is required";
        }
        
        if (empty($_POST['cvv']) || !preg_match('/^[0-9]{3,4}$/', $_POST['cvv'])) {
            $errors[] = "Valid CVV is required";
        }
        
        // Process payment if no errors
        if (empty($errors)) {
            // Store payment information in database (FOR EDUCATIONAL PURPOSES ONLY)
            $stmt = $conn->prepare("INSERT INTO Gymbros.payment_methods 
                (member_id, card_name, card_number, expiry_date, cvv, billing_address) 
                VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("isssss", 
                $_SESSION['user_id'], 
                $_POST['card_name'],
                $_POST['card_number'],
                $_POST['expiry_date'],
                $_POST['cvv'],
                $_POST['billing_address']
            );
            
            // Execute the payment method storage
            if ($stmt->execute()) {
                // Update user's membership in the database
                $membershipStmt = $conn->prepare("UPDATE Gymbros.gymbros_members SET membership = ? WHERE member_id = ?");
                $membershipStmt->bind_param("si", $selectedMembership, $_SESSION['user_id']);
                
                if ($membershipStmt->execute()) {
                    // Update session variables
                    $_SESSION['membership'] = $selectedMembership;
                    unset($_SESSION['selected_membership']);
                    
                    // Set success message and redirect
                    $_SESSION['payment_success'] = true;
                    header("Location: membership.php");
                    exit();
                } else {
                    $errors[] = "Failed to update membership. Please try again.";
                }
            } else {
                $errors[] = "Failed to save payment information. Please try again.";
            }
        }
    }
?>
