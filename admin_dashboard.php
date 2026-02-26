<?php
session_start();

if (!isset($_SESSION['USERID']) || !isset($_SESSION['ISADMIN']) || $_SESSION['ISADMIN'] != 1) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="style.css" rel="stylesheet">
        <title>CheckM8 — Admin Control Panel</title>
        
        <style>
            .admin-card {
                border: 2px solid #4A3563;
                transition: transform 0.2s, border-color 0.2s;
                cursor: pointer;
                text-decoration: none;
                display: block;
            }
            .admin-card:hover {
                transform: translateY(-5px);
                border-color: #9B7EBD;
                text-decoration: none;
            }
            .admin-icon {
                font-size: 3rem;
                margin-bottom: 15px;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row m-3">
                
                <div class="col-3 content-box m-3 p-4 d-flex flex-column align-items-center" style="height: 85vh;">
                    <div class="accent-box rounded-circle d-flex justify-content-center align-items-center mb-3 mt-4" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold; border: 3px solid #E8E4ED;">
                        👑
                    </div>
                    <h4 class="text-center mb-0" style="color: #E8E4ED;">Administrator</h4>
                    <p class="mb-4" style="color: #9B7EBD;">@<?php echo htmlspecialchars($_SESSION['USERNAME']); ?></p>
                    
                    <hr class="w-100" style="background-color: #4A3563; height: 2px;">
                    
                    <p class="text-center mt-3" style="color: #E8E4ED; font-size: 0.9rem;">
                        Hello Admin
                    </p>

                    <a href="index.php" class="btn mt-auto mb-3 content-box w-100 d-flex justify-content-center align-items-center" style="height: 50px; font-size: 1.1rem; border: 1px solid #9B7EBD; color: #E8E4ED;">
                        Return to Main Feed
                    </a>
                    
                    <a href="logout.php" class="btn mb-3 accent-box w-100 d-flex justify-content-center align-items-center" style="height: 50px; font-size: 1.2rem;">
                        LOGOUT
                    </a>
                </div>
                
                <div class="col m-3 scroll-box" style="max-height: 85vh;">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                        <h2 style="color: #E8E4ED;">Admin Control Panel</h2>
                        <span class="badge" style="background-color: #6B4C8A; font-size: 1rem; padding: 10px 15px;">System Status: ONLINE</span>
                    </div>
                    
                    <div class="row g-4">
                        
                        <div class="col-md-6">
                            <a href="admin_moderation.php" class="content-box p-4 text-center admin-card h-100">
                                <div class="admin-icon">🛡️</div>
                                <h4 style="color: #E8E4ED;">Post Moderation</h4>
                                <p style="color: #9B7EBD; font-size: 0.95rem;">Post Moderation.</p>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="admin_users.php" class="content-box p-4 text-center admin-card h-100">
                                <div class="admin-icon">👥</div>
                                <h4 style="color: #E8E4ED;">Manage Users</h4>
                                <p style="color: #9B7EBD; font-size: 0.95rem;">Manage Users</p>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="admin_tournaments.php" class="content-box p-4 text-center admin-card h-100">
                                <div class="admin-icon">🏆</div>
                                <h4 style="color: #E8E4ED;">Setup Tournaments</h4>
                                <p style="color: #9B7EBD; font-size: 0.95rem;">Setup Tournaments</p>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="admin_stats.php" class="content-box p-4 text-center admin-card h-100">
                                <div class="admin-icon">📊</div>
                                <h4 style="color: #E8E4ED;">Website Statistics</h4>
                                <p style="color: #9B7EBD; font-size: 0.95rem;">Website Statistics</p>
                            </a>
                        </div>

                        <div class="col-md-12">
                            <a href="admin_create.php" class="content-box p-4 text-center admin-card" style="background-color: rgba(107, 76, 138, 0.2);">
                                <div class="admin-icon">🔑</div>
                                <h4 style="color: #E8E4ED;">Create New Admin</h4>
                                <p style="color: #9B7EBD; font-size: 0.95rem;">Create New Admin</p>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>