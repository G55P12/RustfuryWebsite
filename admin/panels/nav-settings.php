<?php
session_start();

if (!isset($_SESSION['authorized']) || !$_SESSION['authorized']) {
    header('Location: ../../admin.php');
    exit;
}

$configFilePath = '../../config.php';
if (function_exists('opcache_invalidate')) {
    opcache_invalidate($configFilePath, true);
}
$config = include $configFilePath;

$allowedKeys = ['serverpages', 'statistics', 'extendedstats', 'link', 'voting', 'donate', 'news', 'reports', 'banslist'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($allowedKeys as $key) {
        if (isset($_POST[$key])) {
            $newConfig[$key]['navigation'] = $_POST[$key];
        }
    }

    $configContent = file_get_contents($configFilePath);

    foreach ($allowedKeys as $key) {
        if (isset($newConfig[$key]['navigation'])) {
            $pattern = "/'" . $key . "'\\s*=>\\s*\\[.*?'navigation'\\s*=>\\s*'.*?'/s";
            $replacement = "'navigation' => '" . addslashes($newConfig[$key]['navigation']) . "'";
            $configContent = preg_replace_callback(
                "/('" . $key . "'\\s*=>\\s*\\[.*?\\])/s",
                function ($matches) use ($replacement) {
                    return preg_replace("/'navigation'\\s*=>\\s*'.*?'/", $replacement, $matches[1]);
                },
                $configContent
            );
        }
    }

    if (file_put_contents($configFilePath, $configContent) === false) {
        $_SESSION['message'] = ['type' => 'error', 'text' => "Failed to write new settings."];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['message'] = ['type' => 'success', 'text' => "Settings updated successfully!"];
    }

    sleep(2);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<link rel="stylesheet" href="../assets/css/settings.css">

<body>
    
    <a href="../dashboard.php">
        <button class="btn btn-danger">< Main Dashboard</button>
    </a>

    <div class="container">

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?= $_SESSION['message']['type'] == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                <?= $_SESSION['message']['text']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="post">
            <div class="section">
                <h2>Addon Navigation Labels</h2>
                <p>The navigation settings below are for additional site addons. Each will automatically show in the navigation bar once the required addon is installed and detected.</p>
                <?php foreach ($allowedKeys as $key): ?>
                    <div class="form-group">
                        <label class="form-label"><?= ucfirst($key); ?></label>
                        <input
                            class="form-input"
                            type="text"
                            id="<?= $key; ?>-navigation"
                            name="<?= $key; ?>"
                            value="<?= htmlspecialchars($config[$key]['navigation'] ?? ''); ?>"
                        >
                    </div>
                <?php endforeach; ?>
                <button class="btn btn-primary" type="submit">Save Settings</button>
            </div>
        </form>

    </div>
            
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const alertBox = document.querySelector(".alert");

            if (alertBox) {
                setTimeout(() => {
                    alertBox.classList.add("fade-out");
                    setTimeout(() => {
                        alertBox.style.display = "none";
                    }, 1000);
                }, 3000);
            }
        });
    </script>

</body>
