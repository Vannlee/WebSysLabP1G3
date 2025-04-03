<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data ?? "")));
}

// Enhanced validation functions
function validate_name($name) {
    return preg_match('/^[a-zA-Z ]+$/', $name);
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_contact($contact) {
    return preg_match('/^\d{8}$/', $contact);
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

// 1) DELETE PROFILE
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
        $stmt = $conn->prepare("DELETE FROM gymbros_members WHERE member_id=?");
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $stmt->close();
        session_destroy();
    }

// 2) UPDATE PROFILE (fname/lname/contact/email)
} elseif ($action_type === "update_profile") {
    $fname       = sanitize_input($_POST["fname"]  ?? "");
    $lname       = sanitize_input($_POST["lname"]  ?? "");
    $contact     = sanitize_input($_POST["contact"]?? "");
    $current_pwd = $_POST["current_pwd"] ?? "";
    $email       = sanitize_input($_POST["email"]  ?? "");

    // Validation - First Name
    if (empty($fname) || !validate_name($fname)) {
        $errorMsg .= "First name should only contain letters and spaces.<br>";
        $success = false;
    }

    // Validation - Last Name
    if (empty($lname) || !validate_name($lname)) {
        $errorMsg .= "Last name should only contain letters and spaces.<br>";
        $success = false;
    }

    // Validation - Email
    if (empty($email) || !validate_email($email)) {
        $errorMsg .= "Please provide a valid email address.<br>";
        $success = false;
    } else {
        // Check if email is already in use by another member
        $stmt = $conn->prepare("SELECT member_id FROM gymbros_members WHERE email=? AND member_id!=?");
        $stmt->bind_param("si", $email, $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errorMsg .= "Email address is already in use by another account.<br>";
            $success = false;
        }
        $stmt->close();
    }

    // Validation - Contact
    if (empty($contact) || !validate_contact($contact)) {
        $errorMsg .= "Contact number must be exactly 8 digits.<br>";
        $success = false;
    }

    // Fallback if email is empty
    if (empty($email)) {
        $email = $_SESSION["email"];
    }

    // Verify current password
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

    if ($success) {
        $stmt = $conn->prepare("UPDATE gymbros_members SET fname=?, lname=?, email=?, contact=? WHERE member_id=?");
        $stmt->bind_param("ssssi", $fname, $lname, $email, $contact, $member_id);

        if (!$stmt->execute()) {
            $errorMsg .= "Error updating profile: " . $stmt->error;
            $success = false;
        } else {
            // Update session email
            $_SESSION["email"] = $email;
            $_SESSION['profile_updated'] = true;
        }
        $stmt->close();
    }

// 3) UPDATE PASSWORD ONLY
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
    } elseif (strlen($new_pwd) < 8) {
        $errorMsg .= "Password must be at least 8 characters long.<br>";
        $success = false;
    } elseif (!preg_match('/[a-zA-Z]/', $new_pwd) || !preg_match('/\d/', $new_pwd) || !preg_match('/[^a-zA-Z\d]/', $new_pwd)) {
        $errorMsg .= "Password must include a combination of letters, numbers, and special characters.<br>";
        $success = false;
    }

    // Verify current password
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

    if ($success) {
        $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE gymbros_members SET password=?, email=? WHERE member_id=?");
        $stmt->bind_param("ssi", $hashed_pwd, $email, $member_id);

        if (!$stmt->execute()) {
            $errorMsg .= "Error updating password: " . $stmt->error;
            $success = false;
        } else {
            $_SESSION['profile_updated'] = true;
        }
        $stmt->close();
    }
}

$conn->close();

// Redirect instead of showing HTML
if ($success) {
    if ($action_type === "delete") {
        header("Location: login.php?logout=1&deleted=1");
        exit();
    } else {
        header("Location: profile.php");
        exit();
    }
} else {
    // Store error in session and redirect back to profile
    $_SESSION['profile_error'] = $errorMsg;
    header("Location: profile.php");
    exit();
}
?>