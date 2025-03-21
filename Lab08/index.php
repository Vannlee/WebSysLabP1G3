<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Fitness Gym</title>      
        <meta name="description" content="Fitness Gym - Landing Page"/>
        <?php
            include "inc/head.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
            include "inc/carousel.inc.php";
        ?>
        <main class="container" id="locations">
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
        <noscript>
            <p style="color: red;">JavaScript is disabled in your browser. Please enable it for the best experience.</p>
        </noscript>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
</html>