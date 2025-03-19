<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "inc/head.inc.php";
include "inc/nav.inc.php";

// Ensure user is logged in
if (!isset($_SESSION["email"])) {
    die("<p>Access denied. Please <a href='login.php'>log in</a> first.</p>");
}

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$email = $_SESSION["email"];

// Fetch user data
$stmt = $conn->prepare("SELECT id, email FROM gymbros_members WHERE email=?");
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
    <title>Update Credentials</title>
</head>
<body>
    <main class="container">
        <h1>Update Email & Password</h1>
        <form action="process_update_credentials.php" method="post">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">

            <div class="mb-3">
                <label for="email" class="form-label">New Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="current_pwd" class="form-label">Current Password:</label>
                <input type="password" id="current_pwd" name="current_pwd" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="new_pwd" class="form-label">New Password (leave blank if unchanged):</label>
                <input type="password" id="new_pwd" name="new_pwd" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update Credentials</button>
        </form>
    </main>
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>
