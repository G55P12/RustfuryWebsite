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

$imgFolder = '../../assets/img/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newConfig['staff']['enabled'] = $_POST['staff_enabled'] ?? $config['staff']['enabled'];
    $newConfig['staff']['heading'] = $_POST['staff_heading'] ?? $config['staff']['heading'];
    $newConfig['staff']['navigation'] = $_POST['staff_navigation'] ?? $config['staff']['navigation'];

    if (isset($_POST['staff']) && is_array($_POST['staff'])) {
        $newConfig['members'] = array_values(array_map(function ($member) {
            return [
                'name' => $member['name'] ?? '',
                'rank' => $member['rank'] ?? '',
                'avatar' => $member['avatar'] ?? '',
                'link' => $member['link'] ?? '',
            ];
        }, $_POST['staff']));
    } else {
        $config['members'] = [];
    }

    $configContent = file_get_contents($configFilePath);

    foreach ($newConfig['staff'] as $key => $value) {
        $pattern = "/('staff'\s*=>\s*\[.*?\s*'$key'\s*=>\s*)'.*?'/s";
        $replacement = "\${1}'" . addslashes($value) . "'";
        $configContent = preg_replace($pattern, $replacement, $configContent, 1);
    }

    $membersSerialized = "'members' => [\n\n";
    foreach ($newConfig['members'] as $member) {
        $membersSerialized .= "            [\n";
        foreach ($member as $key => $value) {
            $escapedValue = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
            $membersSerialized .= "                '$key' => $escapedValue,\n";
        }
        $membersSerialized .= "            ],\n";
    }
    $membersSerialized .= "\n        ]";

    $pattern = "/'members'\s*=>\s*\[\s*(?:\[[^\]]*\]\s*,?\s*)*\]/s";
    $configContent = preg_replace($pattern, $membersSerialized, $configContent, 1);

    if (file_put_contents($configFilePath, $configContent) === false) {
        $_SESSION['message'] = ['type' => 'error', 'text' => "Failed to write new settings."];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['message'] = ['type' => 'success', 'text' => "Settings updated successfully!"];
    }
    
    if (isset($_POST['upload']) && isset($_FILES['newFile']) && $_FILES['newFile']['error'] === UPLOAD_ERR_OK) {
        $acceptableExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileName = basename($_FILES['newFile']['name']);
        $fileTmpName = $_FILES['newFile']['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $acceptableExtensions)) {
            $destination = $imgFolder . $fileName;
            if (move_uploaded_file($fileTmpName, $destination)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => "File uploaded successfully!"];
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => "Failed to move uploaded file."];
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => "Invalid file format."];
        }
    }
    
    sleep(2);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function getFilesFromImgFolder($directory) {
    return is_dir($directory) ? array_filter(scandir($directory), fn($file) => is_file($directory . $file)) : [];
}
?>

