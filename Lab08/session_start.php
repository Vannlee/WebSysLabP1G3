<?php
session_start();
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If session doesn't exist, check for Remember Me cookie
if (!isset($_SESSION["email"]) && isset($_COOKIE["remember_me"])) {
    $token = $_COOKIE["remember_me"];
    $token_hash = hash("sha256", $token);

    $stmt = $conn->prepare("SELECT user_id FROM login_tokens WHERE token_hash=? AND expires_at > NOW()");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $stmt->bind_result($user_id);
    
    if ($stmt->fetch()) {
        // Auto-login user
        $_SESSION["user_id"] = $user_id;
        
        // Fetch user details
        $stmt->close();
        $stmt = $conn->prepare("SELECT email FROM world_of_pets_members WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($email);
        
        if ($stmt->fetch()) {
            $_SESSION["email"] = $email;
        }
    }
    $stmt->close();
}

$conn->close();
?>
