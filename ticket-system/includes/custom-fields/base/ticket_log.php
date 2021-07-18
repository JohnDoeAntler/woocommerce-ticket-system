<?php

class BaseTicketLog {

	public static $prefix = "ticket_system_";

	public function hook() {
		$prefix = self::$prefix . 'log_';

		$cmb_box = new_cmb2_box(array(
			'id'            => 'ticket_log_meta_box',
			'title'         => esc_html__( 'Ticket Log', 'cmb2' ),
			'object_types'  => array( 'ticket_log' ), // Post type
		));

		$cmb_box->add_field(array(
			'name'        => esc_html__('Order', 'cmb2'),
			'desc'        => esc_html__( 'Non amet sit magna elit eu officia quis deserunt.', 'cmb2' ),
			'id'          => $prefix . 'order_id',
			'type'        => 'post_search_text', // This field type
			'post_type'   => 'shop_order',
			'select_type' => 'radio',
			'select_behavior' => 'replace',
		));

		$cmb_box->add_field(array(
			'name' => esc_html__( 'Ticket Action', 'cmb2' ),
			'desc' => esc_html__( 'Sit irure ea tempor aliquip.', 'cmb2' ),
			'id'   => $prefix . 'ticket_action',
			'type' => 'select',
			'options' => array(
				'generate' => esc_html__( 'Generate', 'cmb2' ),
				'consume'   => esc_html__( 'Consume', 'cmb2' ),
			),
		));

		$cmb_box->add_field(array(
			'name' => esc_html__( 'Ticket Type', 'cmb2' ),
			'desc' => esc_html__( 'Pariatur do dolore incididunt aliqua amet labore ea irure esse consequat quis enim amet.', 'cmb2' ),
			'id'   => $prefix . 'ticket_type',
			'type' => 'text',
		));

		$cmb_box->add_field(array(
			'name' => esc_html__( 'Ticket Count', 'cmb2' ),
			'desc' => esc_html__( 'Reprehenderit irure sunt non sit officia eu duis exercitation tempor cupidatat culpa incididunt mollit.', 'cmb2' ),
			'id'   => $prefix . 'ticket_count',
			'type' => 'text',
			'attributes' => array(
				'type' => 'number',
				'pattern' => '\d*',
			),
			'sanitization_cb' => 'absint',
			'escape_cb'       => 'absint',
		));
	}
}