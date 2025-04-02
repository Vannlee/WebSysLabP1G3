<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>About Us - Gymbros</title>
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
            <h1 class="display-4">About Gymbros</h1>
            <p class="lead">Your partner in health and fitness since 2010</p>
            
            <!-- Our Story Section -->
            <section class="container my-5" id="ourstory">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h2>Our Story</h2>
                        <p>Founded in 2010, Gymbros began with a simple mission: to create a welcoming environment where people of all fitness levels could achieve their health goals.</p>
                        <p> Our Motto: This is Fine, emphasises our dedication to developing people through fitness such that they can remain cool and perservere through all forms of hardship.</p>
                        <p>What started as a small local gym has grown into a community of fitness enthusiasts supporting each other on their wellness journeys. Our passion for health and commitment to excellence drives everything we do.</p>
                        <p>Today, we're proud to offer state-of-the-art equipment, expert personal trainers, and a variety of membership options to meet your unique needs.</p>
                    </div>
                    <div class="col-lg-6">
                        <img src="images/this_is_fine.gif" alt="Our Motto" class="img-fluid rounded shadow">
                    </div>
                </div>
            </section>
            
            <!-- Our Mission Section -->
            <section class="container my-5 bg-light p-5 rounded" id="ourmission">
                <div class="text-center mb-4">
                    <h2>Our Mission</h2>
                    <p class="lead">To empower individuals to transform their lives through fitness in a supportive, inclusive environment.</p>
                </div>
                    <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-heart-fill text-danger fs-1 mb-3"></i>
                                <h5 class="card-title">Health First</h5>
                                <p class="card-text">We prioritize your wellbeing above all else, focusing on safe, sustainable fitness practices.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-people-fill text-primary fs-1 mb-3"></i>
                                <h5 class="card-title">Community</h5>
                                <p class="card-text">We foster a supportive community where members motivate each other to reach their goals.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-trophy-fill text-warning fs-1 mb-3"></i>
                                <h5 class="card-title">Excellence</h5>
                                <p class="card-text">We continuously improve our facilities, programs, and services to exceed expectations.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Meet Our Team Section -->
            <section class="container my-5" id="ourteam">
                <h2 class="text-center mb-4">Meet Our Team</h2>
                <div class="row g-4 justify-content-center">
                    <div class="col-md-6 col-lg-2">
                        <div class="card h-100 shadow-sm">
                            <img src="images/john.png" class="card-img-top" alt="Team member">
                            <div class="card-body text-center">
                                <h5 class="card-title">Ong Eugene</h5>
                                <p class="card-subtitle text-muted mb-3">Founder & CEO</p>
                                <p class="card-text">Certified fitness professional with 15+ years of experience in the industry.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="card h-100 shadow-sm">
                            <img src="images/john.png" class="card-img-top" alt="Team member">
                            <div class="card-body text-center">
                                <h5 class="card-title">Ethan Tan E-Xin</h5>
                                <p class="card-subtitle text-muted mb-3">Head Trainer</p>
                                <p class="card-text">Specializes in strength training and nutritional coaching to maximize results.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="card h-100 shadow-sm">
                            <img src="images/jovan.jpg" class="card-img-top" alt="Team member">
                            <div class="card-body text-center">
                                <h5 class="card-title">Jovan Lee Songying</h5>
                                <p class="card-subtitle text-muted mb-3">Developer</p>
                                <p class="card-text">"Sticks and Stones Break my Bones"</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="card h-100 shadow-sm">
                            <img src="images/john.png" class="card-img-top" alt="Team member">
                            <div class="card-body text-center">
                                <h5 class="card-title">Ng Wei Qi</h5>
                                <p class="card-subtitle text-muted mb-3">Wellness Coach</p>
                                <p class="card-text">Focuses on holistic approaches to fitness, including yoga and meditation.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="card h-100 shadow-sm">
                            <img src="images/john.png" class="card-img-top" alt="Team member">
                            <div class="card-body text-center">
                                <h5 class="card-title">Tan Zi Xu</h5>
                                <p class="card-subtitle text-muted mb-3">Fitness Instructor</p>
                                <p class="card-text">Expert in high-intensity interval training and group fitness classes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Facilities Section -->
            <section class="container my-5 bg-light p-5 rounded" id="facilities">
                <h2 class="text-center mb-4">Our Facilities</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Modern Equipment</h5>
                                <p class="card-text">Our gym features the latest fitness technology and equipment to enhance your workout experience.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Spacious Training Areas</h5>
                                <p class="card-text">Enjoy plenty of space for your workouts with dedicated zones for different training styles.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">VIP Lounge</h5>
                                <p class="card-text">Ultimate members can relax in our exclusive VIP area with premium amenities and services.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Testimonials Section -->
            <section class="container my-5" id="testimonials">
                <h2 class="text-center mb-4">What Our Members Say</h2>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <p class="card-text">"Joining Gymbros was the best decision I've made for my health. The trainers are knowledgeable, the equipment is top-notch, and the community is so supportive."</p>
                                <div class="d-flex align-items-center mt-3">
                                    <img src="images/peter.png" class="rounded-circle me-3" width="50" height="50" alt="Member">
                                    <div>
                                        <h6 class="mb-0">David Wilson</h6>
                                        <small class="text-muted">Member since 2018</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <p class="card-text">"As someone who was intimidated by gyms, Gymbros changed my perspective completely. The staff is friendly, and I've achieved results I never thought possible."</p>
                                <div class="d-flex align-items-center mt-3">
                                    <img src="images/peter.png" class="rounded-circle me-3" width="50" height="50" alt="Member">
                                    <div>
                                        <h6 class="mb-0">Lisa Thompson</h6>
                                        <small class="text-muted">Member since 2020</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
</html>