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

    $newConfig['faq']['enabled'] = $_POST['faq_enabled'] ?? $config['faq']['enabled'];
    $newConfig['faq']['heading'] = $_POST['faq_heading'] ?? $config['faq']['heading'];
    $newConfig['faq']['navigation'] = $_POST['faq_navigation'] ?? $config['faq']['navigation'];

    if (isset($_POST['entry']) && is_array($_POST['entry'])) {
        $newConfig['entries'] = array_values(array_map(function ($faqInfo) {
            return [
                'title' => $faqInfo['title'] ?? '',
                'text' => $faqInfo['text'] ?? '',
            ];
        }, $_POST['entry']));
    } else {
        $config['faq']['entries'] = [];
    }

    $configContent = file_get_contents($configFilePath);

    foreach ($newConfig['faq'] as $key => $value) {
        $pattern = "/('faq'\s*=>\s*\[.*?\s*'$key'\s*=>\s*)'.*?'/s";
        $replacement = "\${1}'" . addslashes($value) . "'";
        $configContent = preg_replace($pattern, $replacement, $configContent, 1);
    }

    $entriesSerialized = "'entries' => [\n\n";
    foreach ($newConfig['entries'] as $FAQ) {
        $entriesSerialized .= "            [\n";
        foreach ($FAQ as $key => $value) {
            $escapedValue = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
            $entriesSerialized .= "                '$key' => $escapedValue,\n";
        }
        $entriesSerialized .= "            ],\n";
    }
    $entriesSerialized .= "\n        ]";

    $pattern = "/'entries'\s*=>\s*\[\s*(?:\[[^\]]*\]\s*,?\s*)*\]/s";
    $configContent = preg_replace($pattern, $entriesSerialized, $configContent, 2);

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
                <h2>FAQ Settings</h2>
                <div class="form-group">
                    <p></p>
                    <label class="form-label">Enabled:</label>
                    <select class="form-input" name="faq_enabled">
                        <option value="yes" <?= $config['faq']['enabled'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                        <option value="no" <?= $config['faq']['enabled'] === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Heading:</label>
                    <input class="form-input" type="text" name="faq_heading" value="<?= htmlspecialchars($config['faq']['heading']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Navigation:</label>
                    <input class="form-input" type="text" name="faq_navigation" value="<?= htmlspecialchars($config['faq']['navigation']); ?>">
                </div>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>

            <div class="section">
                <h2>Dropdown Sections</h2>
                <p>You can add a list of FAQs here. For each new FAQ you want to add, use the 'Add FAQ' button below.</p>

                <div id="faq-cards" class="faq-cards-container">
                    <?php foreach ($config['faq']['entries'] as $index => $entry): ?>
                        <div class="faq-card" id="faq-card-<?= $index; ?>">
                            <h3>FAQ <?= $index + 1; ?></h3>
                            <div class="form-group">
                                <label class="form-label">Title:</label>
                                <input class="form-input" type="text" name="entry[<?= $index; ?>][title]" value="<?= htmlspecialchars($entry['title']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Text:</label>
                                <textarea class="form-input" name="entry[<?= $index; ?>][text]" rows="5"><?= htmlspecialchars($entry['text']) ?></textarea>
                            </div>
                            <input type="hidden" name="entry[<?= $index; ?>][remove]" value="0">
                            <button class="btn btn-danger" type="button" onclick="removeFAQ(<?= $index; ?>)">Remove FAQ</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button class="btn btn-secondary" type="button" id="add-faq-button">Add FAQ</button>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>

    </div>

    <script>
        document.getElementById('add-faq-button').addEventListener('click', () => {
            const faqCards = document.getElementById('faq-cards');
            const newIndex = faqCards.children.length;

            const card = document.createElement('div');
            card.className = 'faq-card';
            card.innerHTML = `
                <h3>FAQ ${newIndex + 1}</h3>
                <div class="form-group">
                    <label class="form-label">Title:</label>
                    <input class="form-input" type="text" name="entry[${newIndex}][title]">
                </div>
                <div class="form-group">
                    <label class="form-label">Text:</label>
                    <input class="form-input" type="text" name="entry[${newIndex}][text]">
                </div>
            `;

            faqCards.appendChild(card);
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

        function removeFAQ(index) {
            updateIframeHeight();
            
            const card = document.getElementById(`faq-card-${index}`);
            if (card) {
                card.remove();
            }
        }
    </script>

</body>
