<?php
session_start();
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_SESSION["email"];

$stmt = $conn->prepare("SELECT member_id, fname, lname, email, contact, datejoin, membership FROM gymbros_members WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get membership details for display
$memberships = [
    'basic' => ['name' => 'Basic', 'color' => 'secondary'],
    'Premium' => ['name' => 'Premium', 'color' => 'primary'],
    'Ultimate' => ['name' => 'Ultimate', 'color' => 'success']
];

$membershipColor = isset($memberships[$user['membership']]) ? $memberships[$user['membership']]['color'] : 'secondary';
$membershipName = isset($memberships[$user['membership']]) ? $memberships[$user['membership']]['name'] : $user['membership'];

// Format date
$joinDate = new DateTime($user['datejoin']);
$formattedDate = $joinDate->format('F j, Y');

$transaction_stmt = $conn->prepare("SELECT payment_id, created_at, price, card_number, member_id FROM payment_methods WHERE member_id = ?");
$transaction_stmt->bind_param("i", $user['member_id']);
$transaction_stmt->execute();
$transactions_result = $transaction_stmt->get_result();
$transactions = [];
while ($row = $transactions_result->fetch_assoc()) {
    // No need to mask the card - it's already stored in masked format
    $row['masked_card'] = $row['card_number'];
    
    // Add membership type based on price
    if ($row['price'] == 40) {
        $row['membership_type'] = 'Premium';
    } elseif ($row['price'] == 90) {
        $row['membership_type'] = 'Ultimate';
    } else {
        $row['membership_type'] = 'Basic';
    }
    
    $transactions[] = $row;
}
$transaction_stmt->close();
$conn->close();

// Helper function to determine membership name based on price
function getMembershipName($price) {
    if ($price == 40) {
        return 'Premium';
    } elseif ($price == 90) {
        return 'Ultimate';
    } else {
        return 'Basic';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile - Gymbros</title>
    <?php
        include "inc/head.inc.php";
        include "inc/enablejs.inc.php";
    ?>
    <link rel="stylesheet" href="css/profile.css">
    <script src="js/profile-validation.js"></script>

</head>
<body>
    <?php 
        include "inc/nav.inc.php";
    ?>
    
    <main class="container py-5">
        <div class="profile-header d-flex align-items-center">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['fname'], 0, 1) . substr($user['lname'], 0, 1)); ?>
            </div>
            <div>
                <h1 class="mb-0"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></h1>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="badge bg-<?php echo $membershipColor; ?>"><?php echo htmlspecialchars($membershipName); ?> Member</span>
                <span class="badge bg-light text-dark">Member since <?php echo $formattedDate; ?></span>
            </div>
        </div>

        <?php if (isset($_SESSION['profile_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['profile_error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['profile_error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['profile_updated']) && $_SESSION['profile_updated']): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Your profile has been updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['profile_updated']); ?>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">
                    <i class="bi bi-person-fill"></i> Profile
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                    <i class="bi bi-shield-lock-fill"></i> Security
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="membership-tab" data-bs-toggle="tab" data-bs-target="#membership" type="button" role="tab" aria-controls="membership" aria-selected="false">
                    <i class="bi bi-star-fill"></i> Membership
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button" role="tab" aria-controls="transactions" aria-selected="false">
                    <i class="bi bi-credit-card"></i> Transactions
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="profileTabsContent">
            <!-- Profile Information Tab -->
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <form id="profile-form" action="process_profile.php" method="post">
                    <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($user['member_id']); ?>">
                    <input type="hidden" name="action_type" value="update_profile">
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="fname" class="form-label">First Name</label>
                            <input type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($user['fname']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($user['lname']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input type="text" id="contact" name="contact" class="form-control" value="<?php echo htmlspecialchars($user['contact']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_pwd" class="form-label">Current Password (required to save changes)</label>
                        <input type="password" id="current_pwd" name="current_pwd" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
            
            <!-- Security Tab -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <form id="security-form" action="process_profile.php" method="post">
                    <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($user['member_id']); ?>">
                    <input type="hidden" name="action_type" value="update_password">
                    
                    <!-- Hidden field to ensure email is never null -->
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

                    <div class="mb-3">
                        <label for="current_pwd_security" class="form-label">Current Password</label>
                        <input type="password" id="current_pwd_security" name="current_pwd" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_pwd" class="form-label">New Password</label>
                        <input type="password" id="new_pwd" name="new_pwd" class="form-control" required>
                        <div class="form-text">Password must be at least 8 characters long and include a combination of letters, numbers, and special characters.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_pwd" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_pwd" name="confirm_pwd" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
                
                <div class="danger-zone mt-5">
                    <h2 class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Danger Zone</h2>
                    <p>Once you delete your account, there is no going back. Please be certain.</p>
                    
                    <form id="delete-form" action="process_profile.php" method="post" onsubmit="return confirm('Are you sure you want to delete your profile? This action cannot be undone.');">
                        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($user['member_id']); ?>">
                        <input type="hidden" name="action_type" value="delete">
                        
                        <div class="mb-3">
                            <label for="current_pwd_del" class="form-label">Password to Confirm Deletion</label>
                            <input type="password" id="current_pwd_del" name="current_pwd" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">Delete My Account</button>
                    </form>
                </div>
            </div>
            
            <!-- Membership Tab -->
            <div class="tab-pane fade" id="membership" role="tabpanel" aria-labelledby="membership-tab">
                <div class="d-flex align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Current Plan: <span class="text-<?php echo $membershipColor; ?>"><?php echo htmlspecialchars($membershipName); ?></span></h2>
                        <p class="text-muted mb-0">Member since <?php echo $formattedDate; ?></p>
                    </div>
                    <a href="membership.php" class="btn btn-primary ms-auto">Upgrade Membership</a>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="mb-0">Membership Benefits</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($user['membership'] == 'Basic'): ?>
                            <p><i class="bi bi-check-circle-fill text-success"></i> 1 Hour Gym Access daily</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Free Locker</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Protein Shake</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Gym Item Discounts</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Personal Trainer</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> VIP Lounge</p>
                        <?php elseif ($user['membership'] == 'Premium'): ?>
                            <p><i class="bi bi-check-circle-fill text-success"></i> 2 Hour Gym Access daily</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Free Locker</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Protein Shake</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Gym Item Discounts</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> Personal Trainer</p>
                            <p><i class="bi bi-x-circle-fill text-danger"></i> VIP Lounge</p>
                        <?php elseif ($user['membership'] == 'Ultimate'): ?>
                            <p><i class="bi bi-check-circle-fill text-success"></i> 2 Hour Gym Access daily</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Free Locker</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Protein Shake</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Gym Item Discounts</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> Personal Trainer</p>
                            <p><i class="bi bi-check-circle-fill text-success"></i> VIP Lounge</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i> Need to change your plan? Visit our <a href="membership.php" class="alert-link">membership page</a> to upgrade or downgrade your membership.
                </div>
            </div>
            
            <!-- Transactions Tab -->
            <div class="tab-pane fade" id="transactions" role="tabpanel" aria-labelledby="transactions-tab">
                <h2 class="mb-4">Purchase History</h2>
                
                <?php if (count($transactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Date</th>
                                    <th>Membership Type</th>
                                    <th>Credit Card</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaction['payment_id']); ?></td>
                                        <td>
                                            <?php 
                                            $date = new DateTime($transaction['created_at']);
                                            echo $date->format('M j, Y g:i A'); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if (isset($transaction['membership_type'])) {
                                                echo htmlspecialchars($transaction['membership_type']);
                                            } else {
                                                echo htmlspecialchars(getMembershipName($transaction['price']));
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($transaction['masked_card']); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($transaction['price'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill"></i> No transaction history found.
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-secondary mt-4">
                <i class="bi bi-question-circle-fill"></i> Need help with a transaction? Please <a href="mailto:scott.jones@singaporetech.edu.sg" class="alert-link">contact helpdesk</a>.
                </div>
            </div>
        </div>
    </main>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script>
        // Enable Bootstrap tabs
        document.addEventListener('DOMContentLoaded', function() {
            var triggerTabList = [].slice.call(document.querySelectorAll('#profileTabs button'))
            triggerTabList.forEach(function(triggerEl) {
                var tabTrigger = new bootstrap.Tab(triggerEl)
                triggerEl.addEventListener('click', function(event) {
                    event.preventDefault()
                    tabTrigger.show()
                })
            })
            
            // Password confirmation validation
            const newPasswordInput = document.getElementById('new_pwd');
            const confirmPasswordInput = document.getElementById('confirm_pwd');
            
            confirmPasswordInput.addEventListener('input', function() {
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity("Passwords don't match");
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });
        });
    </script>
</body>
</html>