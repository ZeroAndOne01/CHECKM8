<?php
session_start();

if (!isset($_SESSION['USERID'])) {
    header("Location: index.php");
    exit();
}

$serverName = "ELIJAH\\SQLEXPRESS"; 
$database = "CHECKM8";
$currentBalance = "0.00";
$message = "";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database;ConnectionPooling=0;TrustServerCertificate=1", "", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coin_amount'])) {
        $amount = floatval($_POST['coin_amount']);
        
        $validAmounts = [500.00, 1200.00, 6500.00, 14000.00];
        
        if (in_array($amount, $validAmounts)) {
            $updateStmt = $conn->prepare("UPDATE USERS SET BALANCE = BALANCE + :AMOUNT WHERE USERID = :USERID");
            $updateStmt->execute([
                'AMOUNT' => $amount,
                'USERID' => $_SESSION['USERID']
            ]);
            
            $message = "<div class='alert alert-success text-center mb-4' style='background-color: #4A3563; color: #E8E4ED; border: 1px solid #9B7EBD;'>
                            <strong>Success!</strong> " . number_format($amount) . " coins have been added to your treasury.
                        </div>";
        } else {
            $message = "<div class='alert alert-danger text-center mb-4'>Invalid transaction amount.</div>";
        }
    }

    $stmtBalance = $conn->prepare("SELECT BALANCE FROM USERS WHERE USERID = :USERID");
    $stmtBalance->execute(['USERID' => $_SESSION['USERID']]);
    $userRow = $stmtBalance->fetch(PDO::FETCH_ASSOC);
    if ($userRow) {
        $currentBalance = number_format($userRow['BALANCE'], 2);
    }

} catch(PDOException $e) {
    $message = "<div class='alert alert-danger text-center mb-4'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="style.css" rel="stylesheet">
        <title>CheckM8 — Top Up</title>
        <style>
            .package-card {
                transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
                border: 2px solid #4A3563;
                cursor: default;
            }
            .package-card:hover {
                transform: translateY(-5px);
                border-color: #9B7EBD;
                box-shadow: 0 10px 30px rgba(155, 126, 189, 0.2);
            }
            .best-value-badge {
                position: absolute;
                top: -15px;
                right: -15px;
                background: linear-gradient(135deg, #FFD700, #FDB931);
                color: #1A1625;
                font-weight: 800;
                padding: 6px 18px;
                border-radius: 20px;
                font-size: 0.85rem;
                transform: rotate(12deg);
                box-shadow: 0 4px 15px rgba(0,0,0,0.4);
                letter-spacing: 1px;
            }
            .coin-icon {
                font-size: 3.5rem;
                color: #9B7EBD;
                margin-bottom: 10px;
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <div class="container mt-4 mb-5">
            
            <div class="row mb-4 align-items-center">
                <div class="col-auto">
                    <a href="index.php" class="btn accent-box rounded-pill px-4 py-2" style="font-weight: 600;">← Back to Board</a>
                </div>
                <div class="col text-end">
                    <h5 class="m-0" style="color: #E8E4ED;">Current Balance: <span style="color: #9B7EBD; font-weight: bold;"><?php echo htmlspecialchars($currentBalance); ?></span></h5>
                </div>
            </div>

            <div class="content-box p-5 text-center mb-4 border-0" style="background: linear-gradient(180deg, rgba(38,31,51,0.9) 0%, rgba(26,22,37,0) 100%);">
                <div class="logo-circle mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem; border-width: 2px;">💎</div>
                <h1 style="font-weight: 700; letter-spacing: 1px;">Top Up Your Treasury</h1>
                <p style="color: #B49FDC; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                    Acquire CheckM8 coins to enter premium tournaments, tip your favorite players, and unlock exclusive board themes.
                </p>
            </div>

            <?php echo $message; ?>

            <div class="row g-4 justify-content-center">
                
                <div class="col-md-6 col-lg-3">
                    <div class="content-box p-4 text-center package-card h-100 d-flex flex-column">
                        <span class="coin-icon">♙</span>
                        <h4 style="color: #E8E4ED; font-weight: 600;">Pawn Stash</h4>
                        <h2 class="my-3" style="color: #FFFFFF; font-weight: 800;">500 <small style="font-size: 1rem; color: #9B7EBD;">Coins</small></h2>
                        <form method="POST" action="top_up.php" class="mt-auto">
                            <input type="hidden" name="coin_amount" value="500.00">
                            <button type="submit" class="btn accent-box w-100 rounded-pill py-2" style="font-size: 1.1rem; font-weight: bold;">Php 149.99</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="content-box p-4 text-center package-card h-100 d-flex flex-column">
                        <span class="coin-icon">♘</span>
                        <h4 style="color: #E8E4ED; font-weight: 600;">Knight's Bounty</h4>
                        <h2 class="my-3" style="color: #FFFFFF; font-weight: 800;">1,200 <small style="font-size: 1rem; color: #9B7EBD;">Coins</small></h2>
                        <form method="POST" action="top_up.php" class="mt-auto">
                            <input type="hidden" name="coin_amount" value="1200.00">
                            <button type="submit" class="btn accent-box w-100 rounded-pill py-2" style="font-size: 1.1rem; font-weight: bold;">Php 499.99</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="content-box p-4 text-center package-card h-100 position-relative d-flex flex-column" style="border-color: #7B52AB; transform: scale(1.05); z-index: 5;">
                        <div class="best-value-badge">BEST VALUE</div>
                        <span class="coin-icon" style="color: #E8E4ED; text-shadow: 0 0 15px rgba(155, 126, 189, 0.8);">♕</span>
                        <h4 style="color: #E8E4ED; font-weight: 600;">Queen's Vault</h4>
                        <h2 class="my-3" style="color: #FFFFFF; font-weight: 800;">6,500 <small style="font-size: 1rem; color: #9B7EBD;">Coins</small></h2>
                        <form method="POST" action="top_up.php" class="mt-auto">
                            <input type="hidden" name="coin_amount" value="6500.00">
                            <button type="submit" class="btn accent-box w-100 rounded-pill py-3" style="font-size: 1.2rem; font-weight: bold; background-color: #9B7EBD;">Php 999.99</button>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="content-box p-4 text-center package-card h-100 d-flex flex-column">
                        <span class="coin-icon">♔</span>
                        <h4 style="color: #E8E4ED; font-weight: 600;">King's Treasury</h4>
                        <h2 class="my-3" style="color: #FFFFFF; font-weight: 800;">14,000 <small style="font-size: 1rem; color: #9B7EBD;">Coins</small></h2>
                        <form method="POST" action="top_up.php" class="mt-auto">
                            <input type="hidden" name="coin_amount" value="14000.00">
                            <button type="submit" class="btn accent-box w-100 rounded-pill py-2" style="font-size: 1.1rem; font-weight: bold;">Php 1999.99</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>