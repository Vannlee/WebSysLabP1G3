<?php
    include "inc/head.inc.php";
    include "inc/nav.inc.php";

    $errorMsg = "";
    $success = true;

    // first name vaidation
    if (!empty($_POST["fname"])) {
        $fname = sanitize_input($_POST["fname"]);
    }
    else {
        $fname = "";
    }

    // last name validation
    if (empty($_POST["lname"])) {
        $errorMsg .= "Last name is required.<br>";
        $success = false;
    }
    else {
        $lname = sanitize_input($_POST["lname"]);
        // Check if last name contains only letters
        if (!preg_match("/^[A-Za-z]+$/", $lname)) {
            $errorMsg .= "Last name must contain only letters.<br>";
            $success = false;
        }

    }

    // email validation
    if (empty($_POST["email"])) {
        $errorMsg .= "Email is required.<br>";
        $success = false;
    }
    else {
        $email = sanitize_input($_POST["email"]);
        // Additional check to make sure e-mail address is well-formed.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg .= "Invalid email format.<br>";
            $success = false;
        }
    }

    // password validation
    if (empty($_POST["pwd"])) {
        $errorMsg .= "Password is required.<br>";
        $success = false;
    }
    else {
        $pwd = $_POST["pwd"];
        $pwd_hashed = password_hash($pwd, PASSWORD_DEFAULT);
    }

    // password validation
    if (empty($_POST["pwd_confirm"])) {
        $errorMsg .= "Confirm Password is required.<br>";
        $success = false;
    }
    else {
        $pwd_confirm = $_POST["pwd_confirm"];
        if ($pwd != $pwd_confirm) {
            $errorMsg .= "Passwords do not match.<br>";
            $success = false;
        }
    }
    // Check if the user agreed to the terms
    if (empty($_POST["agree"])) {
    $errorMsg .= "You must agree to the terms and conditions.<br>";
    $success = false;
    }

    if ($success) {
        saveMemberToDB();
        echo "<title>Registration Successful</title>";
        echo "<main class=\"container\">";
        echo "<h3>Registration successful!</h3>";
        echo "<h4>Thank you for signing up, " . $fname . " " . $lname . ".</h4>";
        echo "<p><a class=\"btn btn-success\" href=\"login.php\">Log-in</button></a></p></main>";
    }
    else {
        echo "<title>Registration Unsuccessful</title>";
        echo "<main class=\"container\">";
        echo "<h3>Bruh</h3>";
        echo "<h4>The following input errors were detected:</h4>";
        echo "<p>" . $errorMsg . "</p>";
        echo "<p><a class=\"btn btn-danger\" href=\"register.php\">Back to Sign Up</button></a></p></main>";
    }

    /*
    * Helper function that checks input for malicious or unwanted content.
    */
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /*
    * Helper function to write the member data to the database.
    */
    function saveMemberToDB() {
        global $fname, $lname, $email, $pwd_hashed, $errorMsg, $success;
    
        // Create database connection.
        $config = parse_ini_file('/var/www/private/db-config.ini');
        
        if (!$config) {
            $errorMsg = "Failed to read database config file.";
            $success = false;
        }
        else {
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
        }
        else {
            // Prepare the statement:
            $stmt = $conn->prepare("INSERT INTO world_of_pets_members
            (fname, lname, email, password) VALUES (?, ?, ?, ?)");

            // Bind & execute the query statement:
            $stmt->bind_param("ssss", $fname, $lname, $email, $pwd_hashed);
            if (!$stmt->execute()) {
                $errorMsg = "Execute failed: (" . $stmt->errno . ") " .
                $stmt->error;
                $success = false;
            }
            $stmt->close();
        }

        $conn->close();
        }
    }

    include "inc/footer.inc.php";
?>