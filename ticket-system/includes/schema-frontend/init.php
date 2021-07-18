<?php

require_once plugin_dir_path(__FILE__) . 'base.php';

class SchemaFrontendInit {
	public function hook ($loader) {
		$enabled = array(
			new BaseFrontend(),
		);

		foreach ($enabled as $e) {
			$e->hook($loader);
		}
	}
}