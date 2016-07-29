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
	}
}
