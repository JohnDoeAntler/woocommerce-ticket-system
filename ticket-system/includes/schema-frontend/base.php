<?php
class BaseFrontend {
	public function hook($loader) {
		$loader->add_action('woocommerce_before_add_to_cart_button', $this, 'product_add_on');
		$loader->add_filter('woocommerce_add_to_cart_validation', $this, 'product_add_on_validation', 10, 3);
		$loader->add_filter('woocommerce_add_cart_item_data', $this, 'product_add_on_cart_item_data', 10, 2);
		$loader->add_filter('woocommerce_get_item_data', $this, 'product_add_on_display_cart', 10, 2);
		# print qr code
		$loader->add_action('woocommerce_thankyou', $this, 'generate_qr_code', 10, 1);
		# scan qr code
		$loader->add_action('add_meta_boxes_shop_order', $this, 'metaboxes', 10, 0);
		# add setting
		$loader->add_filter('woocommerce_get_sections_products', $this, 'ticket_system_add_section', 10, 1);
		$loader->add_filter('woocommerce_get_settings_products', $this, 'ticket_system_add_settings', 10, 2);
		# validate setting
		$loader->add_action('updated_option', $this, 'validate_endpoint', 10, 3);
	}

	// add custom input fields on product page
	function product_add_on() {
		global $product;
		$id = $product->get_id();

		$prefix = "ticket_system_schema_";
		$is_enabled = get_post_meta($id, $prefix.'is_enabled', true) == 'on';

		if ($is_enabled) {
			$ticket_action = get_post_meta($id, $prefix.'ticket_action', true );
			$ticket_type = get_post_meta($id, $prefix.'ticket_type', true );
			$ticket_count = get_post_meta($id, $prefix.'ticket_count', true );

			echo '
				<p> Detail: </p>
				<table cellspacing="0">
					<thead>
						<tr>
							<th id="action" class="manage-column column-action" scope="col">action</th>
							<th id="type" class="manage-column column-type" scope="col">type</th>
							<th id="count" class="manage-column column-count" scope="col">count</th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td class="column-action" scope="row">'.$ticket_action.'</td>
							<td class="column-type">'.$ticket_type.'</td>
							<td class="column-count">'.$ticket_count.'</td>
						</tr>
						
					</tbody>
				</table>
				<input name="ticket_system_ticket_action" type="hidden" value="'.$ticket_action.'">
				<input name="ticket_system_ticket_type" type="hidden" value="'.$ticket_type.'">
				<input name="ticket_system_ticket_count" type="hidden" value="'.$ticket_count.'">
			';
		}
	}

	// validate custom fields
	function product_add_on_validation($passed, $product_id, $quantity) {
		$prefix = "ticket_system_schema_";
		$is_enabled = get_post_meta($product_id, $prefix.'is_enabled', true) == 'on';

		if ($is_enabled) {
			foreach ([
				'ticket_system_ticket_action',
				'ticket_system_ticket_type',
				'ticket_system_ticket_count',
			] as $key) {
				if (!isset($_POST[$key]) || sanitize_text_field($_POST[$key]) == '') {
					$passed = false;
				}
			}
		}

		return $passed;
	}

	// store custom fields values into cart
	function product_add_on_cart_item_data($cart_item, $product_id) {
		foreach ([
			'ticket_system_ticket_action',
			'ticket_system_ticket_type',
			'ticket_system_ticket_count',
		] as $key) {
			if(isset($_POST[$key])) {
				$cart_item[$key] = sanitize_text_field($_POST[$key]);
			}
		}
		return $cart_item;
	}

	// display custom fields values on cart
	function product_add_on_display_cart($data, $cart_item) {
		# echo var_dump($cart_item);
		foreach ([
			'ticket_system_ticket_action' => 'Ticket Action',
			'ticket_system_ticket_type' => 'Ticket Type',
			'ticket_system_ticket_count' => 'Ticket Count',
		] as $key => $value) {
			if (isset($cart_item[$key])){
				$data[] = array(
					'name' => $value,
					'value' => sanitize_text_field($cart_item[$key])
				);
			}
		}
		return $data;
	}
 
