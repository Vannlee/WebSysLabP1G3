<?php
session_start();
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Profile</title>
</head>
<body>
<main class="container">
    <h1>Update Profile</h1>
    <form action="process_profile.php" method="post">
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($user['member_id']); ?>">
        <input type="hidden" name="action_type" value="update">

        <div class="mb-3">
            <label for="fname" class="form-label">First Name:</label>
            <input type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($user['fname']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="lname" class="form-label">Last Name:</label>
            <input type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($user['lname']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">New Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="contact" class="form-label">Contact Number:</label>
            <input type="text" id="contact" name="contact" class="form-control" value="<?php echo htmlspecialchars($user['contact']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="datejoin" class="form-label">Date Joined:</label>
            <input type="text" id="datejoin" class="form-control" value="<?php echo htmlspecialchars($user['datejoin']); ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="membership" class="form-label">Membership Type:</label>
            <input type="text" id="membership" class="form-control" value="<?php echo htmlspecialchars($user['membership']); ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="current_pwd" class="form-label">Current Password (required):</label>
            <input type="password" id="current_pwd" name="current_pwd" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="new_pwd" class="form-label">New Password (leave blank to keep existing):</label>
            <input type="password" id="new_pwd" name="new_pwd" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>

    <hr>
    <h3>Danger Zone</h3>
    <form action="process_profile.php" method="post" onsubmit="return confirm('Are you sure you want to delete your profile?');">
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($user['member_id']); ?>">
        <input type="hidden" name="action_type" value="delete">

        <div class="mb-3">
            <label for="current_pwd_del" class="form-label">Password to Confirm Deletion:</label>
            <input type="password" id="current_pwd_del" name="current_pwd" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-danger">Delete Profile</button>
    </form>
</main>
<?php include "inc/footer.inc.php"; ?>
</body>
</html>
