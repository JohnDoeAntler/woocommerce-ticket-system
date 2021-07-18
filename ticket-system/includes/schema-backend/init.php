<?php

require_once plugin_dir_path(__FILE__) . 'base.php';

class SchemaBackendInit {
	public function hook ($loader) {
		$enabled = array(
			new BaseBackend(),
		);

		foreach ($enabled as $e) {
			$e->hook($loader);
		}
	}
}