<?php
/**
 * View for wp-admin page.
 *
 * @package WPE_Content_Model
 */

declare(strict_types=1);

namespace WPE\ContentModel\Settings;

?>

<div class="wrap wpe-content-model-admin-page">
	<header>
		<div class="wrap">
			<a href="<?php echo admin_url( 'admin.php?page=wpe-content-model' ); ?>">
				<svg class="wpengine" width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M0 2.29413L2.29411 0H9.64706V9.64707H0V2.29413ZM10.1765 0H19.8235V7.35294L17.4706 9.64707H12.4706L10.1765 7.35294V0ZM22.6471 10.1765L20.3529 12.4706V17.5294L22.6471 19.8235H30V10.1765H22.6471ZM10.1765 30H19.8235V22.6471L17.4706 20.3529H12.4706L10.1765 22.6471V30ZM30 30V22.6471L27.7059 20.3529H20.3529V30H30ZM20.3529 0V7.35294L22.6471 9.64707H30V0H20.3529ZM13.6471 15C13.6471 15.7059 14.2353 16.353 15 16.353C15.7647 16.353 16.3529 15.7647 16.3529 15C16.3529 14.2941 15.7647 13.6471 15 13.6471C14.2941 13.6471 13.6471 14.2353 13.6471 15ZM9.64706 10.1765H0V19.8235H7.29411L9.64706 17.5294V10.1765ZM7.29411 20.3529L9.64706 22.6471V27.7059L7.29411 30H0V20.3529H7.29411Z" fill="white"></path>
				</svg>
				<h1>Content Model Creator by WP Engine</h1>
			</a>
		</div>
	</header>
	<div id="root"></div>
</div>
