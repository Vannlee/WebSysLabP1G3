<!DOCTYPE html>
<html lang="en">
    <head>
        <title>World of Pets</title>
        <?php
            include "inc/head.inc.php";
        ?>
    </head>
    <body>
        <?php
            include "inc/nav.inc.php";
            include "inc/jumbotron.inc.php";
        ?>

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