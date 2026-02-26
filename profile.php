<?php
session_start();
date_default_timezone_set('Asia/Manila');

// Redirect guests back to login
if (!isset($_SESSION['USERID'])) {
    header("Location: login.php");
    exit();
}

$serverName = "ELIJAH\\SQLEXPRESS"; 
$database = "CHECKM8";

$username = $_SESSION['USERNAME'];
$elo = 1200;
$balance = "0.00";
$postCount = 0;
$myPosts = [];

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database;ConnectionPooling=0;TrustServerCertificate=1", "", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle Post Deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
        $deletePostId = $_POST['delete_post_id'];
        
        // Security check: Verify the post belongs to the logged-in user
        $verifyStmt = $conn->prepare("SELECT USERID FROM POSTS WHERE POSTID = :postid");
        $verifyStmt->execute(['postid' => $deletePostId]);
        $postOwner = $verifyStmt->fetchColumn();
        
        if ($postOwner == $_SESSION['USERID']) {
            $deleteStmt = $conn->prepare("DELETE FROM POSTS WHERE POSTID = :postid");
            $deleteStmt->execute(['postid' => $deletePostId]);
            
            // Refresh the page to remove the deleted post from the view
            header("Location: profile.php");
            exit();
        }
    }

    // 1. Fetch User Stats (Elo Rating and Balance)
    $stmtUser = $conn->prepare("
        SELECT U.BALANCE, C.ELORATING 
        FROM USERS U 
        LEFT JOIN CHESSSTATS C ON U.USERID = C.USERID 
        WHERE U.USERID = :userid
    ");
    $stmtUser->execute(['userid' => $_SESSION['USERID']]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
    if ($userData) {
        $balance = number_format($userData['BALANCE'], 2);
        if (isset($userData['ELORATING'])) {
            $elo = $userData['ELORATING'];
        }
    }

    // 2. Fetch Total Post Count
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM POSTS WHERE USERID = :userid");
    $stmtCount->execute(['userid' => $_SESSION['USERID']]);
    $postCount = $stmtCount->fetchColumn();

    // 3. Fetch the User's Post History
    $stmtPosts = $conn->prepare("
        SELECT POSTID, CONTENT, IMAGEURL, CREATEDAT 
        FROM POSTS 
        WHERE USERID = :userid 
        ORDER BY CREATEDAT DESC
    ");
    $stmtPosts->execute(['userid' => $_SESSION['USERID']]);
    $myPosts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="style.css" rel="stylesheet">
        <title>CheckM8 — My Profile</title>
        <style>
            .profile-banner {
                height: 200px;
                background: linear-gradient(135deg, #4A3563, #261F33);
                border-radius: 20px 20px 0 0;
                position: relative;
                border: 1px solid #6B4C8A;
                border-bottom: none;
            }
            .profile-avatar {
                width: 120px;
                height: 120px;
                background-color: #7B52AB;
                color: #FFFFFF;
                font-size: 3rem;
                font-weight: bold;
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                border: 6px solid #1A1625;
                position: absolute;
                bottom: -60px;
                left: 50%;
                transform: translateX(-50%);
                box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            }
            .stat-box {
                text-align: center;
                padding: 15px;
            }
            .stat-value {
                font-size: 1.5rem;
                font-weight: 800;
                color: #E8E4ED;
                margin-bottom: 0;
            }
            .stat-label {
                font-size: 0.9rem;
                color: #9B7EBD;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .post-action { cursor: pointer; transition: color 0.2s; }
            .post-action:hover { color: #E8E4ED !important; }
        </style>
    </head>
    <body>
        <div class="container mt-4 mb-5 pb-5">
            
            <div class="row mb-4 align-items-center">
                <div class="col-auto">
                    <a href="index.php" class="btn accent-box rounded-pill px-4 py-2" style="font-weight: 600;">← Back to Board</a>
                </div>
                <div class="col text-end">
                    <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4 py-2" style="font-weight: 600; border-color: #9B7EBD; color: #9B7EBD;">Log Out</a>
                </div>
            </div>

            <div class="row justify-content-center mb-5">
                <div class="col-md-10 col-lg-8">
                    <div class="profile-banner">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                    </div>
                    <div class="content-box pt-5 pb-4 px-4 text-center" style="border-radius: 0 0 20px 20px; border-top: none;">
                        <h2 class="mt-4 mb-0" style="color: #E8E4ED; font-weight: 700;"><?php echo htmlspecialchars($username); ?></h2>
                        <p style="color: #9B7EBD; font-size: 1.1rem;">@<?php echo htmlspecialchars($username); ?> • Player</p>
                        
                        <div class="row mt-4 justify-content-center">
                            <div class="col-4 stat-box border-end" style="border-color: #4A3563 !important;">
                                <h3 class="stat-value"><?php echo htmlspecialchars($elo); ?></h3>
                                <span class="stat-label">Elo Rating</span>
                            </div>
                            <div class="col-4 stat-box border-end" style="border-color: #4A3563 !important;">
                                <h3 class="stat-value"><?php echo htmlspecialchars($postCount); ?></h3>
                                <span class="stat-label">Posts</span>
                            </div>
                            <div class="col-4 stat-box">
                                <a href="top_up.php" class="text-decoration-none d-block">
                                    <h3 class="stat-value" style="color: #9B7EBD;"><?php echo htmlspecialchars($balance); ?></h3>
                                    <span class="stat-label">Coins ➕</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8">
                    <h4 class="mb-4" style="color: #E8E4ED; font-weight: 600; border-bottom: 2px solid #4A3563; padding-bottom: 10px;">Your Posts</h4>

                    <?php if (empty($myPosts)): ?>
                        <div class="content-box p-5 text-center">
                            <span style="font-size: 3rem; color: #4A3563;">📭</span>
                            <h4 class="mt-3" style="color: #9B7EBD;">You haven't posted anything yet.</h4>
                            <p style="color: #7B52AB;">Head back to the board to share your first post!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($myPosts as $post): ?>
                            <div class="content-box p-4 mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="accent-box rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 45px; font-weight: bold; background-color: #4A3563;">
                                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="m-0" style="color: #E8E4ED; font-weight: 600;">
                                            <?php echo htmlspecialchars($username); ?>
                                        </h6>
                                        <small style="color: #9B7EBD;">
                                            <?php echo date('M j, Y - g:i A', strtotime($post['CREATEDAT'])); ?>
                                        </small>
                                    </div>
                                    <div class="ms-auto">
                                        <form method="POST" action="profile.php" onsubmit="return confirm('Are you sure you want to delete this post? This move cannot be undone!');" class="m-0 p-0">
                                            <input type="hidden" name="delete_post_id" value="<?php echo $post['POSTID']; ?>">
                                            <button type="submit" class="btn btn-sm post-action" style="color: #9B7EBD; border: none; background: none;" title="Delete Post">🗑️</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <p style="color: #E8E4ED; white-space: pre-wrap; font-size: 1.05rem;"><?php echo htmlspecialchars($post['CONTENT']); ?></p>
                                
                                <?php if (!empty($post['IMAGEURL'])): ?>
                                    <div class="mt-3 mb-2 text-center">
                                        <img src="<?php echo htmlspecialchars($post['IMAGEURL']); ?>" class="img-fluid" style="border-radius: 15px; border: 1px solid #4A3563; max-height: 400px; object-fit: contain;">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between mt-3 px-2" style="color: #7B52AB; font-size: 1.1rem;">
                                    <span class="post-action" title="Comment">💬 0</span>
                                    <span class="post-action" title="Repost">🔁 0</span>
                                    <span class="post-action" title="Like">❤️ 0</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>