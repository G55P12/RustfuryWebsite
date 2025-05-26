<?php
session_start();

$adminConfig = include('../config.php');
$adminList = $adminConfig['admins'];

$isAdmin = false;
$userInfo = null;

if (isset($_SESSION['steamid'])) { 
    require '../../steamauth/userInfo.php';

    $steamID = $_SESSION['steamid'];

    $userInfo = [
        'steamid' => $_SESSION['steamid'],
        'username' => $steamprofile['personaname'],
        'avatar' => $steamprofile['avatarfull']
    ];

    if (in_array($steamID, $adminList)) {
        $isAdmin = true;
    }
}

if ($isAdmin && isset($_POST['confirm_login'])) {
    $_SESSION['authorized'] = true;
    header('Location: ../dashboard.php');
    exit();
}
?>

<link rel="stylesheet" href="../assets/css/login.css">

<body>
    <div class="login-container">
        <div class="user-info-box">
            <?php if ($userInfo)  { ?>
                <img src="<?= htmlspecialchars($userInfo['avatar']); ?>" alt="User Avatar">
                <h2>User: <?= htmlspecialchars($userInfo['username']); ?></h2>
                <p>Steam ID: <?= htmlspecialchars($userInfo['steamid']); ?></p>
                <p style="color: <?= $isAdmin ? 'green' : 'red'; ?>">
                    <?= $isAdmin ? "✅ SteamID Authorized!" : "❌ SteamID not Authorized." ?>
                </p>
            <?php } else { ?>
                <h2>Login Required</h2>
                <p>You must log in via Steam before accessing the admin panel.</p>
            <?php } ?>
        </div>

        <div class="login-button">
            <?php if ($isAdmin) { ?>
                <form method="POST">
                    <button type="submit" name="confirm_login" class="btn btn-primary">Confirm Admin Login</button>
                </form>
            <?php } ?>
        </div>
    </div>
</body>
