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
    $newConfig['welcome'] = $_POST['welcome'] ?? $config['welcome'];
    $newConfig['title'] = $_POST['title'] ?? $config['title'];
    $newConfig['description'] = $_POST['description'] ?? $config['description'];
    $newConfig['logo'] = $_POST['logo'] ?? $config['logo'];
    $newConfig['backgroundImage'] = $_POST['backgroundImage'] ?? $config['backgroundImage'];
    $newConfig['shopImage'] = $_POST['shopImage'] ?? $config['shopImage'];
    $newConfig['christmasImage'] = $_POST['christmasImage'] ?? $config['christmasImage'];
    $newConfig['christmas'] = $_POST['christmasEnabled'] ?? $config['christmas'];

    $configContent = file_get_contents($configFilePath);

    foreach ([
        'welcome' => $newConfig['welcome'],
        'title' => $newConfig['title'],
        'description' => $newConfig['description'],
        'logo' => $newConfig['logo'],
        'backgroundImage' => $newConfig['backgroundImage'],
        'shopImage' => $newConfig['shopImage'],
        'christmasImage' => $newConfig['christmasImage'],
        'christmas' => $newConfig['christmas'],
    ] as $key => $value) {
        $pattern = "/'" . $key . "'\\s*=>\\s*'.*?'/";
        $replacement = "'" . $key . "' => '" . addslashes($value) . "'";
        $configContent = preg_replace($pattern, $replacement, $configContent, 1);
    }

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

    sleep(3);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function getFilesFromImgFolder($directory) {
    return is_dir($directory) ? array_filter(scandir($directory), fn($file) => is_file($directory . $file)) : [];
}
?>

<link rel="stylesheet" href="../assets/css/settings.css">
<link rel="stylesheet" href="../assets/css/modal.css">

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

        <form method="POST" enctype="multipart/form-data">
            <div class="section">
                <h2>Website information</h2>
                <div class="form-group">
                <p>The Welcome text is put in front of the title on the home page. Description is placed underneath.</p>
                    <label class="form-label">Welcome Message:</label>
                    <input type="text" id="welcome" name="welcome" value="<?= htmlspecialchars($config['welcome']) ?>" class="form-input">
                </div>
                <div class="form-group">
                <p>The Title and Description are used in the header part (hero) of the website. Include your server name in the title to improve SEO results.</p>
                    <label class="form-label">Site Title:</label>
                    <input type="text" id ="title" name="title" value="<?= htmlspecialchars($config['title']) ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Site Description:</label>
                    <input type="text" id="description" name="description" value="<?= htmlspecialchars($config['description']) ?>" class="form-input">
                </div>
                <div class="form-group">
                <p>The Christmas Effect, when enabled, will add snow to your website, replace the shop image to the set Christmas image and make it a little more festive for the season!</p>
                    <label class="form-label">Christmas Effect</label>
                    <select class="form-input" name="christmasEnabled">
                        <option value="enabled" <?= $config['christmas'] === 'enabled' ? 'selected' : '' ?>>Enabled</option>
                        <option value="disabled" <?= $config['christmas'] === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div class="form-group">
                <p>Only change the language setting if your primary content is not in english.</p>
                    <label class="form-label">Language:</label>
                    <input type="text" id="language" name="language" value="<?= htmlspecialchars($config['language']) ?>" class="form-input">
                </div>
                <button class="btn btn-primary" type="submit">Save Settings</button>
            </div>

            <div class="section">
                <h2>Logo, Background, and Shop Images</h2>
                <p>You can change your site images here. Use the form below to upload your images into the img folder and select the file name.</p>
                <p>Images can also be given a URL if your images are stored off-site.</p>
                <p>If logo is empty, it will display the title instead of the logo. The logo needs to have a height of 75px, while the width does not matter. Background image should not be left empty.</p>
                <p>IMPORTANT: If you do not have a logo with the same sizes (e.g. 75x75)you need to manually change the width in the CSS code.<br> Search for .navbar-rust .navbar-logo-container in the style.css file and replace the width with your logo's width.</p>
                <div class="form-group">
                    <label class="form-label">Upload New Image:</label>
                    <input class="form-input" type="file" name="newFile" accept=".jpg,.jpeg,.png,.gif,.webp">
                    <button class="btn btn-secondary" type="submit" name="upload">Upload</button>
                </div>
                <table>
                    <tr>
                        <td> 
                            <div class="form-group">
                                <label class="form-label">Logo:</label>
                                <input type="text" id="logo" name="logo" value="<?= htmlspecialchars($config['logo']) ?>" class="form-input">
                                <button class="select btn btn-secondary" type="button" data-field="logo">Select Image</button>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label class="form-label">Background Image:</label>
                                <input type="text" id="backgroundImage" name="backgroundImage" value="<?= htmlspecialchars($config['backgroundImage']) ?>" class="form-input">
                                <button class="select btn btn-secondary" type="button" data-field="backgroundImage">Select Image</button>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label class="form-label">Shop Image:</label>
                                <input type="text" id="shopImage" name="shopImage" value="<?= htmlspecialchars($config['shopImage']) ?>" class="form-input">
                                <button class="select btn btn-secondary" type="button" data-field="shopImage">Select Image</button>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label class="form-label">Christmas Shop Image:</label>
                                <input type="text" id="christmasImage" name="christmasImage" value="<?= htmlspecialchars($config['christmasImage']) ?>" class="form-input">
                                <button class="select btn btn-secondary" type="button" data-field="christmasImage">Select Image</button>
                            </div>
                        </td>
                    </tr>
                </table>
                <button class="btn btn-primary" type="submit">Save Settings</button>
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
