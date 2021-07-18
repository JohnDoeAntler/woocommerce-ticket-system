<?php

function get_ticket_possessions_by_email($email, $phone) {
	global $wpdb;

	$post = $wpdb->get_row("select * from wp_postmeta where meta_key = 'ticket_system_possession_hashed' and meta_value = '".hash('sha256', $email.$phone)."'");

	if (isset($post)) {
		return $post->post_id;
	}

	return null;
}
