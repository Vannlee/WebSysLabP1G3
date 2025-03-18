session_start();
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if (!isset($_SESSION["email"]) && isset($_COOKIE["remember_me"])) {
    $token = $_COOKIE["remember_me"];
    $token_hash = hash("sha256", $token);

    $stmt = $conn->prepare("SELECT user_id FROM login_tokens WHERE token_hash=? AND expires_at > NOW()");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $stmt->bind_result($user_id);

    if ($stmt->fetch()) {
        $_SESSION["user_id"] = $user_id;
        
        // Fetch user email
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
