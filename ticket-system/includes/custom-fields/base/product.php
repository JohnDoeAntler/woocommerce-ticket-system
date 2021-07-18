<?php

class BaseProduct {

	public static $prefix = "ticket_system_";

	public function hook() {
		$prefix = self::$prefix . "schema_";

		$cmb_box = new_cmb2_box(array(
			'id'            => 'ticket_schema_meta_box',
			'title'         => esc_html__('Ticket Schema', 'cmb2'),
			'object_types'  => array( 'product' ), // Post type
		));
	
		$cmb_box->add_field(array(
			'name' => esc_html__( 'Is enabled', 'cmb2' ),
			'desc' => esc_html__( 'Deserunt irure deserunt esse dolore nisi deserunt eu tempor reprehenderit veniam est ipsum.', 'cmb2' ),
			'id'   => $prefix . 'is_enabled',
			'type' => 'checkbox',
		));

		$cmb_box->add_field(array(
			'name' => esc_html__( 'Ticket Type', 'cmb2' ),
			'desc' => esc_html__( 'Pariatur do dolore incididunt aliqua amet labore ea irure esse consequat quis enim amet.', 'cmb2' ),
			'id'   => $prefix.'ticket_type',
			'type' => 'text',
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'is_enabled',
				'data-conditional-value' => 'on',
			),
		));

		$cmb_box->add_field(array(
			'name' => esc_html__( 'Ticket Action', 'cmb2' ),
			'desc' => esc_html__( 'Sit irure ea tempor aliquip.', 'cmb2' ),
			'id'   => $prefix . 'ticket_action',
			'type' => 'select',
			'options'          => array(
				'generate' => esc_html__( 'Generate', 'cmb2' ),
				'consume'   => esc_html__( 'Consume', 'cmb2' ),
			),
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'is_enabled',
				'data-conditional-value' => 'on',
			),
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
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'is_enabled',
				'data-conditional-value' => 'on',
			),
		));
	}
}