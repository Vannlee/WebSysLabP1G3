<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand m-2" href="index.php">
            <img src="images/gym-logo.png" alt="Fitness Gym" title="Fitness Gym" height="42" width="88"/>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left side navigation -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php#locations">Locations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="membership.php">Memberships</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Classes
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="explore.php">Explore</a></li>
                        <li><a class="dropdown-item" href="timetable.php">Timetable</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="instructors.php">View Instructors</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="aboutus.php">About Us</a>
                </li>
            </ul>

            <!-- Right side (Login or User menu) -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <!-- Guest view -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-success" href="register.php" role="button">Sign Up</a>
                    </li>
                <?php else: ?>
                    <!-- Logged-in user view -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="images/register.png" alt="User" title="User" width="50"/>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="timetable.php">Book a slot</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="booking.php">Manage Bookings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

