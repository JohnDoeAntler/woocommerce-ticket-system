<?php

function revoke_order ($order_id, $status_note = '', $messages = array()) {
	$order = wc_get_order($order_id);

	if (!isset($order)) return;

	$order->set_status('failed', $status_note, true);
	$order->save();

	foreach ($messages as $message) {
		$order->add_order_note($message);
	}
}
