<?php
/**
 * Holds the html template for the shopping cart tagging.
 *
 * Used in the upsy Tagging widget class.
 *
 * @package WooCommerce upsy Tagging
 * @since   1.0.0
 * @var string $upsy_id_field_id   The field id for the upsy id input field
 * @var string $upsy_id_field_name The field name for the upsy id input field
 * @var string $upsy_id            The upsy id
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<p>
	<label for="<?php echo esc_attr( $upsy_id_field_id ); ?>"><?php esc_html_e( 'upsy ID:' ); ?></label>
	<input type="text" class="widefat" id="<?php echo esc_attr( $upsy_id_field_id ); ?>"
		   name="<?php echo esc_attr( $upsy_id_field_name ); ?>" value="<?php echo esc_attr( $upsy_id ); ?>" />
	<span class="description">
		<?php esc_html_e( 'Must begin with a letter (a-z), and may be followed by any number of letters (a-z),
				digits (0-9), hyphens ("-"), underscores ("_"), colons (":"), and periods (".")' ); ?>
	</span>
</p>
