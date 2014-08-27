<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Charitable_Campaign_Post_Type' ) ) : 

/**
 * Charitable Campaign post type.
 *
 * @class 		Charitable_Campaign_Post_Type
 * @abstract
 * @author 		Studio164a
 * @category 	Admin
 * @package 	Charitable/Admin/Campaign Post Type
 * @version     0.1
 */
final class Charitable_Campaign_Post_Type {

	/**
	 * @var Charitable $charitable
	 * @access private
	 */
	private $charitable;

	/**
	 * @var Charitable_Meta_Box_Helper $meta_box_helper
	 * @access private
	 */
	private $meta_box_helper;

	/**
	 * Create object instance. 
	 *
	 * @param Charitable $charitable
	 * @return void
	 * @access private
	 * @since 0.1
	 */
	private function __construct(Charitable $charitable) {
		$this->charitable = $charitable;

		$this->charitable->register_object($this);

		$this->meta_box_helper = new Charitable_Meta_Box_Helper( 'charitable-campaign' );

		add_action('add_meta_boxes', array( &$this, 'add_meta_boxes' ), 10);
		add_action('save_post', array( &$this, 'save_post' ), 10, 2);

		add_action('campaign_general_metabox', array( &$this, 'campaign_general_metabox' ));
		add_action('campaign_donations_metabox', array( &$this, 'campaign_donations_metabox' ));
	}

	/**
	 * Create an object instance. This will only work during the charitable_admin_start event.
	 * 
	 * @see charitable_admin_start hook
	 *
	 * @param Charitable $charitable
	 * @return void
	 * @access private
	 * @since 0.1
	 */
	public static function charitable_admin_start(Charitable $charitable) {
		if ( ! $charitable->is_admin_start() ) {
			return;
		}

		new Charitable_Campaign_Post_Type($charitable);
	}

	/**
	 * Add meta boxes.
	 * 
	 * @see add_meta_boxes hook
	 *
	 * @return void
	 * @access public
	 * @since 0.1
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'campaign-general', 
			__( 'Campaign General Settings', 'charitable' ), 
			array( $this->meta_box_helper, 'display' ), 
			'campaign', 
			'normal', 
			'high', 
			array( 'view' => 'metaboxes/campaign-general-metabox' )
		);

		add_meta_box(
			'campaign-donation-form',
			__( 'Campaign Donation Settings', 'charitable' ), 
			array( $this->meta_box_helper, 'display' ), 
			'campaign',
			'normal',
			'high', 
			array( 'view' => 'metaboxes/campaign-donations-metabox' )
		);
	}

	/**
	 * Adds fields to the campaign general settings metabox. 
	 *
	 * @return void
	 * @access public
	 * @since 0.1
	 */
	public function campaign_general_metabox() {

		/**
		 * Get the array of fields to be displayed within the 
		 * campaign settings metabox. 
		 */
		$fields = apply_filters( 'campaign_general_metabox_fields', 
			array(
				'goal' => array(
					'priority' => 4, 
					'view' => 'metaboxes/campaign-general/campaign-goal'
				), 
				'end_time' => array(
					'priority' => 8, 
					'view' => 'metaboxes/campaign-general/campaign-end-time'
				)
			) 
		);

		$this->meta_box_helper->display_fields( $fields );
	}

	/**
	 * Adds fields to the campaign donations metabox. 
	 *
	 * @return void
	 * @access public
	 * @since 0.1
	 */
	public function campaign_donations_metabox() {
		/**
		 * Get the array of fields to be displayed within the 
		 * campaign donations metabox. 
		 */
		$fields = apply_filters( 'campaign_donations_metabox_fields', 
			array(
				'donations' => array(
					'priority' => 4, 
					'view' => 'metaboxes/campaign-donations/campaign-donation-options'
				), 
				'form' => array(
					'priority' => 8, 
					'view' => 'metaboxes/campaign-donations/campaign-donation-form'
				)
			) 
		);

		$this->meta_box_helper->display_fields( $fields );
	}

