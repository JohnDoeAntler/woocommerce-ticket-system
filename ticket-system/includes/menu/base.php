<?php

class BaseMenu {
	function hook ($loader) {
		$loader->add_action('admin_menu', $this, 'add_ticket_system_to_menu');
		$loader->add_action('init', $this, 'add_ticket_possession');
		$loader->add_action('init', $this, 'add_ticket_log');
	}

	function add_ticket_system_to_menu() {
		add_menu_page('Ticket System', 'Ticket System', 'manage_options', 'ticket-system.php', 'redirect_admin_page', 'dashicons-tickets');
	}

	function redirect_admin_page() {
	}

	function add_ticket_possession () {
		register_extended_post_type('ticket_possession', array(
			'supports' => false,
			'show_in_menu' => 'ticket-system.php',
		));
	}

	function add_ticket_log () {
		$prefix = "ticket_system_log_";

		register_extended_post_type('ticket_log', array(
			'supports' => false,
			'show_in_menu' => 'ticket-system.php',
			'admin_cols' => array(
				// A taxonomy terms column:
				'title' => false,
				'order_id' => array(
					'title'       => 'Order',
					'meta_key'    => $prefix.'order_id',
				),
				'action' => array(
					'title'       => 'Action',
					'meta_key'    => $prefix.'ticket_action',
				),
				'type' => array(
					'title'       => 'Type',
					'meta_key'    => $prefix.'ticket_type',
				),
				'count' => array(
					'title'       => 'Count',
					'meta_key'    => $prefix.'ticket_count',
				),
				'created_at' => array(
					'title'      => 'Created At',
					'post_field' => 'post_date',
				),
			),
		));
	}
}