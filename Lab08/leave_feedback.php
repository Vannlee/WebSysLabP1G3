<?php
    session_start();

    if (!isset($_SESSION["email"])) {
        header("Location: login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Leave a Feedback - Gymbros</title>
        <?php
            include "inc/head.inc.php";
            include "inc/enablejs.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
        ?>
        <main class="container">
            <form action="process_feedback.php" method="post">
                <div class="alert alert-light mt-4">
                    <h1>So Sorry to Lose You</h1>
                    <label for="feedback_content" class="form-label">We would like to know more on what made you change your mind:</label>
                    <textarea class="form-control" id="feedback_content" name="feedback_content" rows="5" required></textarea>
                </div>
                <button type="submit" name="process_feedback" class="btn btn-primary" style="float: right;">Submit</button>
            </form>
            
        </main>
        <?php
        include "inc/footer.inc.php";
        ?>
    </body>