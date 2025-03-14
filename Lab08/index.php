<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Fitness Gym</title>
        <?php
            include "inc/head.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
            include "inc/jumbotron.inc.php";
        ?>
        <!-- Gym Carousel-->
        <div id = "gymCarousel" class = "carousel slide" data-bs-ride = "carousel">
            <!-- Carousel Indicators -->
            <ol class = "carousel-indicators">
                <li data-bs-target = "#gymCarousel" data-bs-slide-to = "0" class = "active"></li>
                <li data-bs-target = "#gymCarousel" data-bs-slide-to = "1"></li>
                <li data-bs-target = "#gymCarousel" data-bs-slide-to = "2"></li>
            </ol>
            <!-- Carousel Inner -->
            <div class = "carousel-inner">
                <!-- First Slide -->
                <div class = "carousel-item active"> 
                    <a href = "membership.php">
                        <img src = "images/tabby_large.jpg" class="d-block w-100" alt="Basic Membership">
                    </a>
                    <div class = "carousel-caption d-none d-md-block">
                        <h5>Basic Membership</h5>
                        <p>Access to gym equipment and group fitness classes.</p>
                    </div>
                </div>
                <!-- Second Slide -->
                <div class = "carousel-item">
                    <a href = "membership.php">
                        <img src = "images/tabby_large.jpg" class="d-block w-100" alt="Premium Membership">
                    </a>
                    <div class = "carousel-caption d-none d-md-block">
                        <h5>Premium Membership</h5>
                        <p>Access to gym equipment and group fitness classes.</p>
                    </div>
                </div>
                <!-- Third Slide -->
                <div class = "carousel-item">
                    <a href = "membership.php">
                        <img src = "images/tabby_large.jpg" class="d-block w-100" alt="Supreme Membership">
                    </a>
                    <div class = "carousel-caption d-none d-md-block">
                        <h5>Supreme Membership</h5>
                        <p>Access to gym equipment and group fitness classes.</p>
                    </div>
                </div>
            </div>
            <!-- Carousel Controls -->
            <<a class="carousel-control-prev" href="#gymCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#gymCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>

        <main class="container">
            <!-- Northeast section -->
            <section id="northeast">
                <h2>Northeast Locations</h2>
                <div class ="row">
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/sengkang_branch.jpg" 
                            alt="Sengkang GymBro Branch" title="Click to make a booking"
                            location="Anchorvale Road, #123" hours="07:00 - 22:00"
                            contact="61234567"/>
                        <figcaption>Sengkang GymBro Branch</figcaption>
                    </figure>
                </article>
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/hougang_branch.jpg" 
                            alt="Hougang GymBro Branch" title="Click to make a booking"
                            location="Hougang Ave 68, #456" hours="06:00 - 23:00"
                            contact="98765432"/>
                        <figcaption>Hougang GymBro Branch</figcaption>
                    </figure>
                </article>
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/punggol_branch.jpg" 
                            alt="Punggol GymBro Branch" title="Click to make a booking"
                            location="Teck Lee LRT" hours="09:00 - 00:00"
                            contact="99996666"/>
                        <figcaption>Punggol GymBro Branch</figcaption>
                    </figure>
                </article>
                </div>
            </section>

            <!-- Southwest section -->
            <section id="southwest">
                <h2>Southwest Locations</h2>
                <div class ="row">
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/cck_branch.jpg" 
                            alt="Choa Chu Kang GymBro Branch" title="Click to make a booking"
                            location="Choa Chu Kang Central, #999" hours="05:00 - 21:00"
                            contact="66669999"/>
                        <figcaption>Choa Chu Kang GymBro Branch</figcaption>
                    </figure>
                </article>
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/batok_branch.jpg" 
                            alt="Bukit Batok GymBro Branch" title="Click to make a booking"
                            location="Bukit Batok West Ave 46, #009" hours="07:00 - 23:00"
                            contact="64447888"/>
                        <figcaption>Bukit Batok GymBro Branch</figcaption>
                    </figure>
                </article>
                <article class="col-sm">
                    <figure>
                        <img class="img-thumbnail" src="images/jr_west_branch.jpg" 
                            alt="jurong West GymBro Branch" title="Click to make a booking"
                            location="Joo Koon Road, #666" hours="04:00 - 21:00"
                            contact="90000001"/>
                        <figcaption>Jurong West GymBro Branch</figcaption>
                    </figure>
                </article>
                </div>
            </section>
        </main>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
</html>