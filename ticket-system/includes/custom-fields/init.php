<?php

require_once plugin_dir_path(__FILE__) . 'base/init.php';

class CustomFieldsInit {
	public function hook ($loader) {
		$enabled = array(
			new BaseInit(),
		);

		foreach ($enabled as $e) {
			$e->hook($loader);
		}
	}
}