<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data ?? "")));
}

if (!isset($_SESSION["email"])) {
    die("Access denied. Please <a href='login.php'>log in</a>.");
}

$member_id   = $_POST["member_id"]   ?? null;
$action_type = $_POST["action_type"] ?? null;

$errorMsg = "";
$success  = true;

// Database connection
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ----------------- DELETE PROFILE -------------------
if ($action_type === "delete") {
    $current_pwd = $_POST["current_pwd"] ?? '';
    
    $stmt = $conn->prepare("SELECT password FROM gymbros_members WHERE member_id=?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user || !password_verify($current_pwd, $user["password"])) {
        $success = false;
        $errorMsg = "Incorrect password.";
    } else {
        // Delete user
        $stmt = $conn->prepare("DELETE FROM gymbros_members WHERE member_id=?");
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $stmt->close();
        session_destroy();
    }

// ----------------- UPDATE PROFILE (fname/lname/contact/email) -------------------
} elseif ($action_type === "update_profile") {
    $fname       = sanitize_input($_POST["fname"]  ?? "");
    $lname       = sanitize_input($_POST["lname"]  ?? "");
    $contact     = sanitize_input($_POST["contact"]?? "");
    $current_pwd = $_POST["current_pwd"] ?? "";
    $email       = sanitize_input($_POST["email"]  ?? "");

    // Fallback if email is empty
    if (empty($email)) {
        $email = $_SESSION["email"];
    }

    // Verify password
    $stmt = $conn->prepare("SELECT password FROM gymbros_members WHERE member_id=?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($current_pwd, $user["password"])) {
        $errorMsg .= "Incorrect current password.<br>";
        $success = false;
    }

    // If everything is OK, update
    if ($success) {
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, email=?, contact=? WHERE member_id=?");
        $stmt->bind_param("ssssi", $fname, $lname, $email, $contact, $member_id);

        if (!$stmt->execute()) {
            $errorMsg .= "Error updating profile: " . $stmt->error;
            $success = false;
        } else {
            // Update session email
            $_SESSION["email"] = $email;
        }
        $stmt->close();
    }

// ----------------- UPDATE PASSWORD ONLY -------------------
} elseif ($action_type === "update_password") {
    $current_pwd = $_POST["current_pwd"] ?? "";
    $new_pwd     = $_POST["new_pwd"]     ?? "";
    $confirm_pwd = $_POST["confirm_pwd"] ?? "";
    $email       = sanitize_input($_POST["email"]  ?? "");

    // Fallback if email is empty
    if (empty($email)) {
        $email = $_SESSION["email"];
    }

    if (empty($new_pwd) || empty($confirm_pwd)) {
        $errorMsg .= "New password fields cannot be empty.<br>";
        $success = false;
    } elseif ($new_pwd !== $confirm_pwd) {
        $errorMsg .= "New passwords do not match.<br>";
        $success = false;
    }

    // Verify user password from DB
    $stmt = $conn->prepare("SELECT password FROM gymbros_members WHERE member_id=?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($current_pwd, $user["password"])) {
        $errorMsg .= "Incorrect current password.<br>";
        $success = false;
    }

    // If OK, update password
    if ($success) {
        $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE gymbros_members SET password=?, email=? WHERE member_id=?");
        $stmt->bind_param("ssi", $hashed_pwd, $email, $member_id);

        if (!$stmt->execute()) {
            $errorMsg .= "Error updating password: " . $stmt->error;
            $success = false;
        }
        $stmt->close();
    }
}

$conn->close();

// ----------------- OUTPUT RESULT -------------------
if ($success) {
    echo "<title>Success</title><main class='container'><h3>Action completed successfully.</h3><a href='profile.php' class='btn btn-success'>Return to Profile</a></main>";
} else {
    echo "<title>Error</title><main class='container'><h3>Action failed.</h3><p>$errorMsg</p><a href='profile.php' class='btn btn-warning'>Try Again</a></main>";
}
?>
