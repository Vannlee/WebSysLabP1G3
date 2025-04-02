<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

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

// Get membership details
$memberships = [
    'basic' => ['name' => 'Basic', 'price' => 0],
    'Premium' => ['name' => 'Premium', 'price' => 40],
    'Ultimate' => ['name' => 'Ultimate', 'price' => 90]
];

$errorMsg = "";
$success = true;

// ALL validation and processing logic BEFORE any HTML output

// Ensure the request is a POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect back to payment page
    header("Location: payment.php");
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    $errorMsg .= "User not logged in.<br>";
    $success = false;
}

// Check if there's a selected membership
if (!isset($_SESSION['selected_membership'])) {
    $errorMsg .= "No membership selected.<br>";
    $success = false;
} else {
    $selectedMembership = $_SESSION['selected_membership'];
}

// Get current membership
$currentMembership = getUserMembership();

// If current and selected memberships are the same, redirect
if ($currentMembership === $selectedMembership) {
    header("Location: membership.php");
    exit();
}

// Validate payment form fields
if ($success) {
    // Validate name on card
    if (empty($_POST['card_name'])) {
        $errorMsg .= "Name on card is required.<br>";
        $success = false;
    } else {
        $card_name = htmlspecialchars(stripslashes(trim($_POST['card_name'])));
        if (!preg_match('/^[A-Za-z\s\-\']+$/', $card_name)) {
            $errorMsg .= "Name on card can only contain letters, spaces, hyphens, and apostrophes.<br>";
            $success = false;
        }
    }
    
    // Validate card number
    if (empty($_POST['card_number'])) {
        $errorMsg .= "Card number is required.<br>";
        $success = false;
    } else {
        // Remove spaces from card number
        $card_number = preg_replace('/\s+/', '', $_POST['card_number']);
        
        // Check if it's exactly 16 digits
        if (!preg_match('/^[0-9]{16}$/', $card_number)) {
            $errorMsg .= "Card number must be 16 digits.<br>";
            $success = false;
        }
    }
    
    // Validate expiry date
    if (empty($_POST['expiry_date'])) {
        $errorMsg .= "Expiry date is required.<br>";
        $success = false;
    } else {
        $expiry_date = htmlspecialchars(stripslashes(trim($_POST['expiry_date'])));
        
        // Check format (MM/YY)
        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiry_date)) {
            $errorMsg .= "Expiry date must be in MM/YY format.<br>";
            $success = false;
        } else {
            // Check if card is expired
            list($month, $year) = explode('/', $expiry_date);
            $expiry_year = 2000 + (int)$year; // Convert to 4-digit year
            $expiry_month = (int)$month;
            
            $current_year = (int)date('Y');
            $current_month = (int)date('m');
            
            if ($expiry_year < $current_year || 
                ($expiry_year == $current_year && $expiry_month < $current_month)) {
                $errorMsg .= "Card has expired.<br>";
                $success = false;
            }
        }
    }
    
    // Validate CVV
    if (empty($_POST['cvv'])) {
        $errorMsg .= "CVV is required.<br>";
        $success = false;
    } else {
        $cvv = htmlspecialchars(stripslashes(trim($_POST['cvv'])));
        
        // Check if it's 3 or 4 digits
        if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
            $errorMsg .= "CVV must be 3 or 4 digits.<br>";
            $success = false;
        }
    }
    
    // Validate billing address
    if (empty($_POST['billing_address'])) {
        $errorMsg .= "Billing address is required.<br>";
        $success = false;
    } else {
        $billing_address = htmlspecialchars(stripslashes(trim($_POST['billing_address'])));
        
        // Check minimum length
        if (strlen($billing_address) < 10) {
            $errorMsg .= "Please enter a complete billing address (minimum 10 characters).<br>";
            $success = false;
        }
    }
}

// Process payment if no errors
if ($success) {
    // Store payment information in database
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
            $errorMsg .= "Failed to update membership. Please try again.<br>";
            $success = false;
        }
    } else {
        $errorMsg .= "Failed to save payment information. Please try again.<br>";
        $success = false;
    }
}

// If we've reached here without redirecting, we need to show an error
// But we need to make sure we don't output anything before potential headers
// So we'll buffer all HTML output

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment Failed - Gymbros</title>
    <?php include "inc/head.inc.php"; ?>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>
    
    <main class="container my-5">
        <div class="card shadow">
            <div class="card-header bg-danger text-white"><h3>Payment Failed</h3></div>
            <div class="card-body">
                <h4>Error:</h4>
                <p><?php echo $errorMsg; ?></p>
                <p><a class="btn btn-warning" href="payment.php">Try Again</a></p>
            </div>
        </div>
    </main>
    
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>
<?php
// Send the output
ob_end_flush();

// Close the database connection
$conn->close();
?>