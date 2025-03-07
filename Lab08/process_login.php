<?php
    include "inc/head.inc.php";
    include "inc/nav.inc.php";

    $errorMsg = "";
    $success = true;

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
        $pwd = password_hash($pwd, PASSWORD_DEFAULT);
    }

    if ($success) {
        authenticateUser();
    }

    if ($success) {
        echo "<title>Login Successful</title>";
        echo "<main class=\"container\">";
        echo "<h3>Login successful!</h3>";
        echo "<h4>Welcome back, " . $fname . " " . $lname . ".</h4>";
        echo "<p><a class=\"btn btn-success\" href=\"index.php\">Return to Home</button></a></p></main>";
    }
    else {
        echo "<title>Login Failed</title>";
        echo "<main class=\"container\">";
        echo "<h3>Bruh</h3>";
        echo "<h4>The following input errors were detected:</h4>";
        echo "<p>" . $errorMsg . "</p>";
        echo "<p><a class=\"btn btn-warning\" href=\"login.php\">Return to Login</button></a></p></main>";
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

    function authenticateUser() {
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
                $stmt = $conn->prepare("SELECT * FROM world_of_pets_members WHERE email=?");
                
                // Bind & execute the query statement:
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Note that email field is unique, so should only have one row.
                    $row = $result->fetch_assoc();
                    $fname = $row["fname"];
                    $lname = $row["lname"];
                    $pwd_hashed = $row["password"];

                    // Check if the password matches:
                    if (!password_verify($_POST["pwd"], $pwd_hashed)) {
                        // Don’t tell hackers which one was wrong, keep them guessing…
                        $errorMsg = "Email not found or password doesn't match...";
                        $success = false;
                    }
                }
                else {
                    $errorMsg = "Email not found or password doesn't match...";
                    $success = false;
                }
                $stmt->close();
            }
            $conn->close();
        }
    }

    include "inc/footer.inc.php";
?>