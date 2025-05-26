<!doctype html>
<html lang="<?php echo $config['language']; ?>">
<head>
	<meta charset="utf-8">
	<title><?php echo $config['title']; ?></title>
	<meta name="description" content="<?php echo $config['description']; ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<meta property="og:title" content="<?php echo $config['title']; ?>">
	<meta property="og:type" content="website">
	<meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">

	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/animate.min.css">
	<link rel="stylesheet" href="assets/css/style.css">
	<link rel="stylesheet" href="assets/css/steam.css">
	<link rel="icon" href="favicon.ico">

	<meta name="theme-color" content="#141814CC">
</head>

<body style="background: url(assets/img/<?php echo $config['backgroundImage']; ?>) no-repeat center top rgb(20, 24, 20)">
	<div class="background-gradient"></div>

    <?php if($config['christmas'] == 'enabled') { ?>
        <div id="snow"></div>
    <?php } ?>

	<div class="container">
		<div class="steam-login">
			<?php if(!isset($_SESSION['steamid'])) {
				loginbutton(); //login button
			} else {
				include ('steamauth/userInfo.php');
				//Protected content
				if(file_exists('profile.php')) echo '<a  href="profile.php">';
				echo '<img src="'.$steamprofile['avatar'].'" title="" alt=""/></a> &nbsp&nbsp';
				echo $steamprofile['personaname'];
			} ?>
			<span style='float:right;'>
				<?php if(isset($_SESSION['steamid'])) {
					logoutbutton(); //logout button
				} ?>
			</span>            
		</div>   
	</div>