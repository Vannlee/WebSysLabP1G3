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
$conn->close();

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile | GymBros</title>
    <?php
        include "inc/head.inc.php";
        include "inc/enablejs.inc.php";
    ?>
    <style>
        .profile-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #6c757d;
            margin-right: 20px;
        }
        
        .tab-content {
            padding: 30px;
            background-color: #fff;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-tabs .nav-link {
            border-radius: 10px 10px 0 0;
        }
        
        .danger-zone {
            background-color: #fff5f5;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid #ffe5e5;
        }
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>
    
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
        </ul>
        
        <div class="tab-content" id="profileTabsContent">
            <!-- Profile Information Tab -->
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <form action="process_profile.php" method="post">
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
                <form action="process_profile.php" method="post">
                    <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($user['member_id']); ?>">
                    <input type="hidden" name="action_type" value="update_password">
                    
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
                    <h4 class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Danger Zone</h4>
                    <p>Once you delete your account, there is no going back. Please be certain.</p>
                    
                    <form action="process_profile.php" method="post" onsubmit="return confirm('Are you sure you want to delete your profile? This action cannot be undone.');">
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
                        <h4 class="mb-1">Current Plan: <span class="text-<?php echo $membershipColor; ?>"><?php echo htmlspecialchars($membershipName); ?></span></h4>
                        <p class="text-muted mb-0">Member since <?php echo $formattedDate; ?></p>
                    </div>
                    <a href="membership.php" class="btn btn-primary ms-auto">Upgrade Membership</a>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Membership Benefits</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($user['membership'] == 'basic'): ?>
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