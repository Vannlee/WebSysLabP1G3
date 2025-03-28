<?php
session_start();
ob_start(); // Prevents "headers already sent" errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

// Ensure user is logged in
if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$email = $_SESSION["email"];

// Fetch user data
$stmt = $conn->prepare("SELECT member_id, fname, lname, email FROM gymbros_members WHERE email=?");
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
    <title>Update or Delete Profile</title>
</head>
<body>
<main class="container">
    <h1>Manage Your Profile</h1>
    <form action="process_profile.php" method="post">
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($user['member_id']); ?>">

        <div class="mb-3">
            <label for="action_type" class="form-label">What do you want to do?</label>
            <select name="action_type" id="action_type" class="form-control" required>
                <option value="update" selected>Update Profile</option>
                <option value="delete">Delete Profile</option>
            </select>
        </div>

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
            <label for="current_pwd" class="form-label">Current Password (required for all actions):</label>
            <input type="password" id="current_pwd" name="current_pwd" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="new_pwd" class="form-label">New Password (only if updating):</label>
            <input type="password" id="new_pwd" name="new_pwd" class="form-control">
        </div>

        <button type="submit" class="btn btn-danger">Submit</button>
    </form>
</main>
<?php include "inc/footer.inc.php"; ?>
</body>
</html>
