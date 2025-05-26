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
    $configContent = file_get_contents($configFilePath);
    $pattern = "/'servers'\s*=>\s*\[\s*(?:\[[^\]]*\]\s*,?\s*)*\]/s";
    $configContent = preg_replace($pattern, "'servers' => []", $configContent, 1);

    file_put_contents($configFilePath, $configContent);

    if (isset($_POST['servers']) && is_array($_POST['servers'])) {
        $newConfig['servers'] = array_values(array_map(function ($server) {
            return [
                'ip' => $server['ip'] ?? '',
                'port' => (int)($server['port'] ?? 0),
                'queryPort' => (int)($server['queryPort'] ?? 0),
                'storeLink' => $server['storeLink'] ?? '',
                'battlemetricsId' => (int)($server['battlemetricsId'] ?? 0),
                'battlemetricsLink' => $server['battlemetricsLink'] ?? '',
            ];
        }, $_POST['servers']));
    } else {
        $config['servers'] = [];
    }

    $serversSerialized = "'servers' => [\n";
    foreach ($_POST['servers'] as $server) {
        $serversSerialized .= "        [\n";
        foreach ($server as $key => $value) {
            $escapedValue = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
            $serversSerialized .= "            '$key' => $escapedValue,\n";
        }
        $serversSerialized .= "        ],\n";
    }
    $serversSerialized .= "    ]";

    $configContent = preg_replace($pattern, $serversSerialized, $configContent, 1);

    $newConfig['connectButton'] = $_POST['connectButton'] ?? $config['connectButton'];
    $newConfig['api'] = $_POST['api'] ?? $config['api'];
    $newConfig['lastWiped']['enabled'] = $_POST['lastWiped_enabled'] ?? $config['lastWiped']['enabled'];
    $newConfig['lastWiped']['text'] = $_POST['lastWiped_text'] ?? $config['lastWiped']['text'];
    $newConfig['lastWiped']['hoursPassed'] = $_POST['lastWiped_hoursPassed'] ?? $config['lastWiped']['hoursPassed'];
    
    foreach ([
        'connectButton' => $newConfig['connectButton'],
        'api' => $newConfig['api'],
        'lastWiped' => $newConfig['lastWiped']
    ] as $section => $values) {

        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if ($key === 'hoursPassed') {
                    $pattern = "/'hoursPassed'\s*=>\s*(?:(['\"])(.*?)\\1|\d+)/";
                    $intValue = (int) $value;
                    $replacement = "'hoursPassed' => $intValue";
                    $configContent = preg_replace($pattern, $replacement, $configContent, 1);
                } else {
                    $pattern = "/'" . $key . "'\s*=>\s*('.*?'|\d+)/";
                    $replacement = "'" . $key . "' => '" . addslashes($value) . "'";
                    $configContent = preg_replace($pattern, $replacement, $configContent, 1);
                }
            }
        } else {
            $pattern = "/'" . $section . "'\s*=>\s*'.*?'/";
            $replacement = "'" . $section . "' => '" . addslashes($values) . "'";
            $configContent = preg_replace($pattern, $replacement, $configContent, 1);
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
                <h2>Server Cards</h2>
                <p>You can add a list of available servers here. For each new server you want to add, use the 'Add Server' button below.</p>
                <p>If you do not set the BattleMetrics link, the button will not be shown. Store link will display a button (if set) for a link to buy VIP status for that specific server.<br>Links need to start with the protocol (https://)</p>
                <p>IMPORTANT: By default the BattleMetrics API is activated. To get data from the battlemetrics API you need to set the battlemetricsId of your server.<br>It is located in the URL in your browser if you view the server on BattleMetrics.</p>
                    <div id="server-cards" class="server-cards-container">
                        <?php foreach ($config['servers'] as $index => $server): ?>
                            <div class="server-card" id="server-card-<?= $index; ?>">
                                <h3>Server <?= $index + 1; ?></h3>
                                <div class="form-group">
                                    <label class="form-label">Server IP:</label>
                                    <input class="form-input" type="text" name="servers[<?= $index; ?>][ip]" value="<?= htmlspecialchars($server['ip']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Server Port:</label>
                                    <input class="form-input" type="text" name="servers[<?= $index; ?>][port]" value="<?= htmlspecialchars($server['port']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Query Port:</label>
                                    <input class="form-input" type="text" name="servers[<?= $index; ?>][queryPort]" value="<?= htmlspecialchars($server['queryPort']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Store Link:</label>
                                    <input class="form-input" type="text" name="servers[<?= $index; ?>][storeLink]" value="<?= htmlspecialchars($server['storeLink']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Battlemetrics ID:</label>
                                    <input class="form-input" type="text" name="servers[<?= $index; ?>][battlemetricsId]" value="<?= htmlspecialchars($server['battlemetricsId']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Battlemetrics Link:</label>
                                    <input class="form-input" type="text" name="servers[<?= $index; ?>][battlemetricsLink]" value="<?= htmlspecialchars($server['battlemetricsLink']); ?>">
                                </div>
                                <button class="btn btn-danger" type="button" onclick="removeServer(<?= $index; ?>)">Remove Server</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <button class="btn btn-secondary" type="button" id="add-server-button">Add Server</button>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>

            <div class="section">
                <h2>Player Connection</h2>
                <div class="form-group">
                    <label class="form-label">Connect Button:</label>
                    <p>You have two different options to join the server from the server cards. The first way is with the direct connect button, which connects the player to your server through Steam.<br> If the Steam is not available you can use the second method - the pop up - filled with instructions to join the server via the ingame console.</p>
                    <select name="connectButton" class="form-input">
                        <option value="popup" <?= $config['connectButton'] === 'popup' ? 'selected' : '' ?>>Popup</option>
                        <option value="direct" <?= $config['connectButton'] === 'direct' ? 'selected' : '' ?>>Direct</option>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>

            <div class="section">
                <h2>Server Information</h2>
                <div class="form-group">
                    <label class="form-label">API:</label>
                    </p>This website has 2 different ways of gaining information for your server. One is the BattleMetrics API, and the other is SourceQuery.</p>
                    <p>SourceQuery is directly connecting to your server (and requires the sockets extension and open ports if you use it).<br> If you want to support tags (as shown ingame in the serverlist, the blue information) you need to use the SourceQuery option. </p>
                    <p>BattleMetrics supports a maximum of 15 servers and pulls its server information directly from the BattleMetrics API. </p>
                    <select name="api" class="form-input">
                        <option value="battlemetrics" <?= $config['api'] === 'battlemetrics' ? 'selected' : '' ?>>Battlemetrics</option>
                        <option value="sourcequery" <?= $config['api'] === 'sourcequery' ? 'selected' : '' ?>>SourceQuery</option>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>

            <div class="section">
                <h2>Last Wipe Banner</h2>
                <div class="form-group">
                    <p>If this section is enabled, server cards will show a just wiped banner if they have recently been wiped. Set the banner text and hours passed since wipe until the banner is hidden again.</p>
                    <label class="form-label">Enabled:</label>
                    <select class="form-input" name="lastWiped_enabled">
                        <option value="yes" <?= $config['lastWiped']['enabled'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                        <option value="no" <?= $config['lastWiped']['enabled'] === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Text:</label>
                    <input class="form-input" type="text" name="lastWiped_text" value="<?= htmlspecialchars($config['lastWiped']['text']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Hours Passed:</label>
                    <input class="form-input" type="number" name="lastWiped_hoursPassed" value="<?= htmlspecialchars($config['lastWiped']['hoursPassed']); ?>">
                </div>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>

    </div>

    <script>
        document.getElementById('add-server-button').addEventListener('click', () => {
            const serverCards = document.getElementById('server-cards');
            const newIndex = serverCards.children.length;

            const card = document.createElement('div');
            card.className = 'server-card';
            card.innerHTML = `
                <h3>Server ${newIndex + 1}</h3>
                <div class="form-group">
                    <label class="form-label">Server IP:</label>
                    <input class="form-input" type="text" name="servers[${newIndex}][ip]">
                </div>
                <div class="form-group">
                    <label class="form-label">Server Port:</label>
                    <input class="form-input" type="text" name="servers[${newIndex}][port]">
                </div>
                <div class="form-group">
                    <label class="form-label">Query Port:</label>
                    <input class="form-input" type="text" name="servers[${newIndex}][queryPort]">
                </div>
                <div class="form-group">
                    <label class="form-label">Store Link:</label>
                    <input class="form-input" type="text" name="servers[${newIndex}][storeLink]">
                </div>
                <div class="form-group">
                    <label class="form-label">Battlemetrics ID:</label>
                    <input class="form-input" type="text" name="servers[${newIndex}][battlemetricsId]">
                </div>
                <div class="form-group">
                    <label class="form-label">Battlemetrics Link:</label>
                    <input class="form-input" type="text" name="servers[${newIndex}][battlemetricsLink]">
                </div>
            `;

            serverCards.appendChild(card);
        });

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

        function removeServer(index) {
            updateIframeHeight();
            
            const card = document.getElementById(`server-card-${index}`);
            if (card) {
                card.remove();
            }
        }
    </script>
    
</body>
