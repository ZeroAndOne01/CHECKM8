<?php
session_start();

if (isset($_SESSION['USERID'])) {
    header("Location: index.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailInput = trim($_POST['email']);

    if (empty($emailInput)) {
        $message = "<div class='alert alert-danger'>Please enter your email address.</div>";
    } else {
        $message = "<div class='alert alert-success text-center'>
                        <strong>Request Received!</strong><br>
                        If an account with <b>" . htmlspecialchars($emailInput) . "</b> exists in our system, we have sent a password reset link to it.
                    </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="style.css" rel="stylesheet">
        <title>CheckM8 — Forgot Password</title>
        <style>
            .logo-circle {
                background-color: #6B4C8A;
                color: white;
                width: 140px;
                height: 140px;
                line-height: 140px;
                border-radius: 50%;
                text-align: center;
                font-weight: bold;
                font-size: 1.5rem;
                margin: 0;
            }
        </style>
    </head>
    <body class="d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="container mt-4 mb-5">
            
            <div class="row justify-content-center align-items-center m-1 mb-4">
                <div class="col-auto d-flex justify-content-end">
                    <div class="logo-circle" style="font-size: 8rem;">♞</div>
                </div>
                <div class="col-auto">
                    <h1 style="font-size: 3.5rem; font-weight: 700; letter-spacing: 1px;">CheckM8</h1>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-5 content-box">
                    <div class="row justify-content-center m-3">
                        <div class="col">
                            
                            <h3 class="text-center mt-3 mb-2" style="color: #E8E4ED;">Reset Password</h3>
                            <p class="text-center mb-4" style="color: #9B7EBD; font-size: 0.95rem;">Enter the email address associated with your account and we'll send you a link to reset your password.</p>

                            <?php echo $message; ?>

                            <?php if (empty($message) || strpos($message, 'alert-danger') !== false): ?>
                                <form method="POST" action="forgot_password.php">
                                    <label class="mt-2" style="color: #E8E4ED; font-weight: bold;">Email Address:</label>
                                    <input type="email" name="email" class="form-control rounded-pill search-input mb-4 p-3" placeholder="grandmaster@example.com" required>
                                    
                                    <button type="submit" class="btn accent-box w-100 rounded-pill p-3 mt-2 mb-3" style="font-size: 1.2rem; font-weight: bold;">
                                        SEND RESET LINK
                                    </button>
                                </form>
                            <?php endif; ?>

                            <div class="text-center mt-4 mb-2">
                                <p style="font-size: 1rem;"><a href="login.php" style="font-size: 1rem; color: #9B7EBD; text-decoration: underline;">Return to Login</a></p>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>