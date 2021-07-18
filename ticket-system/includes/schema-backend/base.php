<?php

class BaseBackend {
	public function hook($loader) {
		$loader->add_action('woocommerce_after_checkout_validation', $this, 'validation', 10, 2);
		$loader->add_filter('woocommerce_payment_complete_order_status', $this, 'consume', 10, 2);
		$loader->add_action('woocommerce_order_status_changed', $this, 'generate', 10, 3);
	}

	# if checking-out, requirement > possession, wp_notice
	function validation ($fields, $errors) {
		# prefix
		$schema_prefix = "ticket_system_schema_";
		$possession_prefix = "ticket_system_possession_";
		$email = $fields['billing_email'];
		$phone = $fields['billing_phone'];
		
		# get tickets possessions
		$ticket_possessions_id = get_ticket_possessions_by_email($email, $phone);
		$ticket_possessions = [];
		if (isset($ticket_possessions_id)) {
			$ticket_possessions = get_post_meta($ticket_possessions_id, $possession_prefix.'tickets', true);
		}

		# get required ticket cost
		$requirement = [];
		foreach (WC()->cart->cart_contents as $cart_content_product) {
			# product id
			$id = $cart_content_product['product_id'];
			$quantity = $cart_content_product['quantity'];
			# get whether consume or not
			$is_enabled = get_post_meta($id, $schema_prefix.'is_enabled', true) == 'on';
			$ticket_action = get_post_meta($id, $schema_prefix.'ticket_action', true);
			# if it is consume
			if ($is_enabled && $ticket_action == 'consume') {
				$ticket_type = get_post_meta($id, $schema_prefix.'ticket_type', true);
				$ticket_count = get_post_meta($id, $schema_prefix.'ticket_count', true) * $quantity;

				$has_ticket_type_index = -1;
				$has_ticket_type = false;

				foreach ($requirements as $index => $sub) {
					if ($sub[$schema_prefix.'ticket_type'] == $ticket_type) {
						$has_ticket_type_index = $index;
						$has_ticket_type = true;
					}
				}

				if ($has_ticket_type) {
					$requirements[$has_ticket_type_index][$schema_prefix.'ticket_count'] += $ticket_count;
				} else {
					$requirements[] = array(
						$schema_prefix.'ticket_type' => $ticket_type,
						$schema_prefix.'ticket_count' => $ticket_count,
					);
				}
			}
		}

		foreach ($requirements as $req) {
			$s_ticket_type = $req[$schema_prefix.'ticket_type'];
			$s_ticket_count = $req[$schema_prefix.'ticket_count'];

			$p_index = -1;

			foreach ($ticket_possessions as $index => $pos) {
				if ($pos[$possession_prefix.'ticket_type'] == $s_ticket_type) {
					$p_index = $index;
					break;
				}
			}

			if ($p_index == -1) {
				wc_add_notice('inadequate tickets possession, required "'.$s_ticket_count.'" of "'.$s_ticket_type.'" but found "0".', 'error');
			} else if ($ticket_possessions[$p_index][$possession_prefix.'ticket_count'] < $s_ticket_count) {
				wc_add_notice('inadequate tickets possession, required "'.$s_ticket_count.'" of "'.$s_ticket_type.'" but found "'.$ticket_possessions[$p_index][$possession_prefix.'ticket_count'].'".', 'error');
			}
		}
	}

	// if new order created and order item ticket_action == consume: reduce current tickets, add ticket log
	function consume ($status, $order_id) {
		// prevent triggering the 'is_order_consumed' variable just by viewing the page.
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			return;
		}

		// check did consumed or not
		$is_consumed = get_post_meta($order_id, 'ticket-system-is-order-consumed');
		if ($is_consumed) {
			return;
		}

		// main part
		$schema_prefix = "ticket_system_schema_";
		$possession_prefix = "ticket_system_possession_";

		$order = wc_get_order($order_id);
		$email = $order->get_billing_email() ? $order->get_billing_email() : $_POST['_billing_email'];
		$phone = $order->get_billing_phone() ? $order->get_billing_phone() : $_POST['_billing_phone'];
		
		# get tickets possessions
		$ticket_possessions_id = get_ticket_possessions_by_email($email, $phone);
		$ticket_possessions = [];
		if (isset($ticket_possessions_id)) {
			$ticket_possessions = get_post_meta($ticket_possessions_id, $possession_prefix.'tickets', true);
		}

		# get tickets requirements
		$requirements = [];
		foreach ($order->get_items() as $item) {
			# product id
			$id = $item->get_product_id();
			$quantity = $item->get_quantity();
			# get whether consume or not
			$is_enabled = get_post_meta($id, $schema_prefix.'is_enabled', true) == 'on';
			$ticket_action = get_post_meta($id, $schema_prefix.'ticket_action', true);
			# if it is consume
			if ($is_enabled && $ticket_action == 'consume') {
				$ticket_type = get_post_meta($id, $schema_prefix.'ticket_type', true);
				$ticket_count = get_post_meta($id, $schema_prefix.'ticket_count', true) * $quantity;

				$has_ticket_type_index = -1;
				$has_ticket_type = false;

				foreach ($requirements as $index => $sub) {
					if ($sub[$schema_prefix.'ticket_type'] == $ticket_type) {
						$has_ticket_type_index = $index;
						$has_ticket_type = true;
					}
				}

				if ($has_ticket_type) {
					$requirements[$has_ticket_type_index][$schema_prefix.'ticket_count'] += $ticket_count;
				} else {
					$requirements[] = array(
						$schema_prefix.'ticket_type' => $ticket_type,
						$schema_prefix.'ticket_count' => $ticket_count,
					);
				}
			}
		}

