<?php
session_start();
session_unset();
session_destroy();

// Remove Remember Me Cookie
setcookie("remember_me", "", time() - 3600, "/", "", true, true);

// Remove token from database
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_COOKIE["remember_me"])) {
    $token_hash = hash("sha256", $_COOKIE["remember_me"]);
    $stmt = $conn->prepare("DELETE FROM login_tokens WHERE token_hash=?");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Location: login.php");
exit();
?>
