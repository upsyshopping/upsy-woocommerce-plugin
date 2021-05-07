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
	<?php
	
	$customer_id = get_option('upsy_settings_customer_id');
	if(trim($customer_id)) {
		$iframe_url = 'https://upsyconfig.serviceform.com/?customer='.$customer_id
		?>
      <div id="iframe_container" style="width:100%;height:800px">
          <iframe src=<?php echo $iframe_url ?> title="Upsy Customer Settings"  frameborder="0" allowfullscreen
          style="max-width: 1200px; width:100%;position: absolute; height: 800px; border: none;"></iframe>
      </div>
		<?php
	}
	?>



</div>

<script>
  setTimeout(function (){

// Util function
    function addFormatter (input, formatFn) {
      console.log(input);
      var oldValue = input.value;

      var handleInput = event => {
        var result = formatFn(input.value, oldValue, event);
        if (typeof result === "string") {
          input.value = result;
        }

        oldValue = input.value;
      }

      handleInput();
      input.addEventListener("input", handleInput);
    }

// Example implementation
// HOF returning regex prefix formatter
    function regexPrefix (regex, prefix) {
      return (newValue, oldValue) => regex.test(newValue) ? newValue : (newValue ? oldValue : prefix);
    }

// Apply formatter
    var input = document.querySelector("#upsy_settings_customer_id");
    addFormatter(input, regexPrefix(/^ups-/, "ups-"));

  }, 2000);

</script>
