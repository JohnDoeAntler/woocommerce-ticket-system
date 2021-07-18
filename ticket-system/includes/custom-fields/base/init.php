<?php

require_once plugin_dir_path(__FILE__) . 'product.php';
require_once plugin_dir_path(__FILE__) . 'ticket_log.php';
require_once plugin_dir_path(__FILE__) . 'ticket_possession.php';

class BaseInit {
	public function hook ($loader) {
		$enabled = array(
			new BaseProduct(),
			new BaseTicketLog(),
			new BaseTicketPossession(),
		);

		foreach ($enabled as $e) {
			$loader->add_action('cmb2_admin_init', $e, 'hook');
		}
	}
}
