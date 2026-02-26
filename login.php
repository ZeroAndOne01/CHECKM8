<?php
session_start();

if (isset($_SESSION['USERID'])) {
    header("Location: index.php");
    exit();
}

$serverName = "ELIJAH\\SQLEXPRESS"; 
$database = "CHECKM8";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']); 
    $passwordInput = $_POST['password'];

    if (empty($identifier) || empty($passwordInput)) {
        $errorMessage = "Please enter both your username/email and password.";
    } elseif (strlen($passwordInput) < 16 || strlen($passwordInput) > 32) {
        $errorMessage = "Invalid password length. Passwords are between 16 and 32 characters.";
    } else {
        try {
            $conn = new PDO("sqlsrv:server=$serverName;Database=$database;ConnectionPooling=0;TrustServerCertificate=1", "", "");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("SELECT USERID, USERNAME, PASSWORDHASH, ISADMIN, FAILEDLOGINATTEMPTS, LOCKOUTUNTIL FROM USERS WHERE USERNAME = :id1 OR EMAIL = :id2");
            $stmt->execute(['id1' => $identifier, 'id2' => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $currentDateTime = new DateTime();
                $lockoutTime = $user['LOCKOUTUNTIL'] ? new DateTime($user['LOCKOUTUNTIL']) : null;

                if ($lockoutTime && $currentDateTime < $lockoutTime) {
                    $diff = $currentDateTime->diff($lockoutTime);
                    $errorMessage = "Account locked due to too many failed attempts. Try again in " . $diff->i . " minutes and " . $diff->s . " seconds.";
                } else {
                    if (password_verify($passwordInput, $user['PASSWORDHASH'])) {
                        $resetStmt = $conn->prepare("UPDATE USERS SET FAILEDLOGINATTEMPTS = 0, LOCKOUTUNTIL = NULL WHERE USERID = :uid");
                        $resetStmt->execute(['uid' => $user['USERID']]);
                        
                        $_SESSION['USERID'] = $user['USERID'];
                        $_SESSION['USERNAME'] = $user['USERNAME'];
                        $_SESSION['ISADMIN'] = $user['ISADMIN'];
                        
                        header("Location: index.php");
                        exit();
                    } else {
                        $attempts = $user['FAILEDLOGINATTEMPTS'] + 1;
                        
                        if ($attempts >= 3) {
                            $lockoutUntilStr = (new DateTime('+5 minutes'))->format('Y-m-d H:i:s');
                            $lockStmt = $conn->prepare("UPDATE USERS SET FAILEDLOGINATTEMPTS = :attempts, LOCKOUTUNTIL = :lockout WHERE USERID = :uid");
                            $lockStmt->execute(['attempts' => $attempts, 'lockout' => $lockoutUntilStr, 'uid' => $user['USERID']]);
                            
                            $errorMessage = "Account locked due to 3 failed attempts. Please try again in 5 minutes.";
                        } else {
                            $updateStmt = $conn->prepare("UPDATE USERS SET FAILEDLOGINATTEMPTS = :attempts WHERE USERID = :uid");
                            $updateStmt->execute(['attempts' => $attempts, 'uid' => $user['USERID']]);
                            
                            $remaining = 3 - $attempts;
                            $errorMessage = "Invalid password. You have $remaining attempt(s) remaining.";
                        }
                    }
                }
            } else {
                $errorMessage = "Invalid username/email or password.";
            }
        } catch(PDOException $e) {
            $errorMessage = "Database Error: " . $e->getMessage();
        }
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
        <title>CheckM8 — Login</title>
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
                            
                            <h3 class="text-center mt-3 mb-4" style="color: #E8E4ED;">Ready for your next move?</h3>

                            <?php if(!empty($errorMessage)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($errorMessage); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="login.php">
                                <label class="mt-2">Username or Email:</label>
                                <input type="text" name="identifier" class="form-control rounded-pill search-input mb-4" required>
                                
                                <div class="d-flex justify-content-between align-items-end">
                                    <label>Password <span style="color: #9B7EBD; font-size: 0.85rem;">(16-32 characters)</span>:</label>
                                    <a href="forgot_password.php" style="color: #9B7EBD; font-size: 0.85rem; text-decoration: underline;">Forgot Password?</a>
                                </div>
                                <input type="password" name="password" class="form-control rounded-pill search-input mb-4" minlength="16" maxlength="32" required>

                                <button type="submit" class="btn accent-box w-100 rounded-pill p-3 mt-2 mb-3" style="font-size: 1.2rem;">
                                    <strong>LOG IN</strong>
                                </button>
                            </form>

                            <div class="text-center mt-3 mb-3">
                                <p style="font-size: 1rem;">Don't have an account? <a href="registration.php" style="font-size: 1rem; color: #9B7EBD; text-decoration: underline;">Sign Up</a></p>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>