<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       plugin_name.com/team
 * @since      1.0.0
 *
 * @package    PluginName
 * @subpackage PluginName/admin/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>  
	<h2>Upsy Customer Settings</h2>  
		<!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
	<?php settings_errors(); ?>  
	<form method="POST" action="options.php">  
		<?php 
			settings_fields( 'upsy_customer_general_settings' );
			do_settings_sections( 'upsy_customer_general_settings' ); 
		?>             
		<?php submit_button(); ?>  
	</form> 

	<div class="notice notice-success is-dismissible" id="upsy_wc_auth_success_message" style="display: none;">
		<p>Store succesfully authorized - welcome to using Upsy! Your Upsy installation is now in progress in our systems. The Upsy team will get back to you when the setup is done, and your store is ready to be used.</p>
	</div>
	<div class="notice notice-error is-dismissible" id="upsy_wc_auth_error_message" style="display:none">
		<p>Sorry authentication failed.Please try again.</p>
	</div>
	<?php 
		if(empty(get_option('isUpsyWcAuthSuccess'))){
			?>
			   <div id="upsy_wc_auth_block">
					<hr />
					<p style="color:#F75B5B">Upsy needs to be authorized before it can be used - Click here to give Upsy read access to your store.</p>
					<button type='button' name='submit' class='button button-primary button-hero' id="upsy_wc_auth_connection">
						Authorize Upsy
					</button>
				</div>
			<?php
		} 
	?>		
</div>