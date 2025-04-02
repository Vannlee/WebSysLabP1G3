<?php
    session_start();
    // echo "Session data: "; //debugging
    // print_r($_SESSION); //debugging
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Function to check if user is logged in
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    function getUserMembership() {
        if (isset($_SESSION['membership'])) {
            return $_SESSION['membership'];
        }
    
        // Fallback: fetch from DB if session variable isn't set
        if (isset($_SESSION['user_id'])) {
            global $conn; // or pass $conn as a parameter
            $membership = fetchUserMembershipFromDB($conn, $_SESSION['user_id']);
            if ($membership !== null) {
                $_SESSION['membership'] = $membership;
            }
            return $membership;
        }
    
        return null;
    }
    
    // Function to get user's current membership type
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
    
    // Handle membership selection
    if (isset($_GET['select']) && !isLoggedIn()) {
        // User clicked a membership but isn't logged in, store selection in session
        $_SESSION['selected_membership'] = $_GET['select'];
        // Redirect to login page
        header("Location: login.php");
        exit();
    } elseif (isset($_GET['select']) && isLoggedIn()) {
        // User is logged in and selected a new plan
        $newPlan = $_GET['select'];
        if (getUserMembership() != $newPlan) {
            // Store the new plan selection in session
            $_SESSION['selected_membership'] = $newPlan;
            // Redirect to payment page
            header("Location: payment.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Membership</title>
    <?php
        include "inc/head.inc.php";
        include "inc/enablejs.inc.php";
    ?>
</head>
<body>
    <?php
        include "inc/nav.inc.php";
    ?>
    
    <main class="container">
        <section class="container my-5">
            <h2 class="text-center mb-4">Choose Your Membership</h2>
            
            <div class="row justify-content-center g-4">
                
                <!-- Free Tier -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow <?php if(isLoggedIn() && getUserMembership() == 'basic') echo 'border-success'; ?>">
                        <?php if(isLoggedIn() && getUserMembership() == 'basic'): ?>
                            <div class="card-header bg-success text-white text-center">Current Plan</div>
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <h5 class="card-title">Basic</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Free</h6>
                            <p><i class="bi bi-check-circle-fill text-success"></i> 1 Hour Gym Access daily</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Free Locker</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Protein Shake</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Gym Item Discounts</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Personal Trainer</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> VIP Lounge</p>
                            <?php if(isLoggedIn() && getUserMembership() == 'basic'): ?>
                                <button class="btn btn-success" disabled>Current Plan</button>
                            <?php else: ?>
                                <a href="?select=basic" class="btn btn-outline-primary">Select</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Premium -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow <?php if(isLoggedIn() && getUserMembership() == 'Premium') echo 'border-success'; else echo 'border-primary'; ?>">
                        <?php if(isLoggedIn() && getUserMembership() == 'Premium'): ?>
                            <div class="card-header bg-success text-white text-center">Current Plan</div>
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <h5 class="card-title">Premium</h5>
                            <h6 class="card-subtitle mb-2 text-muted">$40/month</h6>
                            <p><i class="bi bi-check-circle-fill text-success"></i> 2 Hour Gym Access daily</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Free Locker</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Protein Shake</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Gym Item Discounts</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Personal Trainer</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> VIP Lounge</p>
                            <?php if(isLoggedIn() && getUserMembership() == 'Premium'): ?>
                                <button class="btn btn-success" disabled>Current Plan</button>
                            <?php else: ?>
                                <a href="?select=Premium" class="btn btn-primary">Select</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Ultimate -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow <?php if(isLoggedIn() && getUserMembership() == 'Ultimate') echo 'border-success'; ?>">
                        <?php if(isLoggedIn() && getUserMembership() == 'Ultimate'): ?>
                            <div class="card-header bg-success text-white text-center">Current Plan</div>
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <h5 class="card-title">Ultimate</h5>
                            <h6 class="card-subtitle mb-2 text-muted">$90/month</h6>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Unlimited Gym Access daily</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Free Locker</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Protein Shake</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Gym Item Discounts</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Personal Trainer</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> VIP Lounge</p>
                            <?php if(isLoggedIn() && getUserMembership() == 'Ultimate'): ?>
                                <button class="btn btn-success" disabled>Current Plan</button>
                            <?php else: ?>
                                <a href="?select=Ultimate" class="btn btn-outline-primary">Select</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </section>
    </main>
    
    <?php
        include "inc/footer.inc.php";
    ?>
</body>
</html>