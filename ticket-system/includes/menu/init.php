<?php

require_once plugin_dir_path(__FILE__) . 'base.php';

class MenuInit {
	public function hook ($loader) {
		$enabled = array(
			new BaseMenu(),
		);

		foreach ($enabled as $e) {
			$e->hook($loader);
		}
	}
}