	function generate_qr_code($order_id) {
		$order = wc_get_order($order_id);
		$email = $order->get_billing_email();
		$phone = $order->get_billing_phone();
		$response = wp_remote_post(get_option('ticket-system-api-endpoint').'/encrypt/'.get_option('ticket-system-api-key'), 
			array(
				'headers' => array(
					'Content-Type' => 'application/json'
				),
				'body' => json_encode(
					array(
						'email' => $email,
						'phone' => $phone
					)
				)
			)
		);
		$hash = json_decode($response['body'])->message->hash;
		?>
			<h2 class="woocommerce-order-details__title" style="margin-top: 0px">Ticket System - QR Code</h2>
			<p>
				screenshot this QR code, hand it to staff when purchasing a ticket-based service for identification.
			</p>
			<div id="ticket-system-qrcode"></div>
			<script src="https://cdn.jsdelivr.net/npm/davidshimjs-qrcodejs@0.0.2/qrcode.min.js"></script>
			<script>
				new QRCode(
					document.getElementById("ticket-system-qrcode"),
					"<?php echo $hash; ?>",
				);
			</script>
			<div style="margin-bottom: 2rem;"></div>
		<?php
	}

	function metaboxes() {
		$screen = get_current_screen();
		if ($screen->action == 'add') {
			global $wp_meta_boxes;
			add_meta_box('testdiv', __('QR code scanner'), array($this, 'scan_qr_code'), 'shop_order', 'normal', 'high');
		}
	}

	function scan_qr_code() {
		?>	
			<div id="ticket-system-qr-code-scanner" width="600px"></div>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.0.3/html5-qrcode.min.js"></script>
			<script>
				function onScanSuccess(decodedText, decodedResult) {
					(async () => {
						const test = await fetch('<?php echo get_option('ticket-system-api-endpoint').'/decrypt/'.get_option('ticket-system-api-key') ?>', {
								'method': 'POST',
								'body': JSON.stringify(
									{
										"hash": decodedText,
									}
								)
						});

						const data = await test.json();
						document.getElementById('_billing_email').value = data.message.email;
						document.getElementById('_billing_phone').value = data.message.phone;
					})();
				}

				function onScanFailure(error) {
					window.alert('unable to scan the qr code.');
				}

				let html5QrcodeScanner = new Html5QrcodeScanner(
					"ticket-system-qr-code-scanner",
					{
						fps: 10,
						qrbox: 250,
					},
					false,
				);
				html5QrcodeScanner.render(onScanSuccess, onScanFailure);
			</script>
		<?php
	}

	function ticket_system_add_section( $sections ) {
		$sections['ticket-system'] = __('Ticket System');
		return $sections;
	}

	function ticket_system_add_settings($settings, $current_section) {
		/**
		 * Check the current section is what we want
		 **/
		if ( $current_section == 'ticket-system' ) {
			$settings_slider = array();
			// Add Title to the Settings
			$settings_slider[] = array(
				'id' => 'ticket-system-settings',
				'name' => __('Ticket System'),
				'desc' => __( 'The following options are used to configure ticket system'),
				'type' => 'title',
			);

			// Add second text field option
			$settings_slider[] = array(
				'id'       => 'ticket-system-api-endpoint',
				'name'     => __('API Endpoint'),
				'desc'     => __('An API endpoint for QR Code encryption/decryption.'),
				'desc_tip' => __('An API endpoint for QR Code encryption/decryption.'),
				'type'     => 'text',
			);

			if (empty(get_option('ticket-system-api-endpoint'))) {
				update_option('ticket-system-api-endpoint', 'https://ts.johndoeantler.com');
			}

			// Add second text field option
			$settings_slider[] = array(
				'id'       => 'ticket-system-api-key',
				'name'     => __('API Key'),
				'desc'     => __('An API Key for QR Code encryption/decryption.'),
				'desc_tip' => __('An API Key for QR Code encryption/decryption.'),
				'type'     => 'text',
			);
			
			$settings_slider[] = array( 'type' => 'sectionend', 'id' => 'wcslider' );
			return $settings_slider;
		} else {
			return $settings;
		}
	}

	function validate_endpoint ($option_name, $old_value, $new_value) {
		if ($option_name == 'ticket-system-api-key' && $new_value != '<invalid_token>') {
			$response = wp_remote_post(
				get_option('ticket-system-api-endpoint').'/auth',
				array(
					'headers' => array(
						'Content-Type' => 'application/json',
					),
					'body' => json_encode(
						array(
							'id' => $new_value
						)
					)
				)
			);

			$status = json_decode($response['body'])->status;

			if ($status != 'success') {
				update_option('ticket-system-api-key', '<invalid_token>');
			}
		}
	}
}
