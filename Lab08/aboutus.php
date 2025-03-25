<?php
    session_start(); // Make sure this is the FIRST thing in the file
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Fitness Gym</title>
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
            <div>
                <h1 style="text-align:center;">About Us</h1>
                <p>
                    Fitness Gym is a gym that is dedicated to helping you achieve your fitness goals. We have a wide range of classes and instructors to help you get started on your fitness journey. Our gym is equipped with the latest equipment and technology to help you track your progress and stay motivated. Whether you are a beginner or an experienced athlete, we have something for everyone. Come join us and start your fitness journey today!
                </p>
                <p>
                    <img src="images/this_is_fine.gif" alt="Fitness Gym" title="Fitness Gym" height = "300" width="500"/>
            </div>
        </main>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
</html>