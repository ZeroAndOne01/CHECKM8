<?php
session_start();

if (!isset($_SESSION['USERID'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'])) {
    
    $serverName = "ELIJAH\\SQLEXPRESS"; 
    $database = "CHECKM8";
    $targetPostId = $_POST['post_id'];

    try {
        $conn = new PDO("sqlsrv:server=$serverName;Database=$database;ConnectionPooling=0;TrustServerCertificate=1", "", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $flagStmt = $conn->prepare("UPDATE POSTS SET ISFLAGGED = 1 WHERE POSTID = :id");
        $flagStmt->execute(['id' => $targetPostId]);

    } catch(PDOException $e) {
        error_log("Flagging Error: " . $e->getMessage());
    }
}

header("Location: index.php");
exit();
?>