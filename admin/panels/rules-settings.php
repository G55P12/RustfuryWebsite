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

    $newConfig['rules']['enabled'] = $_POST['rules_enabled'] ?? $config['rules']['enabled'];
    $newConfig['rules']['heading'] = $_POST['rules_heading'] ?? $config['rules']['heading'];
    $newConfig['rules']['navigation'] = $_POST['rules_navigation'] ?? $config['rules']['navigation'];

    if (isset($_POST['rule']) && is_array($_POST['rule'])) {
        $newConfig['rule'] = array_values(array_map(function ($rule) {
            return [
                'title' => $rule['title'] ?? '',
                'text' => $rule['text'] ?? '',
            ];
        }, $_POST['rule']));
    } else {
        $newConfig['rules']['rules'] = [];
    }

    $configContent = file_get_contents($configFilePath);

    foreach ($newConfig['rules'] as $key => $value) {
        $pattern = "/('rules'\s*=>\s*\[.*?\s*'$key'\s*=>\s*)'.*?'/s";
        $replacement = "\${1}'" . addslashes($value) . "'";
        $configContent = preg_replace($pattern, $replacement, $configContent, 1);
    }

    $rulesSerialized = "'rules' => [\n\n";
    foreach ($newConfig['rule'] as $rule) {
        $rulesSerialized .= "            [\n";
        foreach ($rule as $key => $value) {
            $escapedValue = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
            $rulesSerialized .= "                '$key' => $escapedValue,\n";
        }
        $rulesSerialized .= "            ],\n";
    }
    $rulesSerialized .= "\n        ]";

    $pattern = "/'rules'\s*=>\s*\[\s*(?:\[[^\]]*\]\s*,?\s*)*\]/s";
    $configContent = preg_replace($pattern, $rulesSerialized, $configContent, 2);

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
                <h2>Rules Settings</h2>
                <div class="form-group">
                    <p>If enabled, the rules section will show near the middle of the page. You can also set the page heading and navigation bar text.</p>
                    <label class="form-label">Enabled:</label>
                    <select class="form-input" name="rules_enabled">
                        <option value="yes" <?= $config['rules']['enabled'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                        <option value="no" <?= $config['rules']['enabled'] === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Heading:</label>
                    <input class="form-input" type="text" name="rules_heading" value="<?= htmlspecialchars($config['rules']['heading']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Navigation:</label>
                    <input class="form-input" type="text" name="rules_navigation" value="<?= htmlspecialchars($config['rules']['navigation']); ?>">
                </div>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>

            <div class="section">
                <h2>Rules</h2>
                <p>You can add a list of rules here. For each new rule you want to add, use the 'Add Rule' button below. Feel free to add any new rules you feel applicable to your server, they will automatically show up on the page. If you want a new line within your text, you can use a < br > (no spaces - HTML for new line) to start a new line.</p>
                <div id="rule-cards" class="rules-cards-container">
                    <?php foreach ($config['rules']['rules'] as $index => $rule): ?>
                        <div class="rule-card" id="rule-card-<?= $index; ?>">
                            <div class="form-group">
                                <label class="form-label">Title:</label>
                                <input class="form-input" type="text" name="rule[<?= $index; ?>][title]" value="<?= htmlspecialchars($rule['title']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Text:</label>
                                <textarea class="form-input" name="rule[<?= $index; ?>][text]" rows="5"><?= htmlspecialchars($rule['text']) ?></textarea>
                            </div>
                            <input type="hidden" name="rule[<?= $index; ?>][remove]" value="0">
                            <button class="btn btn-danger" type="button" onclick="removeRule(<?= $index; ?>)">Remove Rule</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-secondary" type="button" id="add-rule-button">Add Rule</button>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    
    </div>
    
    <script>
        function updateIframeHeight() {
            setTimeout(() => {
                const iframeHeight = document.body.scrollHeight;
                parent.postMessage({ iframeHeight }, "*");
            }, 100);
        }

        function removeRule(index) {
            updateIframeHeight();
            
            const card = document.getElementById(`rule-card-${index}`);
            if (card) {
                card.remove();
            }
        }

        window.addEventListener("load", updateIframeHeight);

        document.getElementById('add-rule-button').addEventListener('click', () => {
            updateIframeHeight();

            const ruleCards = document.getElementById('rule-cards');
            const newIndex = ruleCards.children.length;

            const card = document.createElement('div');
            card.className = 'rule-card';
            card.innerHTML = `
                <div class="form-group">
                    <label class="form-label">Title:</label>
                    <input class="form-input" type="text" name="rule[${newIndex}][title]">
                </div>
                <div class="form-group">
                    <label class="form-label">Text:</label>
                    <input class="form-input" type="text" name="rule[${newIndex}][text]">
                </div>
            `;

            ruleCards.appendChild(card);
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
    </script>

</body>
