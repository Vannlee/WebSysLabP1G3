<?php
    session_start();
    // Check if user is already logged in
    if (isset($_SESSION["email"])) {
        header("Location: index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login | GymBros</title>
    <?php
        include "inc/head.inc.php";
        include "inc/enablejs.inc.php";
    ?>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <?php
        include "inc/nav.inc.php";
    ?>
    
    <main class="container login-container">
        <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> Invalid email or password. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> Registration successful! Please log in with your new account.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="login-card">
            <div class="login-header">
                <h2 class="mb-0 text-center">Welcome Back!</h2>
            </div>
            
            <div class="login-body">
                <form action="process_login.php" method="post">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input required maxlength="45" type="email" id="email" name="email" class="form-control"
                                placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label for="pwd" class="form-label">Password</label>
                            <a href="forgot-password.php" class="text-decoration-none small">Forgot password?</a>
                        </div>
                        <div class="input-group password-field">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input required type="password" id="pwd" name="pwd" class="form-control"
                                placeholder="Enter your password">
                            <span class="password-toggle" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-4 form-check">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Remember me for 30 days</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login">Sign In</button>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Sign up now</a></p>
            </div>
        </div>
    </main>
    
    <?php
        include "inc/footer.inc.php";
    ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('pwd');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle the eye / eye-slash icon
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
            
            // Focus on email field on page load
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>