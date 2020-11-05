<?php
/**
 * Holds the html template for the order tagging.
 *
 * Used in the main plugin file.
 *
 * @package WooCommerce upsy Tagging
 * @since   1.0.0
 * @var array $order Assoc list that includes order_number, buyer and line_items
 */
?>

<?php if ( isset( $order ) && is_array( $order ) ): ?>
	<div class="upseller_purchase_order" style="display:none">
		<span class="order_number"><?php echo esc_html( $order['order_number'] ); ?></span>
		<?php if ( ! empty( $order['buyer'] ) ): ?>
			<div class="buyer">
				<span class="email"><?php echo esc_html( $order['buyer']['email'] ); ?></span>
				<?php if ( ! empty( $order['buyer']['first_name'] ) ): ?>
					<span class="first_name"><?php echo esc_html( $order['buyer']['first_name'] ); ?></span>
				<?php endif; ?>
				<span class="last_name"><?php echo esc_html( $order['buyer']['last_name'] ); ?></span>
			</div>
		<?php endif; ?>
		<div class="purchased_items">
			<?php foreach ( $order['line_items'] as $line_item ): ?>
				<div class="line_item">
					<span class="product_id"><?php echo esc_html( $line_item['product_id'] ); ?></span>
					<span class="quantity"><?php echo esc_html( $line_item['quantity'] ); ?></span>
					<span class="name"><?php echo esc_html( $line_item['name'] ); ?></span>
					<span class="unit_price"><?php echo esc_html( $line_item['unit_price'] ); ?></span>
					<span class="price_currency_code">
						<?php echo esc_html( $line_item['price_currency_code'] ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<script type="text/javascript">
		var _UPupdatePageTypes = function(){
		    var _UPtypeslen = jQuery('.upseller_page_type').length;
		    var _UPthis_pagetype = 'unknown';
		    if(_UPtypeslen > 1){
		        _UPthis_pagetype = jQuery('.upseller_page_type:first').text();
		        if(_UPthis_pagetype == 'checkout'){
		            jQuery('.upseller_page_type:first').text('thank_you');
		            jQuery('.upseller_page_type:nth-child(2n)').remove();
		        }
		    }
		};
		jQuery(document).ready(function() {
		    _UPupdatePageTypes();
		});		
	</script>
	
<?php endif; ?>