		# check if there's a ticket requirement > ticket possession, if yes, append into list of errors
		$errors = [];
		foreach ($requirements as $req) {
			$s_ticket_type = $req[$schema_prefix.'ticket_type'];
			$s_ticket_count = $req[$schema_prefix.'ticket_count'];

			$p_index = -1;

			foreach ($ticket_possessions as $index => $pos) {
				if ($pos[$possession_prefix.'ticket_type'] == $s_ticket_type) {
					$p_index = $index;
					break;
				}
			}

			if ($p_index == -1) {
				$errors[] = 'order required a amount of '.$s_ticket_count.' tickets with type "'.$s_ticket_type.'", but only found 0 in customer possession';
				continue;
			}

			if ($ticket_possessions[$p_index][$possession_prefix.'ticket_count'] < $s_ticket_count) {
				$errors[] = 'order required a amount of '.$s_ticket_count.' tickets with type "'.$s_ticket_type.'", but only found '.$ticket_possessions[$p_index][$possession_prefix.'ticket_count'].' in customer possession';
				continue;
			}

			$ticket_possessions[$p_index][$possession_prefix.'ticket_count'] -= $s_ticket_count;
		}

		# if errors occurred, revoke order and return
		if (count($errors) > 0) {
			revoke_order($order_id, 'inadequate tickets possession', $errors);
		} else {
			$ret = update_post_meta($ticket_possessions_id, $possession_prefix.'tickets', $ticket_possessions);

			if ($ret) {
				update_post_meta($order_id, 'ticket-system-is-order-consumed', 'on');
				foreach ($requirements as $req) {
					$type = $req[$schema_prefix.'ticket_type'];
					$count = $req[$schema_prefix.'ticket_count'];
					add_ticket_log($order_id, 'consume', $type, $count);
				}
			}
		}
	}

	// if order status updated to completed and order item ticket_action == generate: add ticket log and add/update ticket possession
	function generate ($order_id, $old_status, $new_status) {
		$schema_prefix = "ticket_system_schema_";
		$possession_prefix = "ticket_system_possession_";

		if ($new_status == 'completed') {
			$order = wc_get_order($order_id);

			$email = $order->get_billing_email();
			$phone = $order->get_billing_phone();
			$ticket_possessions_id = get_ticket_possessions_by_email($email, $phone);

			// if: user don't got email => create possessions
			if (!isset($ticket_possessions_id)) {
				$ticket_possessions_id = wp_insert_post(array(
					'post_type' => 'ticket_possession',
					'post_title' => $email,
					'post_content' => $email,
					'post_status' => 'publish',
				));

				update_post_meta($ticket_possessions_id, $possession_prefix.'hashed', hash("sha256", $email.$phone));
				update_post_meta($ticket_possessions_id, $possession_prefix.'customer_email', $email);
				update_post_meta($ticket_possessions_id, $possession_prefix.'customer_phone', $phone);
				update_post_meta($ticket_possessions_id, $possession_prefix.'tickets', array());
			}

			$ticket_possessions = get_post_meta($ticket_possessions_id, 'ticket_system_possession_tickets', true);

			foreach ($order->get_items() as $item) {
				# extract info from order item
				$product_id = $item->get_product_id();
				$quantity = $item->get_quantity();
				# get schema from product
				$is_enabled = get_post_meta($product_id, $schema_prefix.'is_enabled', 'true') == 'on';
				$ticket_action = get_post_meta($product_id, $schema_prefix.'ticket_action', 'true');

				if ($is_enabled && $ticket_action == 'generate') {
					$ticket_type = get_post_meta($product_id, $schema_prefix.'ticket_type', 'true');
					$ticket_count = get_post_meta($product_id, $schema_prefix.'ticket_count', 'true') * $quantity;

					// get possession
					$possession_index = null;

					foreach ($ticket_possessions as $index => $_possession) {
						if ($_possession[$possession_prefix.'ticket_type'] == $ticket_type) {
							$possession_index = $index;
							break;
						}
					}

					// if: user don't got possession => create possession record
					// else: user got possession => update possession record

					if (!isset($possession_index)) {
						array_push($ticket_possessions, array(
							$possession_prefix.'ticket_type' => $ticket_type,
							$possession_prefix.'ticket_count' => $ticket_count
						));
					} else {
						$ticket_possessions[$possession_index][$possession_prefix.'ticket_count'] += $ticket_count;
					}
				}
			}

			$ret = update_post_meta($ticket_possessions_id, 'ticket_system_possession_tickets', $ticket_possessions);

			if (ret) {
				foreach ($order->get_items() as $item) {
					# extract info from order item
					$product_id = $item->get_product_id();
					$quantity = $item->get_quantity();
					# get schema from product
					$is_enabled = get_post_meta($product_id, $schema_prefix.'is_enabled', 'true') == 'on';
					$ticket_action = get_post_meta($product_id, $schema_prefix.'ticket_action', 'true');

					if ($is_enabled && $ticket_action == 'generate') {
						$ticket_type = get_post_meta($product_id, $schema_prefix.'ticket_type', 'true');
						$ticket_count = get_post_meta($product_id, $schema_prefix.'ticket_count', 'true') * $quantity;

						add_ticket_log($order_id, 'generate', $ticket_type, $ticket_count);
					}
				}
			}
		}
	}
}