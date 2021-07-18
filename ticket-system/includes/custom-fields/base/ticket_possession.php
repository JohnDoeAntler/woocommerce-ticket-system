<?php

class BaseTicketPossession {

	public static $prefix = "ticket_system_";

	public function hook() {
		$prefix = self::$prefix . 'possession_';

		$cmb_box = new_cmb2_box(array(
			'id'            => 'ticket_possession_meta_box',
			'title'         => esc_html__( 'Ticket Possession', 'cmb2' ),
			'object_types'  => array( 'ticket_possession' ), // Post type
		));

		$cmb_box->add_field(array(
			'name'        => esc_html__('Customer email', 'cmb2'),
			'desc'        => esc_html__('Non amet sit magna elit eu officia quis deserunt.', 'cmb2'),
			'id'          => $prefix . 'customer_email',
			'type'        => 'text_email',
		));

		$cmb_box->add_field(array(
			'name'        => esc_html__('Customer phone', 'cmb2'),
			'desc'        => esc_html__('Non amet sit magna elit eu officia quis deserunt.', 'cmb2'),
			'id'          => $prefix . 'customer_phone',
			'type'        => 'text',
		));

		$group_field_id = $cmb_box->add_field(array(
			'id'          => $prefix . 'tickets',
			'type'        => 'group',
			'description' => __('Generates reusable form entries'),
			'options'     => array(
				'group_title'       => __('Entry {#}'), // since version 1.1.4, {#} gets replaced by row number
				'add_button'        => __('Add another ticket type possession'),
				'remove_button'     => __('Remove'),
				'sortable'          => true,
				'closed'         => true, // true to have the groups closed by default
				'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'cmb2' ), // Performs confirmation before removing group.
			),
		));

		$cmb_box->add_group_field($group_field_id, array(
			'id'   => $prefix.'ticket_type',
			'name' => 'Type',
			'description' => 'Laboris sint ea incididunt irure proident.',
			'type' => 'text',
		));

		$cmb_box->add_group_field($group_field_id, array(
			'id'   => $prefix.'ticket_count',
			'name' => 'Amount',
			'description' => 'Laboris sint ea incididunt irure proident.',
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