<link rel="stylesheet" href="../assets/css/modal.css">
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

        <form method="post" enctype="multipart/form-data">
            <div class="section">
                <h2>Staff Settings</h2>
                <div class="form-group">
                    <p>To improve credibility of your server, you can add a list of staff members here. If avatar is left empty, it will display a default image instead. Upload
                    staff avatars to the img folder and add the filename. Dimensions are 180x180 px. Link can be left empty and it will only show up as normal text. If you input a link, the name of the staff member will be linked to the URL. Link has to start with the protocol (https://).</p>
                    <label class="form-label">Enabled:</label>
                    <select class="form-input" name="staff_enabled">
                        <option value="yes" <?= $config['staff']['enabled'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                        <option value="no" <?= $config['staff']['enabled'] === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Heading:</label>
                    <input class="form-input" type="text" name="staff_heading" value="<?= htmlspecialchars($config['staff']['heading']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Navigation:</label>
                    <input class="form-input" type="text" name="staff_navigation" value="<?= htmlspecialchars($config['staff']['navigation']); ?>">
                </div>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>

            <div class="section">
                <h2>Staff</h2>
                <p>You can add a list of available staff here. For each new staff you want to add, use the 'Add Staff' button below.</p>
                <p>Avatars can be uploaded to the /img folder using the tool below. The 'Select Img' button in each staff card will allow you to select the avatar to use from the /img folder.</p>
                <div class="form-group">
                    <label class="form-label">Upload New Image:</label>
                    <input class="form-input" type="file" name="newFile" accept=".jpg,.jpeg,.png,.gif,.webp">
                    <button class="btn btn-secondary" type="submit" name="upload">Upload</button>
                </div>
                <div id="staff-cards" class="staff-cards-container">
                    <?php foreach ($config['staff']['members'] as $index => $staff): ?>
                        <div class="staff-card" id="staff-card-<?= $index; ?>">
                            <h3>Staff <?= $index + 1; ?></h3>
                            <div class="form-group">
                                <label class="form-label">Name:</label>
                                <input class="form-input" type="text" name="staff[<?= $index; ?>][name]" value="<?= htmlspecialchars($staff['name']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Rank:</label>
                                <input class="form-input" type="text" name="staff[<?= $index; ?>][rank]" value="<?= htmlspecialchars($staff['rank']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Link:</label>
                                <input class="form-input" type="text" name="staff[<?= $index; ?>][link]" value="<?= htmlspecialchars($staff['link']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Avatar:</label>
                                <input class="form-input" type="text" id="staffAvatar[<?= $index; ?>]" name="staff[<?= $index; ?>][avatar]" value="<?= htmlspecialchars($staff['avatar']); ?>">
                            </div>
                            <button class="select btn btn-secondary" type="button" data-field="staffAvatar[<?= $index; ?>]">Select Img</button>
                            <input type="hidden" name="staff[<?= $index; ?>][remove]" value="0">
                            <button class="btn btn-danger" type="button" onclick="removeStaff(<?= $index; ?>)">Remove Staff</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-secondary" type="button" id="add-staff-button">Add Staff</button>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>

        <div id="select-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Select a File</h2>
                <div class="file-list">
                    <?php foreach (getFilesFromImgFolder($imgFolder) as $file): ?>
                        <div class="file-item" data-file="<?= $file ?>"><?= $file ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
    </div>
    
    <script>
        function updateIframeHeight() {
            setTimeout(() => {
                const iframeHeight = document.body.scrollHeight;
                parent.postMessage({ iframeHeight }, "*");
            }, 100);
        }

        function removeStaff(index) {
            updateIframeHeight();

            const card = document.getElementById(`staff-card-${index}`);
            if (card) {
                card.remove();
            }
        }

        window.addEventListener("load", updateIframeHeight);

        document.getElementById('add-staff-button').addEventListener('click', () => {
            updateIframeHeight();

            const staffCards = document.getElementById('staff-cards');
            const newIndex = staffCards.children.length;

            const card = document.createElement('div');
            card.className = 'staff-card';
            card.innerHTML = `
                <h3>Staff ${newIndex + 1}</h3>
                <div class="form-group">
                    <label class="form-label">Name:</label>
                    <input class="form-input" type="text" name="staff[${newIndex}][name]">
                </div>
                <div class="form-group">
                    <label class="form-label">Rank:</label>
                    <input class="form-input" type="text" name="staff[${newIndex}][rank]">
                </div>
                <div class="form-group">
                    <label class="form-label">Avatar:</label>
                    <input class="form-input" type="text" name="staff[${newIndex}][avatar]">
                </div>
                <div class="form-group">
                    <label class="form-label">Link:</label>
                    <input class="form-input" type="text" name="staff[${newIndex}][link]">
                </div>
            `;

            staffCards.appendChild(card);
        });
        
        document.addEventListener("DOMContentLoaded", () => {
            const selectModal = document.getElementById("select-modal");
            let activeField = null;

            document.querySelectorAll(".select").forEach(button => {
                button.addEventListener("click", () => {
                    activeField = button.dataset.field;
                    selectModal.style.display = "block";
                });
            });

            document.querySelectorAll(".file-item").forEach(item => {
                item.addEventListener("click", () => {
                    if (activeField) {
                        document.getElementById(activeField).value = item.dataset.file;
                        selectModal.style.display = "none";
                    }
                });
            });

            document.querySelector(".close-modal").addEventListener("click", () => {
                selectModal.style.display = "none";
            });
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
