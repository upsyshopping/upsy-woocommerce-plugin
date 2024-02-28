<?php
/**
 * Holds the html template for the category tagging.
 *
 * Used in the main plugin file.
 *
 * @package WooCommerce upsy Tagging
 * @since   1.0.0
 * @var string $category_path The category path
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php if ( isset( $category_path ) && is_string( $category_path ) ): ?>
	<div class="upseller_category" style="display:none"><?php echo esc_html( $category_path ); ?></div>
	<div class="upseller_category_id" style="display:none"><?php echo esc_attr($category_id); ?></div>
	<script>
		jQuery( document.body ).trigger( 'init_category' );
	</script>
<?php endif; ?>
