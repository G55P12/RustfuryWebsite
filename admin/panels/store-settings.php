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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newConfig['store']['enabled'] = $_POST['enabled'] ?? $config['store']['enabled'];
    $newConfig['store']['heading'] = $_POST['heading'] ?? $config['store']['heading'];
    $newConfig['store']['navigation'] = $_POST['navigation'] ?? $config['store']['navigation'];
    $newConfig['store']['button'] = $_POST['button'] ?? $config['store']['button'];
    $newConfig['store']['message'] = $_POST['message'] ?? $config['store']['message'];
    $newConfig['store']['link'] = $_POST['link'] ?? $config['store']['link'];

    $configContent = file_get_contents($configFilePath);

    foreach ($newConfig['store'] as $key => $value) {
        $pattern = "/('store'\s*=>\s*\[.*?\s*'$key'\s*=>\s*)'.*?'/s";
        $replacement = "\${1}'" . addslashes($value) . "'";
        $configContent = preg_replace($pattern, $replacement, $configContent, 1);
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

        <form method="POST">
            <div class="section">
                <h2>Store Settings</h2>
                <p>If the store is enabled, it will show a link and a text to your shop to buy VIP packages or kits from. Set store enabled to yes or no to show or hide everything. The link needs to start with the protocol (https://)</p>
                <div class="form-group">
                    <label class="form-label">Enabled</label>
                    <select class="form-input" name="enabled">
                        <option value="yes" <?= $config['store']['enabled'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                        <option value="no" <?= $config['store']['enabled'] === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Heading</label>
                    <input type="text" name="heading" class="form-input" value="<?= htmlspecialchars($config['store']['heading']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Navigation</label>
                    <input type="text" name="navigation" class="form-input" value="<?= htmlspecialchars($config['store']['navigation']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button" class="form-input" value="<?= htmlspecialchars($config['store']['button']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-input" rows="5"><?= htmlspecialchars($config['store']['message']) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Link</label>
                    <input type="url" name="link" class="form-input" value="<?= htmlspecialchars($config['store']['link']) ?>">
                </div>
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
