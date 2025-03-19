<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable error reporting for debugging



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
    // Ensure email is properly formatted
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

// Check if the user selected "Remember Me"
$remember = isset($_POST["remember"]) ? true : false;

if ($success) {
    authenticateUser();
}

if ($success) {
    echo "<title>Login Successful</title>";
    echo "<main class=\"container\">";
    echo "<h3>Login successful!</h3>";
    echo "<h4>Welcome back, " . $fname . " " . $lname . ".</h4>";
    echo "<p><a class=\"btn btn-success\" href=\"index.php\">Return to Home</a></p></main>";
} else {
    echo "<title>Login Failed</title>";
    echo "<main class=\"container\">";
    echo "<h3>Login Failed</h3>";
    echo "<h4>The following input errors were detected:</h4>";
    echo "<p>" . $errorMsg . "</p>";
    echo "<p><a class=\"btn btn-warning\" href=\"login.php\">Return to Login</a></p></main>";
}

/**
 * Helper function to sanitize user input.
 */
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Authenticate user credentials and implement Remember Me feature.
 */
function authenticateUser() {
    global $fname, $lname, $email, $pwd, $errorMsg, $success, $remember;

    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
    } else {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        } else {
            // Prepare the statement:
            $stmt = $conn->prepare("SELECT id, fname, lname, password FROM gymbros_members WHERE email=?");

            
            // Bind & execute the query statement:
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Fetch user details
                $row = $result->fetch_assoc();
                $user_id = $row["id"];
                $fname = $row["fname"];
                $lname = $row["lname"];
                $pwd_hashed = $row["password"];

                // Check if the password matches:
                if (password_verify($pwd, $pwd_hashed)) {
                    $_SESSION["email"] = $email;
                    $_SESSION["user_id"] = $user_id;

                    //  Implement Remember Me Feature
                    if ($remember) {
                        $token = bin2hex(random_bytes(32)); // Generate secure token
                        $token_hash = hash("sha256", $token); // Hash before storing
                    
                        // Debugging: Check values before inserting
                        error_log("Debug: Storing remember me token for user_id: " . $user_id);
                    
                        // Store token in database with error handling
                        $stmt = $conn->prepare("INSERT INTO login_tokens (user_id, token_hash, expires_at) 
                                                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY)) 
                                                ON DUPLICATE KEY UPDATE token_hash=?, expires_at=DATE_ADD(NOW(), INTERVAL 30 DAY)");
                    
                        if (!$stmt) {
                            error_log("Debug: Prepare failed - " . $conn->error);
                        }
                    
                        $stmt->bind_param("iss", $user_id, $token_hash, $token_hash);
                    
                        if (!$stmt->execute()) {
                            error_log("Debug: Execute failed - " . $stmt->error);
                        }
                    
                        $stmt->close();
                    
                        // Set HTTP-only, secure cookie
                        setcookie("remember_me", $token, time() + (86400 * 30), "/", "", true, true);
                    }
                    
                } else {
                    $errorMsg = "Email not found or password doesn't match.";
                    $success = false;
                }
            } else {
                $errorMsg = "Email not found or password doesn't match.";
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
}

include "inc/footer.inc.php";
?>
