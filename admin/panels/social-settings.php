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

$socialLinks = ['steam', 'youtube', 'twitch', 'twitter', 'instagram', 'facebook', 'vk', 'tiktok'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newConfig['discord']['inviteLink'] = $_POST['discord_inviteLink'] ?? $config['discord']['inviteLink'];
    $newConfig['discord']['text'] = $_POST['discord_text'] ?? $config['discord']['text'];
    $newConfig['discord']['serverId'] = $_POST['discord_serverId'] ?? $config['discord']['serverId'];
    
    if (isset($_POST['socials_enabled'])) {
        $newEnabledValue = $_POST['socials_enabled'];
        if ($config['socials']['enabled'] !== $newEnabledValue) {
            $config['socials']['enabled'] = $newEnabledValue;
            $changesMade = true;
        }
    }

    foreach ($socialLinks as $key) {
        if (isset($_POST[$key])) {
            $newValue = $_POST[$key];
            if ($config['socials']['links'][$key] !== $newValue) {
                $config['socials']['links'][$key] = $newValue;
                $changesMade = true;
            }
        }
    }

    $configContent = file_get_contents($configFilePath);

    $escapedEnabled = addslashes($config['socials']['enabled']);
    $configContent = preg_replace(
        "/(['\"]socials['\"]\\s*=>\\s*\\[.*?['\"]enabled['\"]\\s*=>\\s*['\"]).*?(['\"])/s",
        "\\1$escapedEnabled\\2",
        $configContent
    );

    foreach ($socialLinks as $key) {
        $escapedValue = addslashes($config['socials']['links'][$key]);
        $pattern = "/(['\"]" . preg_quote($key, '/') . "['\"]\\s*=>\\s*['\"]).*?(['\"])/";
        $replacement = "\\1$escapedValue\\2";
        $configContent = preg_replace($pattern, $replacement, $configContent);
    }

    foreach ($newConfig['discord'] as $key => $value) {
        $pattern = "/('discord'\s*=>\s*\[.*?\s*'$key'\s*=>\s*)'.*?'/s";
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
                <h2>Discord Settings</h2>
                <p>If Invite Link is set, a button in the navigation will show up as a Call-To-Action for users to join your discord server. It will disappear if you remove the link.<br>The link needs to start with the protocol (https://).</p>
                <p>To show your Discord player count, you need to include your discord server ID.<br> This is only required if you want show the amount of people on your Discord server. To get the server ID:</p>
                <p>1. Go to your Server Settings. Click on Widget.<br> 2. Enable Server Widget at the top. <br> 3. Copy the server ID shown in the box below into serverId</p>
                <div class="form-group">
                    <label class="form-label">Invite Link:</label>
                    <input type="text" id="discord_inviteLink" name="discord_inviteLink" value="<?= htmlspecialchars($config['discord']['inviteLink']) ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Text:</label>
                    <input type="text" id ="discord_text" name="discord_text" value="<?= htmlspecialchars($config['discord']['text']) ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Server ID:</label>
                    <input type="text" id="discord_serverId" name="discord_serverId" value="<?= htmlspecialchars($config['discord']['serverId']) ?>" class="form-input">
                </div>
                <button class="btn btn-primary" type="submit">Save Settings</button>
            </div>

            <div class="section">
                <h2>Social Links</h2>
                <p>Social links are only displayed if not empty. If entered, they will show up with an icon for the specific platform in the nva bar.<br> All links need to start with their protocol (https://)</p>
                <div class="form-group">
                    <label class="form-label">Enabled</label>
                    <select class="form-input" name="socials_enabled">
                        <option value="yes" <?= $config['socials']['enabled'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                        <option value="no" <?= $config['socials']['enabled'] === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <?php foreach ($socialLinks as $key): ?>
                    <div class="form-group">
                        <label class="form-label"><?= ucfirst($key) ?> Link</label>
                        <input type="url" name="<?= $key ?>" class="form-input" value="<?= htmlspecialchars($config['socials']['links'][$key]) ?>">
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
