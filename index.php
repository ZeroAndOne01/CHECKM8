<?php
session_start();
date_default_timezone_set('Asia/Manila');

$serverName = "ELIJAH\\SQLEXPRESS"; 
$database = "CHECKM8";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database;ConnectionPooling=0;TrustServerCertificate=1", "", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $userElo = 1200; 
    $userBalance = "0.00";

    if (isset($_SESSION['USERID'])) {
        
        $stmtStats = $conn->prepare("SELECT ELORATING FROM CHESSSTATS WHERE USERID = :userId");
        $stmtStats->execute(['userId' => $_SESSION['USERID']]);
        $userStats = $stmtStats->fetch(PDO::FETCH_ASSOC);
        if ($userStats) {
            $userElo = $userStats['ELORATING'];
        }

        $stmtBalance = $conn->prepare("SELECT BALANCE FROM USERS WHERE USERID = :userId");
        $stmtBalance->execute(['userId' => $_SESSION['USERID']]);
        $userRow = $stmtBalance->fetch(PDO::FETCH_ASSOC);
        if ($userRow) {
            $userBalance = number_format($userRow['BALANCE'], 2);
        }
    }

    if (isset($_SESSION['USERID'])) {
        
        $stmtStats = $conn->prepare("SELECT ELORATING FROM CHESSSTATS WHERE USERID = :userId");
        $stmtStats->execute(['userId' => $_SESSION['USERID']]);
        $userStats = $stmtStats->fetch(PDO::FETCH_ASSOC);
        if ($userStats) {
            $userElo = $userStats['ELORATING'];
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_content'])) {
            $content = trim($_POST['post_content']);
            $imageUrl = null;
            if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                
                $uploadDir = 'uploads/';
                $fileExtension = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = uniqid('post_', true) . '.' . $fileExtension;
                    $targetFilePath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($_FILES['post_image']['tmp_name'], $targetFilePath)) {
                        $imageUrl = $targetFilePath; 
                    }
                }
            }

            if (!empty($content) || $imageUrl != null) {
                $insertPost = $conn->prepare("INSERT INTO POSTS (USERID, CONTENT, IMAGEURL) VALUES (:userid, :content, :imageurl)");
                $insertPost->execute([
                    'userid' => $_SESSION['USERID'],
                    'content' => $content,
                    'imageurl' => $imageUrl
                ]);
                
                header("Location: index.php");
                exit();
            }
        }
    }

    $query = "SELECT P.POSTID, P.CONTENT, P.IMAGEURL, P.CREATEDAT, U.USERNAME 
              FROM POSTS P
              JOIN USERS U ON P.USERID = U.USERID
              ORDER BY P.CREATEDAT DESC";
    $stmtPosts = $conn->query($query);
    $feedPosts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

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
        <title>CheckM8 — Dashboard</title>
        <style>
            .post-action { cursor: pointer; transition: color 0.2s; }
            .post-action:hover { color: #E8E4ED !important; }
            .scroll-box::-webkit-scrollbar { width: 8px; }
            .scroll-box::-webkit-scrollbar-track { background: #1A1625; }
            .scroll-box::-webkit-scrollbar-thumb { background-color: #4A3563; border-radius: 10px; }
            
            .custom-file-upload { color: #9B7EBD; cursor: pointer; transition: color 0.2s; }
            .custom-file-upload:hover { color: #E8E4ED; }
            input[type="file"] { display: none; }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row m-3">
                
                <div class="col-3 content-box m-3 p-4 d-flex flex-column align-items-center justify-content-center" style="height: 75vh;">
                    <?php if (!isset($_SESSION['USERID'])): ?>
                        <h3 class="mb-5 text-center" style="color: #E8E4ED;">Welcome to CheckM8</h3>
                        <a href="login.php" class="btn w-75 mb-4 accent-box d-flex justify-content-center align-items-center" style="height: 60px; font-size: 1.5rem;">LOGIN</a>
                        <a href="registration.php" class="btn w-75 accent-box d-flex justify-content-center align-items-center" style="height: 60px; font-size: 1.5rem;">SIGN UP</a>
                    <?php else: ?>
                        <div class="accent-box rounded-circle d-flex justify-content-center align-items-center mb-3 mt-2" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold;">
                            <?php echo strtoupper(substr($_SESSION['USERNAME'], 0, 1)); ?>
                        </div>
                        <h4 class="text-center mb-0" style="color: #E8E4ED;"><?php echo htmlspecialchars($_SESSION['USERNAME']); ?></h4>
                        <p class="mb-4" style="color: #9B7EBD;">@<?php echo htmlspecialchars($_SESSION['USERNAME']); ?></p>
                        
                        <div class="d-flex justify-content-around w-100 mb-auto px-2">
                            <div class="text-center">
                                <h4 class="mb-0" style="color: #E8E4ED;"><?php echo htmlspecialchars($userElo); ?></h4>
                                <small style="color: #9B7EBD;">Elo Rating</small>
                            </div>
                            <div class="text-center">
                                <h4 class="mb-0" style="color: #E8E4ED;">0</h4>
                                <small style="color: #9B7EBD;">Followers</small>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['ISADMIN']) && $_SESSION['ISADMIN'] == 1): ?>
                            <a href="admin_dashboard.php" class="btn mt-2 mb-3 w-75 d-flex justify-content-center align-items-center" style="height: 50px; font-size: 1.1rem; background-color: #E8E4ED; color: #1A1625; font-weight: bold; border-radius: 30px; text-decoration: none;">
                                ADMIN PANEL
                            </a>
                        <?php endif; ?>
                        
                        <a href="logout.php" class="btn mt-2 mb-3 accent-box w-75 d-flex justify-content-center align-items-center" style="height: 50px; font-size: 1.2rem;">LOGOUT</a>
                    <?php endif; ?>
                </div>
                
                <div class="col m-3 scroll-box" style="max-height: 80vh; padding-bottom: 15vh;">
                    
                    <div class="row mb-4 sticky-top pt-2 pb-2 align-items-center" style="background-color: #1A1625; z-index: 10;">
                        <?php if (!isset($_SESSION['USERID'])): ?>
                            <div class="col-12">
                                <input type="text" class="form-control rounded-pill search-input" placeholder="Search users, games, or posts...">
                            </div>
                        <?php else: ?>
                            <div class="col-8">
                                <input type="text" class="form-control rounded-pill search-input" placeholder="Search users, games, or posts...">
                            </div>
                            <div class="col-4 d-flex justify-content-end align-items-center">
                                <h5 class="me-3 mb-0" style="color: #E8E4ED;">Balance</h5>
                                <a href="top_up.php" class="accent-box px-4 py-2 rounded-pill text-center text-decoration-none d-block shadow-sm">
                                    <h5 class="mb-0" style="color: #FFFFFF; font-weight: 700;"><?php echo htmlspecialchars($userBalance); ?></h5>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!isset($_SESSION['USERID'])): ?>
                        <div class="content-box p-5 text-center">
                            <h3 style="color: #9B7EBD;">Please log in or sign up to see the global feed!</h3>
                        </div>
                    <?php else: ?>
                        
                        <form method="POST" action="index.php" enctype="multipart/form-data">
                            <div class="content-box p-4 mb-4" style="border: 2px solid #6B4C8A;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="accent-box rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px; font-weight: bold; flex-shrink: 0;">
                                        <?php echo strtoupper(substr($_SESSION['USERNAME'], 0, 1)); ?>
                                    </div>
                                    <textarea name="post_content" class="form-control search-input border-0" placeholder="What's your next move?" rows="2" style="resize: none; border-radius: 15px;" maxlength="1000"></textarea>
                                </div>
                                
                                <div id="imagePreviewContainer" class="mb-3 px-5" style="display: none; position: relative; max-width: fit-content;">
                                    <img id="imagePreview" src="" style="max-height: 200px; border-radius: 10px; border: 1px solid #9B7EBD;">
                                    <button type="button" id="removeImageBtn" class="btn btn-danger rounded-circle position-absolute d-flex justify-content-center align-items-center" style="top: -10px; right: -10px; width: 25px; height: 25px; padding: 0; font-weight: bold; border: 2px solid #1A1625;">&times;</button>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2 px-2">
                                    <label class="custom-file-upload m-0" title="Attach an Image">
                                        <input type="file" name="post_image" id="postImageInput" accept=".jpg,.jpeg,.png,.gif,.webp">
                                        <span style="font-size: 1.5rem;">📷</span> <span style="font-size: 0.9rem; margin-left: 5px;">Attach Image</span>
                                    </label>
                                    
                                    <button type="submit" class="btn accent-box rounded-pill px-4 py-1" style="font-weight: bold;">Post</button>
                                </div>
                            </div>
                        </form>

                        <?php if (empty($feedPosts)): ?>
                            <div class="content-box p-5 text-center">
                                <h4 style="color: #9B7EBD;">The board is empty. Be the first to make a move!</h4>
                            </div>
                        <?php else: ?>
                            <?php foreach ($feedPosts as $post): ?>
                                <div class="content-box p-4 mb-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="accent-box rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px; font-weight: bold; background-color: #4A3563;">
                                            <?php echo strtoupper(substr($post['USERNAME'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h5 class="m-0" style="color: #E8E4ED;">
                                                <?php echo htmlspecialchars($post['USERNAME']); ?>
                                            </h5>
                                            <small style="color: #9B7EBD;">
                                                @<?php echo htmlspecialchars($post['USERNAME']); ?> • <?php echo date('M j, Y - g:i A', strtotime($post['CREATEDAT'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <p style="color: #E8E4ED; white-space: pre-wrap; font-size: 1.1rem;"><?php echo htmlspecialchars($post['CONTENT']); ?></p>
                                    
                                    <?php if (!empty($post['IMAGEURL'])): ?>
                                        <div class="mt-3 mb-2 text-center">
                                            <img src="<?php echo htmlspecialchars($post['IMAGEURL']); ?>" class="img-fluid" style="border-radius: 15px; border: 1px solid #4A3563; max-height: 500px; object-fit: contain;">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between mt-3 px-3" style="color: #9B7EBD; font-size: 1.2rem;">
                                        <span class="post-action" title="Comment">💬 0</span>
                                        <span class="post-action" title="Repost">🔁 0</span>
                                        <span class="post-action" title="Like">❤️ 0</span>
                                        
                                        <form method="POST" action="flag_post.php" class="d-inline m-0 p-0" onsubmit="return confirm('Report this post to the admins?');">
                                            <input type="hidden" name="post_id" value="<?php echo $post['POSTID']; ?>">
                                            <button type="submit" class="btn btn-link p-0 m-0 text-decoration-none post-action" title="Report Flag" style="color: inherit;">🚩</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>

        <nav class="navbar fixed-bottom bottom-nav-bg pb-4" style="height: 15vh; z-index: 100;">
            <div class="container d-flex justify-content-between align-items-end h-100">
                
                <div class="d-flex gap-3 gap-md-4" style="width: 38vw;">
                    <a class="nav-link accent-box flex-fill rounded-pill nav-btn" href="profile.php">Profile</a>
                    <a class="nav-link accent-box flex-fill rounded-pill nav-btn" href="#">Friends</a>
                </div>

                <div class="d-flex gap-3 gap-md-4" style="width: 38vw;">
                    <a class="nav-link accent-box flex-fill rounded-pill nav-btn" href="#">Leaderboard</a>
                    <a class="nav-link accent-box flex-fill rounded-pill nav-btn" href="#">Settings</a>
                </div>
                
            </div>
            
            <a class="nav-link play-circle position-absolute" style="bottom: 35px; left: 50%; transform: translateX(-50%);" href="#">
                <span style="font-size: 3.5rem; line-height: 1;">♟</span>
                <span style="font-size: 1.3rem; margin-top: -5px; letter-spacing: 1px;">PLAY</span>
            </a>
        </nav>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
        
        <script>
            const imageInput = document.getElementById('postImageInput');
            const previewContainer = document.getElementById('imagePreviewContainer');
            const previewImage = document.getElementById('imagePreview');
            const removeBtn = document.getElementById('removeImageBtn');

            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    previewImage.src = URL.createObjectURL(file);
                    previewContainer.style.display = 'block';
                } else {
                    previewContainer.style.display = 'none';
                    previewImage.src = '';
                }
            });

            removeBtn.addEventListener('click', function() {
                imageInput.value = '';
                previewContainer.style.display = 'none';
                previewImage.src = '';
            });
        </script>
    </body>
</html>