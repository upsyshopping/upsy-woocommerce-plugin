<?php
/**
 * Holds the html template for the upsy elements.
 *
 * Used in the main plugin file and the upsy Tagging widget class.
 *
 * @package WooCommerce upsy Tagging
 * @since   1.0.0
 * @var array $element_ids List of div id strings
 */
?>

<?php if ( isset( $element_ids ) && is_array( $element_ids ) ): ?>
	<?php foreach ( $element_ids as $element_id ): ?>
		<div class="upseller_element" id="<?php echo esc_attr( $element_id ); ?>"></div>
	<?php endforeach; ?>
<?php endif; ?>