	/**
	 * Save meta for the campaign. 
	 * 
	 * @param int $post_ID Post ID.
	 * @param WP_Post $post Post object.
	 * @return void
	 * @access public 
	 * @since 0.1
	 */
	public function save_post($post_id, WP_Post $post) {
		if ( $this->meta_box_helper->user_can_save( $post ) ) {

			$campaign_goal_enabled = isset( $_POST['campaign_goal_enabled'] ) && $_POST['campaign_goal_enabled'] == 'on';
			$campaign_goal = floatval( $_POST['campaign_goal'] );
			$campaign_end_time_enabled = isset( $_POST['campaign_end_time_enabled'] ) && $_POST['campaign_end_time_enabled'] == 'on';
			$campaign_end_time = $_POST['campaign_end_time'];
			$campaign_custom_donations_enabled =isset( $_POST['campaign_custom_donations_enabled'] ) && $_POST['campaign_custom_donations_enabled'] == 'on';
			$campaign_suggested_donations = $_POST['campaign_suggested_donations'];
			$campaign_donation_form_fields = (array) $_POST['campaign_donation_form_fields'];

			update_post_meta( $post_id, 'campaign_goal_enabled', $campaign_goal_enabled );
			update_post_meta( $post_id, 'campaign_goal', $campaign_goal );
			update_post_meta( $post_id, 'campaign_end_time_enabled', $campaign_end_time_enabled );
			update_post_meta( $post_id, 'campaign_end_time', $campaign_end_time );
			update_post_meta( $post_id, 'campaign_custom_donations_enabled', $campaign_custom_donations_enabled );
			update_post_meta( $post_id, 'campaign_suggested_donations', $campaign_suggested_donations );
			update_post_meta( $post_id, 'campaign_donation_form_fields', $campaign_donation_form_fields );
		}
	}	

	/**
	 * Return the array of all possible donation form fields. 
	 *
	 * @uses charitable_registered_donation_form_fields
	 * 
	 * @return array
	 * @access public
	 * @since 0.1
	 */
	public function get_donation_form_fields() {
		return apply_filters( 'charitable_registered_donation_form_fields', 
			array(
				'first_name' => array( 
					'label' 	=> __( 'First name', 'charitable' ), 
					'meta_key' 	=> 'donor_first_name', 
					'type'		=> 'text'
				),
				'last_name' => array( 
					'label' 	=> __( 'Last name', 'charitable' ), 
					'meta_key' 	=> 'donor_last_name',
					'type'		=> 'text'
				),
				'address' => array( 
					'label' 	=> __( 'Address', 'charitable' ), 
					'meta_key' 	=> 'donor_address',
					'type'		=> 'text'
				),
				'address_2' => array( 
					'label' 	=> __( 'Address 2', 'charitable' ), 
					'meta_key' 	=> 'donor_address_2',
					'type'		=> 'text' 
				),
				'city' => array( 
					'label' 	=> __( 'City', 'charitable' ), 
					'meta_key' 	=> 'donor_city',
					'type'		=> 'text' 
				),
				'state' => array( 
					'label' 	=> __( 'State', 'charitable' ), 
					'meta_key' 	=> 'donor_state',
					'type'		=> 'text' 
				),
				'postcode' => array( 
					'label' 	=> __( 'Postcode', 'charitable' ), 
					'meta_key' 	=> 'donor_postcode',
					'type'		=> 'number' 
				),
				'country' => array( 
					'label' 	=> __( 'Country', 'charitable' ), 
					'meta_key' 	=> 'donor_country',
					'type'		=> 'select', 
					'options' 	=> $this->charitable->get_location_helper()->get_countries()
				),
				'phone' => array( 
					'label' 	=> __( 'Phone', 'charitable' ), 
					'meta_key' 	=> 'donor_phone',
					'type'		=> 'text' 
				),
				'email' => array( 
					'label' 	=> __( 'Email', 'charitable' ), 
					'meta_key' 	=> 'donor_email',
					'type'		=> 'email',
					'required_in_form' => true
				),
				'website' => array( 
					'label' 	=> __( 'Website', 'charitable' ), 
					'meta_key' 	=> 'donor_website',
					'type'		=> 'url' 
				),
				'comment' => array( 
					'label' 	=> __( 'Comment', 'charitable' ), 
					'meta_key' 	=> 'donor_comment',
					'type'		=> 'textarea'
				)
			) 
		);
	}
}

endif; // End class_exists check