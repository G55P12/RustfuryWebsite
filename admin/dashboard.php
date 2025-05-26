<?php
session_start();

if (!isset($_SESSION['authorized']) || !$_SESSION['authorized']) {
    header('Location: includes/login.php');
    exit;
}

?>

<link rel="stylesheet" href="assets/css/dashboard.css">

    <main class="hero">
        <div class="panel-container">
            <h1 class="panel-heading">Site Panels:</h1>
            <div class="panel-buttons">
                <a href="panels/site-settings.php"><button class="btn btn-primary">Site Info/Images</button></a>
                <a href="panels/server-settings.php"><button class="btn btn-primary">Servers</button></a>
                <a href="panels/store-settings.php"><button class="btn btn-primary">Store</button></a>
                <a href="panels/social-settings.php"><button class="btn btn-primary">Socials</button></a>
                <a href="panels/rules-settings.php"><button class="btn btn-primary">Rules</button></a>
                <a href="panels/staff-settings.php"><button class="btn btn-primary">Staff</button></a>
                <a href="panels/faq-settings.php"><button class="btn btn-primary">FAQ</button></a>
                <a href="panels/nav-settings.php"><button class="btn btn-primary">Addon Nav</button></a>
            </div>

            <h1 class="panel-heading">Addon Panels:</h1>

            <div class="panel-buttons">
                <?php if (file_exists('../extstats/admin/dashboard.php')) { ?>
                    <a href="../extstats/admin/dashboard.php"><button class="btn btn-primary">Extended Stats</button></a>
                <?php } elseif (file_exists('../stats/admin/dashboard.php')) { ?>
                    <a href="../stats/admin/dashboard.php"><button class="btn btn-primary">Simple Stats</button></a>
                <?php } else { ?> 
                    <button class="btn btn-disabled">Stats Addon</button></a>
                <?php } ?>
                
                <?php if (file_exists('../link/admin/dashboard.php')) { ?>
                    <a href="../link/admin/dashboard.php"><button class="btn btn-primary">Account Linking</button></a>
                <?php } else { ?>
                    <button class="btn btn-disabled">Account Linking</button></a>
                <?php } ?>

                <?php if (file_exists('../servers/admin/dashboard.php')) { ?>
                    <a href="../servers/admin/dashboard.php"><button class="btn btn-secondary">Server Pages</button></a>
                <?php } else { ?>
                    <button class="btn btn-disabled">Server Pages</button></a>
                <?php } ?>

                <?php if (file_exists('../vote/admin/dashboard.php')) { ?>
                    <a href="../vote/admin/dashboard.php"><button class="btn btn-primary">Vote Links</button></a>
                <?php } else { ?>
                    <button class="btn btn-disabled">Vote Links</button></a>
                <?php } ?>

                <?php if (file_exists('../donate/admin/dashboard.php')) { ?>
                    <a href="../donate/admin/dashboard.php"><button class="btn btn-primary">Donations Info</button></a>
                <?php } else { ?>
                    <button class="btn btn-disabled">Donations Info</button></a>
                <?php } ?>
                
            </div>
        </div>
    </main>