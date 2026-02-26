<?php
session_start();

if (!isset($_SESSION['USERID']) || !isset($_SESSION['ISADMIN']) || $_SESSION['ISADMIN'] != 1) {
    header("Location: index.php");
    exit();
}

$serverName = "ELIJAH\\SQLEXPRESS"; 
$database = "CHECKM8";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']);

    if (empty($identifier)) {
        $message = "<div class='alert alert-danger'>Please enter a username or email.</div>";
    } else {
        try {
            $conn = new PDO("sqlsrv:server=$serverName;Database=$database;ConnectionPooling=0;TrustServerCertificate=1", "", "");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare("SELECT USERID, USERNAME, ISADMIN FROM USERS WHERE USERNAME = :id1 OR EMAIL = :id2");
            $stmt->execute(['id1' => $identifier, 'id2' => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $message = "<div class='alert alert-danger'>User not found. Please check the spelling and try again.</div>";
            } elseif ($user['ISADMIN'] == 1) {
                $message = "<div class='alert alert-warning'><strong>@" . htmlspecialchars($user['USERNAME']) . "</strong> is already an Administrator!</div>";
            } else {
                $updateStmt = $conn->prepare("UPDATE USERS SET ISADMIN = 1 WHERE USERID = :userid");
                $updateStmt->execute(['userid' => $user['USERID']]);
                
                $message = "<div class='alert alert-success'>Success! <strong>@" . htmlspecialchars($user['USERNAME']) . "</strong> has been promoted to Administrator.</div>";
            }

        } catch(PDOException $e) {
            $message = "<div class='alert alert-danger'>Database Error: " . $e->getMessage() . "</div>";
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
        <title>CheckM8 — Create Admin</title>
    </head>
    <body>
        <div class="container mt-5 mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #E8E4ED;">🔑 Create New Admin</h2>
                <a href="admin_dashboard.php" class="btn accent-box px-4 py-2">Back to Dashboard</a>
            </div>

            <div class="row justify-content-center mt-5">
                <div class="col-md-8 col-lg-6">
                    
                    <?php echo $message; ?>

                    <div class="content-box p-5" style="border: 2px solid #6B4C8A;">
                        <h4 class="text-center mb-4" style="color: #E8E4ED;">Promote a User</h4>
                        <p class="text-center mb-4" style="color: #9B7EBD;">Enter the username or email address of the account you wish to elevate to Administrator status. This action grants them full access to the moderation panel.</p>
                        
                        <form method="POST" action="admin_create.php">
                            <div class="mb-4">
                                <label style="color: #E8E4ED; font-weight: bold;">Username or Email:</label>
                                <input type="text" name="identifier" class="form-control rounded-pill search-input mt-2 p-3" placeholder="e.g., GrandmasterFlash" required>
                            </div>
                            
                            <button type="submit" class="btn accent-box w-100 rounded-pill p-3" style="font-size: 1.2rem; font-weight: bold;" onclick="return confirm('Are you absolutely sure you want to grant this user Admin privileges?');">
                                GRANT ADMIN PRIVILEGES
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>