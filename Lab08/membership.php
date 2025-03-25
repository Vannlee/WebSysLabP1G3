<?php
    session_start();
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
        <section class="container my-5">
  <h2 class="text-center mb-4">Choose Your Membership</h2>

  <div class="row g-4 justify-content-center">

    <!-- Student Tier -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card h-100 shadow text-center">
        <div class="card-body">
          <h5 class="card-title">Student</h5>
          <h6 class="card-subtitle mb-2 text-muted">$35/month</h6>
          <p class="card-text">✅2 Hour Gym Access daily</p>
          <p class="card-text">✅Free Locker</p>
          <p class="card-text">❌Protein Shake</p>
          <p class="card-text">❌Gym Item Discounts</p>
          <p class="card-text">❌Personal Trainer</p>
          <p class="card-text">❌VIP Lounge</p>


          <a href="register.php" class="btn btn-outline-primary">Select</a>
        </div>
      </div>
    </div>

    <!-- Basic Tier -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card h-100 shadow text-center">
        <div class="card-body">
          <h5 class="card-title">Standard</h5>
          <h6 class="card-subtitle mb-2 text-muted">$55/month</h6>
          <p class="card-text">✅2 Hour Gym Access daily</p>
          <p class="card-text">✅Free Locker</p>
          <p class="card-text">✅Protein Shake</p>
          <p class="card-text">❌Gym Item Discounts</p>
          <p class="card-text">❌Personal Trainer</p>
          <p class="card-text">❌VIP Lounge</p>
          <a href="register.php" class="btn btn-outline-primary">Select</a>
        </div>
      </div>
    </div>

    <!-- Premium Tier -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card h-100 shadow text-center border-primary">
        <div class="card-body">
          <h5 class="card-title">Premium</h5>
          <h6 class="card-subtitle mb-2 text-muted">$75/month</h6>
          <p class="card-text">✅2 Hour Gym Access daily</p>
          <p class="card-text">✅Free Locker</p>
          <p class="card-text">✅Protein Shake</p>
          <p class="card-text">✅Gym Item Discounts</p>
          <p class="card-text">❌Personal Trainer</p>
          <p class="card-text">❌VIP Lounge</p>
          <a href="register.php" class="btn btn-primary">Select</a>
        </div>
      </div>
    </div>

    <!-- Ultimate Tier -->
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card h-100 shadow text-center">
        <div class="card-body">
          <h5 class="card-title">Ultimate</h5>
          <h6 class="card-subtitle mb-2 text-muted">$110/month</h6>
          <p class="card-text">✅2 Hour Gym Access daily</p>
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