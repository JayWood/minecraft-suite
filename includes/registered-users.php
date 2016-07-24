<?php
/**
 * Minecraft Suite Registered Users
 *
 * @since NEXT
 * @package Minecraft Suite
 */

/**
 * Minecraft Suite Registered Users.
 *
 * @since NEXT
 */
class MS_Registered_Users {

	/**
	 * Parent plugin class
	 *
	 * @var   Minecraft_Suite
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 *
	 * @param  Minecraft_Suite $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ), 999 );
	}

	public function init() {
		if ( get_role( 'mcs_pending_user' ) ) {
			return;
		}

		$new_role = add_role( 'mcs_pending_user', __( 'Minecraft Pending', 'minecraft-suite' ), array(
			'read' => true,
		) );
	}

	public function get_max_users() {
		return get_option( $this->plugin->option_prefix . 'max_users', 0 );
	}

	public function display_form() {

	}
}
