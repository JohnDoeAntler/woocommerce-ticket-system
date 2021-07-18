<?php

function add_ticket_log ($order_id, $ticket_action, $ticket_type, $ticket_count) {
	$prefix = "ticket_system_log_";

	$post_id = wp_insert_post(array (
		'post_type' => 'ticket_log',
		'post_status' => 'publish',
	));

	if ($post_id) {
		add_post_meta($post_id, $prefix.'order_id', $order_id);
		add_post_meta($post_id, $prefix.'ticket_action', $ticket_action);
		add_post_meta($post_id, $prefix.'ticket_type', $ticket_type);
		add_post_meta($post_id, $prefix.'ticket_count', $ticket_count);
	}

	return $post_id;
}