<?php
session_start();

require_once __DIR__ .  ('/steamauth/steamauth.php');
# You would uncomment the line beneath to make it refresh the data every time the page is loaded
//unset($_SESSION['steam_uptodate']);

require_once __DIR__ . '/core.php';

include 'templates/head.php';
include 'templates/navigation.php';

?>  

	<div class="container">
		<main class="rust-login-container">
			<div class="row">
				<div class="col-12">
					<h2 class="login-heading">Web Panel</h2>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
                    <iframe id="dashboard" src="admin/dashboard.php" style="width:100%;" onload="this.style.height=(this.contentWindow.document.body.scrollHeight+60)+'px';" title="Login"></iframe>
				</div>
			</div>
		</main>
	</div>

	<script>
	window.addEventListener("message", (event) => {
		if (event.data.iframeHeight) {
			const iframe = document.getElementById("dashboard");
			if (iframe) {
				iframe.style.height = event.data.iframeHeight + "px";
			}
		}
	});
	</script>

<?php
	include 'templates/footer.php';
?>