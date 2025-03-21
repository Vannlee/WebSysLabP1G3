<?php
session_start();
//error_reporting(E_ALL);  turn on when need to troubleshoot
//ini_set('display_errors', 1);

error_reporting(0);
ini_set('display_errors', 0);



include "inc/head.inc.php";
include "inc/nav.inc.php";

$errorMsg = "";
$success = true;

// Email validation
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
}

// Password validation
if (empty($_POST["pwd"])) {
    $errorMsg .= "Password is required.<br>";
    $success = false;
} else {
    $pwd = $_POST["pwd"];
}

// Check if "Remember Me" is checked
$remember = isset($_POST["remember"]) ? true : false;

if ($success) {
    authenticateUser();
}

// Display login results
if ($success) {
    echo "<title>Login Successful</title>";
    echo "<main class='container'>";
    echo "<h3>Login successful!</h3>";
    echo "<h4>Welcome back, " . htmlspecialchars($fname) . " " . htmlspecialchars($lname) . ".</h4>";
    echo "<p><a class='btn btn-success' href='index.php'>Return to Home</a></p></main>";
} else {
    echo "<title>Login Failed</title>";
    echo "<main class='container'>";
    echo "<h3>Login Failed</h3>";
    echo "<h4>The following input errors were detected:</h4>";
    echo "<p>" . $errorMsg . "</p>";
    echo "<p><a class='btn btn-warning' href='login.php'>Return to Login</a></p></main>";
}

/**
 * Sanitize user input.
 */
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Authenticate user and implement "Remember Me" functionality.
 */
function authenticateUser() {
    global $fname, $lname, $email, $pwd, $errorMsg, $success, $remember;

    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        $errorMsg = "Database connection failed: " . $conn->connect_error;
        error_log("Debug: Connection failed - " . $conn->connect_error);
        $success = false;
        return;
    }

    $stmt = $conn->prepare("SELECT member_id, fname, lname, password FROM gymbros_members WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row["member_id"];
        $fname = $row["fname"];
        $lname = $row["lname"];
        $pwd_hashed = $row["password"];

        // Verify password
        if (password_verify($pwd, $pwd_hashed)) {
            $_SESSION["email"] = $email;
            $_SESSION["user_id"] = $user_id;

            // "Remember Me" functionality
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $token_hash = hash("sha256", $token);

                $stmt = $conn->prepare("INSERT INTO login_tokens (member_id, token_hash, expire_at) 
                        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY)) 
                        ON DUPLICATE KEY UPDATE token_hash=?, expire_at=DATE_ADD(NOW(), INTERVAL 30 DAY)");
                $stmt->bind_param("iss", $user_id, $token_hash, $token_hash);

                $stmt->execute();
                setcookie("remember_me", $token, time() + (86400 * 30), "/", "", true, true);
            }
        } else {
            $errorMsg = "Incorrect password.";
            $success = false;
        }
    } else {
        $errorMsg = "Email not found.";
        $success = false;
    }

    $stmt->close();
    $conn->close();
}

include "inc/footer.inc.php";
?>
