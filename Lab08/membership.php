<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Membership</title>
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
        <section class="container my-5">
            <h2 class="text-center mb-4">Choose Your Membership</h2>
            <div class="row justify-content-center g-4">

                <!-- Free Tier -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow">
                        <div class="card-body text-center">
                            <h5 class="card-title">Basic</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Free</h6>
                            <p class="card-text">✅1 Hour Gym Access daily</p>
                            <p class="card-text">❌Free Locker</p>
                            <p class="card-text">❌Protein Shake</p>
                            <p class="card-text">❌Gym Item Discounts</p>
                            <p class="card-text">❌Personal Trainer</p>
                            <p class="card-text">❌VIP Lounge</p>
                            <a href="register.php" class="btn btn-outline-primary">Select</a>
                        </div>
                    </div>
                </div>

                <!-- Premium -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow border-primary">
                        <div class="card-body text-center">
                            <h5 class="card-title">Premium</h5>
                            <h6 class="card-subtitle mb-2 text-muted">$40/month</h6>
                            <p class="card-text">✅2 Hour Gym Access daily</p>
                            <p class="card-text">✅Free Locker</p>
                            <p class="card-text">✅Protein Shake</p>
                            <p class="card-text">❌Gym Item Discounts</p>
                            <p class="card-text">❌Personal Trainer</p>
                            <p class="card-text">❌VIP Lounge</p>
                            <a href="register.php" class="btn btn-primary">Select</a>
                        </div>
                    </div>
                </div>

                <!-- Ultimate -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow">
                        <div class="card-body text-center">
                            <h5 class="card-title">Ultimate</h5>
                            <h6 class="card-subtitle mb-2 text-muted">$90/month</h6>
                            <p class="card-text">✅Unlimited Gym Access daily</p>
                            <p class="card-text">✅Free Locker</p>
                            <p class="card-text">✅Protein Shake</p>
                            <p class="card-text">✅Gym Item Discounts</p>
                            <p class="card-text">✅Personal Trainer</p>
                            <p class="card-text">✅VIP Lounge</p>
                            <a href="register.php" class="btn btn-outline-primary">Select</a>
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
