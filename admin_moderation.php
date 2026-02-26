<?php
session_start();

if (!isset($_SESSION['USERID']) || !isset($_SESSION['ISADMIN']) || $_SESSION['ISADMIN'] != 1) {
    header("Location: index.php");
    exit();
}

$serverName = "ELIJAH\\SQLEXPRESS"; 
$database = "CHECKM8";
$message = "";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database;ConnectionPooling=0;TrustServerCertificate=1", "", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $targetPostId = $_POST['target_postid'];
        $action = $_POST['admin_action'];

        if ($action == 'delete') {
            $delStmt = $conn->prepare("DELETE FROM POSTS WHERE POSTID = :id");
            $delStmt->execute(['id' => $targetPostId]);
            $message = "<div class='alert alert-success'>Post successfully deleted from the platform.</div>";
        } elseif ($action == 'approve') {
            $appStmt = $conn->prepare("UPDATE POSTS SET ISFLAGGED = 0 WHERE POSTID = :id");
            $appStmt->execute(['id' => $targetPostId]);
            $message = "<div class='alert alert-info'>Post approved. The flag has been cleared.</div>";
        }
    }

    $query = "SELECT P.POSTID, P.CONTENT, P.CREATEDAT, U.USERNAME 
              FROM POSTS P 
              JOIN USERS U ON P.USERID = U.USERID 
              WHERE P.ISFLAGGED = 1 
              ORDER BY P.CREATEDAT ASC";
    $stmt = $conn->query($query);
    $flaggedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="style.css" rel="stylesheet">
        <title>CheckM8 — Moderation Queue</title>
        <style>
            .table-dark-purple {
                color: #E8E4ED;
                background-color: #261F33;
            }
            .table-dark-purple th {
                background-color: #4A3563;
                color: #FFFFFF;
                border-color: #6B4C8A;
            }
            .table-dark-purple td {
                border-color: #4A3563;
                vertical-align: middle;
            }
        </style>
    </head>
    <body>
        <div class="container mt-5 mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #E8E4ED;">🛡️ Moderation Queue</h2>
                <a href="admin_dashboard.php" class="btn accent-box px-4 py-2">Back to Dashboard</a>
            </div>

            <?php echo $message; ?>

            <div class="content-box p-4">
                <?php if (empty($flaggedPosts)): ?>
                    <div class="text-center p-5">
                        <h4 style="color: #9B7EBD;">All clear! There are no reported posts in the queue.</h4>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark-purple table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Post ID</th>
                                    <th style="width: 15%;">Author</th>
                                    <th style="width: 45%;">Reported Content</th>
                                    <th style="width: 15%;">Date Posted</th>
                                    <th style="width: 15%;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($flaggedPosts as $post): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($post['POSTID']); ?></td>
                                        <td><strong>@<?php echo htmlspecialchars($post['USERNAME']); ?></strong></td>
                                        <td>
                                            <div style="max-height: 100px; overflow-y: auto; color: #E8E4ED;">
                                                <?php echo htmlspecialchars($post['CONTENT']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('M d, g:i A', strtotime($post['CREATEDAT'])); ?></td>
                                        <td class="text-end">
                                            <form method="POST" action="admin_moderation.php" class="d-inline">
                                                <input type="hidden" name="target_postid" value="<?php echo $post['POSTID']; ?>">
                                                
                                                <button type="submit" name="admin_action" value="approve" class="btn btn-sm btn-success me-1 mb-1">Approve</button>
                                                <button type="submit" name="admin_action" value="delete" class="btn btn-sm btn-outline-danger mb-1" onclick="return confirm('Permanently delete this post?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>