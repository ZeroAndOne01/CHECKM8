<?php
session_start();
date_default_timezone_set('Asia/Manila');

$serverName = "ELIJAH\\SQLEXPRESS"; 
$database = "CHECKM8";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameInput = trim($_POST['username']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $emailInput = trim($_POST['email']);
    $passwordInput = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    if (empty($usernameInput) || empty($firstName) || empty($lastName) || empty($emailInput) || empty($passwordInput)) {
        $errorMessage = "Please fill in all fields.";
    } 
    elseif ($passwordInput !== $confirmPassword) {
        $errorMessage = "Passwords do not match. Please re-type them.";
    }
    elseif (strlen($passwordInput) < 16 || strlen($passwordInput) > 32) {
        $errorMessage = "Your password must be between 16 and 32 characters long.";
    } 
    else {
        try {
            $conn = new PDO("sqlsrv:server=$serverName;Database=$database;ConnectionPooling=0;TrustServerCertificate=1", "", "");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM USERS WHERE EMAIL = :email OR USERNAME = :username");
            $checkStmt->execute(['email' => $emailInput, 'username' => $usernameInput]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $errorMessage = "This email or username is already registered.";
            } else {
                $hashedPassword = password_hash($passwordInput, PASSWORD_DEFAULT);

                $insertUserSql = "INSERT INTO USERS (USERNAME, EMAIL, PASSWORDHASH) 
                                  OUTPUT inserted.USERID 
                                  VALUES (:username, :email, :passwordhash)";
                $stmtUser = $conn->prepare($insertUserSql);
                $stmtUser->execute([
                    'username' => $usernameInput,
                    'email' => $emailInput,
                    'passwordhash' => $hashedPassword
                ]);
                $newUserId = $stmtUser->fetchColumn();

                $displayName = $firstName . " " . $lastName;
                $insertProfileSql = "INSERT INTO USERPROFILES (USERID, DISPLAYNAME) VALUES (:userid, :displayname)";
                $stmtProfile = $conn->prepare($insertProfileSql);
                $stmtProfile->execute(['userid' => $newUserId, 'displayname' => $displayName]);

                $insertStatsSql = "INSERT INTO CHESSSTATS (USERID) VALUES (:userid)";
                $stmtStats = $conn->prepare($insertStatsSql);
                $stmtStats->execute(['userid' => $newUserId]);

                $_SESSION['USERID'] = $newUserId;
                $_SESSION['USERNAME'] = $usernameInput;
                
                header("Location: index.php");
                exit();
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
        <title>CheckM8 — Register</title>
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
    <body>
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
                <div class="col-md-8 col-lg-6 content-box">
                    <div class="row justify-content-center m-3">
                        <div class="col">
                            
                            <?php if(!empty($errorMessage)): ?>
                                <div class="alert alert-danger mt-3" role="alert">
                                    <?php echo htmlspecialchars($errorMessage); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="registration.php">
                                <label class="mt-4">Username:</label>
                                <input type="text" name="username" class="form-control rounded-pill search-input mb-3" required>

                                <label>First Name:</label>
                                <input type="text" name="first_name" class="form-control rounded-pill search-input mb-3" required>
                                
                                <label>Last Name:</label>
                                <input type="text" name="last_name" class="form-control rounded-pill search-input mb-3" required>
                                
                                <label>Email:</label>
                                <input type="email" name="email" class="form-control rounded-pill search-input mb-3" required>
                                
                                <label>Password <span style="color: #9B7EBD; font-size: 0.85rem;">(16-32 characters)</span>:</label>
                                <input type="password" name="password" class="form-control rounded-pill search-input mb-3" minlength="16" maxlength="32" required>

                                <label>Confirm Password:</label>
                                <input type="password" name="confirm_password" class="form-control rounded-pill search-input mb-3" minlength="16" maxlength="32" required>
                                <button type="submit" class="btn accent-box w-100 rounded-pill p-3 mt-4 mb-3" style="font-size: 1.2rem;">
                                    <strong>Join Now</strong>
                                </button>
                            </form>

                            <div class="text-center mt-2 mb-3">
                                <p style="font-size: 1rem;">Already have an account? <a href="login.php" style="color: #9B7EBD; font-size: 1rem; text-decoration: underline;">Log In</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>