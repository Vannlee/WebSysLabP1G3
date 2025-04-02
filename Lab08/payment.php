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
    
    // Check if there's a selected membership
    if (!isset($_SESSION['selected_membership'])) {
        // Redirect to membership page if no membership is selected
        header("Location: membership.php");
        exit();
    }
    
    // Get current and selected membership
    $currentMembership = getUserMembership();
    $selectedMembership = $_SESSION['selected_membership'];
    
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
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment</title>
    <?php
        include "inc/head.inc.php";
        include "inc/enablejs.inc.php";
    ?>
</head>
<body>
    <?php
        include "inc/nav.inc.php";
    ?>
    
    <main class="container my-5">
        <section class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Payment Details</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <h5>Order Summary</h5>
                            <div class="d-flex justify-content-between">
                                <span>Current Plan:</span>
                                <span><?php echo htmlspecialchars($memberships[$currentMembership]['name']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>New Plan:</span>
                                <span><?php echo htmlspecialchars($memberships[$selectedMembership]['name']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold mt-2">
                                <span>Total:</span>
                                <span>$<?php echo number_format($memberships[$selectedMembership]['price'], 2); ?>/month</span>
                            </div>
                        </div>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="card_name" name="card_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="password" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="billing_address" class="form-label">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3" required></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="process_payment" class="btn btn-primary">Pay $<?php echo number_format($memberships[$selectedMembership]['price'], 2); ?></button>
                                <a href="membership.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php
        include "inc/footer.inc.php";
    ?>

    <script>
        // Simple form validation for payment fields
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const cardNumberInput = document.getElementById('card_number');
            const expiryDateInput = document.getElementById('expiry_date');
            const cvvInput = document.getElementById('cvv');
            
            // Format card number as user types
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 16) {
                    value = value.slice(0, 16);
                }
                e.target.value = value;
            });
            
            // Format expiry date as MM/YY
            expiryDateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 4) {
                    value = value.slice(0, 4);
                }
                if (value.length > 2) {
                    value = value.slice(0, 2) + '/' + value.slice(2);
                }
                e.target.value = value;
            });
            
            // Limit CVV to 3-4 digits
            cvvInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 4) {
                    value = value.slice(0, 4);
                }
                e.target.value = value;
            });
        });
    </script>
</body>
</html>