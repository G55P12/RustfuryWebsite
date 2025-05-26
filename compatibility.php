<?php
    $filePermIndex = substr(sprintf('%o', fileperms('index.php')), -4);
    $filePermConfig = substr(sprintf('%o', fileperms('config.php')), -4);
    $filePermCache = substr(sprintf('%o', fileperms(getcwd() . '/cache')), -4);
    $filePermImages = substr(sprintf('%o', fileperms(getcwd() . '/assets/img')), -4);

    function printSuccessAlert(string $alert)
    {
        return '
    <div class="inline-block">
        <div class="flex items-center p-4 mb-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 dark:border-green-800" role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium"></span> '.$alert.'
            </div>
        </div>
    </div>';
    }

    function printFailAlert(string $fail)
    {
        return '
        <div class="inline-block">
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium">Alert!</span> '.$fail.'
            </div>
        </div>
    </div>';
    }
?>

<!doctype html>
<html>
    <head>
        <title>Compatibility Check</title>
        <meta charset="UTF-8">
        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="p-8 bg-slate-300">

    <div class="font-semibold">
        Current PHP version: <?php echo phpversion(); ?>
    </div>
    <?php
        if(version_compare(phpversion(), '8.1.0', '>=')) {
            echo printSuccessAlert('Your current version is compatible.');
        } else {
            echo printFailAlert('Please update your PHP version. You can update your PHP version in your webhosting control panel, if you are unsure where to find it, please contact your webhosting support.');
        }
    ?>

    <div class="font-semibold">
        PHP INI allow_url_fopen: <?php echo ini_get('allow_url_fopen'); ?>
    </div>
    <?php
        if(ini_get('allow_url_fopen') == '1') {
            echo printSuccessAlert('Your current setting is correct.');
        } else {
            echo printFailAlert('Please change your PHP INI settings in your webhoster and enable allow_url_fopen, if you are unsure where to find it, please contact your webhosting support.');
        }
    ?>

    <div class="font-semibold">
        Current file permissions for the index.php: <?php echo $filePermIndex; ?>
    </div>
    <?php
        if($filePermIndex == '0644' || $filePermIndex == '0666') {
            echo printSuccessAlert('Your file permissions are correct!');
        } else {
            echo printFailAlert('Please change your file permissions to 644 or 666, you can change it in your FTP client, if you are unsure where to find it, please contact your webhosting support.');
        }
    ?>

    <div class="font-semibold">
        Current file permissions for the config.php: <?php echo $filePermConfig; ?>
    </div>
    <?php
        if($filePermConfig == '0666') {
            echo printSuccessAlert('Your file permissions are correct!');
        } else {
            echo printFailAlert('Please change your file permissions to 666, you can change it in your FTP client, if you are unsure where to find it, please contact your webhosting support.');
        }
    ?>

    <div class="font-semibold">
        Current file permissions for the cache: <?php echo $filePermCache; ?>
    </div>
    <?php
        if($filePermCache == '0666') {
            echo printSuccessAlert('Your file permissions are correct!');
        } else {
            echo printFailAlert('Please change your file permissions to 666, you can change it in your FTP client, if you are unsure where to find it, please contact your webhosting support.');
        }
    ?>

    <div class="font-semibold">
        Current file permissions for the assets/img: <?php echo $filePermImages; ?>
    </div>
    <?php
        if($filePermImages == '0755') {
            echo printSuccessAlert('Your file permissions are correct!');
        } else {
            echo printFailAlert('Please change your file permissions to 755, you can change it in your FTP client, if you are unsure where to find it, please contact your webhosting support.');
        }
    ?>

    <div class="font-semibold">
    If all of the above checks are green you can remove this file (compatibility.php) from your directory and return to your home page.
    </div>

    </body>
</html>
