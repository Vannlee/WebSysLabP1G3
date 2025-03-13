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
            <!-- dogs section -->
            <section id="dogs">
                <h2>All About Dogs!</h2>
                <div class ="row">
                <article class="col-sm">
                    <h3>Poodles</h3>
                    <figure>
                        <img class="img-thumbnail" src="images/poodle_small.jpg" alt="poodle"
                            title="View larger image..."/>
                        <figcaption>Standard Poodle</figcaption>
                    </figure>
                    <p>
                        Poodles are a group of formal dog breeds, the Standard
                        Poodle, Miniature Poodle and Toy Poodle.
                    </p>
                </article>
                <article class="col-sm">
                    <h3>Chihuahua</h3>
                    <figure>
                        <img class="img-thumbnail" src="images/chihuahua_small.jpg" alt="chihuahua"
                            title="View larger image..."/>
                        <figcaption>Standard Chihuahua</figcaption>
                    </figure>
                    <p>
                        The Chihuahua is the smallest breed of dog, and is named
                        after the Mexican state of Chihuahua.
                    </p>
                </article>
                </div>
            </section>

            <!-- cats section -->
            <section id="cats">
                <h2>All About Cats!</h2>
                <div class="row">
                <article class="col-sm">
                    <h3>Tabby</h3>
                    <figure>
                        <img class="img-thumbnail" src="images/tabby_small.jpg" alt="tabby"
                            title="View larger image..."/>
                        <figcaption>Standard Tabby</figcaption>
                    </figure>
                    <p>
                        A tabby is any domestic cat (Felis catus) with a distinctive
                        'M' shaped marking on its forehead, stripes by its eyes 
                        and across its checks. 
                    </p>
                </article>
                <article class="col-sm">
                    <h3>Calico</h3>
                    <figure>
                        <img class="img-thumbnail" src="images/calico_small.jpg" alt="calico"
                            title="View larger image..."/>
                        <figcaption>Standard Calico</figcaption>
                    </figure>
                    <p>
                        A calico cat is a domestic cat of any breed with a tri-
                        color coat. The calico cat is most commonly thought of 
                        as being typically 25% to 75% white with large orange 
                        and black patches.
                    </p>
                </article>
                </div>
            </section>
        </main>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
</html>