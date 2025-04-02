<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Instructors - Gymbros</title>
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
            <section class="container my-5" id="instructors">
            <h1 class="display-4">Meet Our Instructors</h1>
                <div class="row align-items-center bg-light">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h2>Michael Chen</h2>
                        <p>Energetic, disciplined and always pushing people to their limits, Michael believes in tough love when it comes to training. He's the kind of instructor who won't accept excuses but will always be there to support his clients when they struggle.</p>
                    </div>
                    <div class="col-lg-6">
                        <img src="images/instr_mc.jpg" alt="Instructor Michael Chen" class="img-fluid rounded shadow">
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <img src="images/instr_sm.jpg" alt="Instructor Sarah Martinez" class="img-fluid rounded shadow">
                    </div>
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h2>Sarah Martinez</h2>
                        <p>Sarah is the perfect mix of motivational and no-nonsense instructor. She pushes her clients hard but also knows when to dial it back and provide encouragement. She is known for her infectious enthusiasm and ability to make even the most intense workouts feel fun.</p>
                    </div>
                </div>
                <div class="row align-items-center bg-light">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h2>Jeffrey "Mack" Johnson</h2>
                        <p>Tough but fair, Mack believes in discipline and consistency over quick fixes. His no-excuses attitude is balanced by his genuine care for his clients — he will push you past your limits, but he will also be the first to check in on you afterward.</p>
                    </div>
                    <div class="col-lg-6">
                        <img src="images/instr_jj.jpg" alt="Instructor Mack" class="img-fluid rounded shadow">
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <img src="images/instr_ar.jpg" alt="Instructor Alyssa Reed" class="img-fluid rounded shadow">
                    </div>
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h2>Alyssa Reed</h2>
                        <p>Alyssa is the perfect mix of tough and nurturing. She is a firm believer in pushing limits but she also understands that fitness is just as much about mindset as it is about movement. She is passionate about helping people build confidence and known for her uplifting energy and ability to make people believe in themselves.</p>
                    </div>
                </div>
            </section>
        </main>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
</html>