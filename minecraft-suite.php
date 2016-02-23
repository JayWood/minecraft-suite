<?php
/**
* Plugin Name: Minecraft Suite
* Plugin URI:  http://plugish.com
* Description: A collection of Minecraft tid-bits, widgets, and scripts that are intended for use on a Minecraft WordPress website.
* Version:     0.1.0
* Author:      phyrax
* Author URI:  http://plugish.com
* Donate link: http://plugish.com
* License:     GPLv2
* Text Domain: minecraft-suite
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 phyrax (email : jjwood2004@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */


/**
 * Autoloads files with classes when needed
 *
 * @since  0.1.0
 * @param  string $class_name Name of the class being requested
 * @return  null
 */
function minecraft_suite_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'MS_' ) ) {
		return;
	}

	$filename = strtolower( str_ireplace(
		array( 'MS_', '_' ),
		array( '', '-' ),
		$class_name
	) );

	Minecraft_Suite::include_file( $filename );
}
spl_autoload_register( 'minecraft_suite_autoload_classes' );


/**
 * Main initiation class
 *
 * @since  0.1.0
 * @var  string $version  Plugin version
 * @var  string $basename Plugin basename
 * @var  string $url      Plugin URL
 * @var  string $path     Plugin Path
 */
class Minecraft_Suite {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  0.1.0
	 */
	const VERSION = '0.1.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $url      = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $path     = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var Minecraft_Suite
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Determines if scripts are to be minimized or not.
	 * @var string
	 */
	protected $min = '.min';

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @return Minecraft_Suite A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  0.1.0
	 * @return  null
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$this->plugin_classes();
		$this->hooks();

		$this->min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since 0.1.0
	 * @return  null
	 */
	function plugin_classes() {
		// Attach other plugin classes to the base plugin class.
		// $this->admin = new MS_Admin( $this );

		$this->server_status = new MS_Server_Status( $this );
		$this->applications = new MS_Applications( $this );
	}

	/**
	 * Add hooks and filters
	 *
	 * @since 0.1.0
	 * @return null
	 */
	public function hooks() {
		// Not needed yet
//		register_activation_hook( __FILE__, array( $this, '_activate' ) );
//		register_deactivation_hook( __FILE__, array( $this, '_deactivate' ) );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'wp_ajax_nopriv_minecraft-server-suite', array( $this, 'handle_ajax' ) );
		add_action( 'wp_ajax_minecraft-server-suite', array( $this, 'handle_ajax' ) );
	}

	public function handle_ajax() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mcs-nonce' ) ) {
			wp_send_json_error( __( 'Internal Server Error', 'ms' ) );
		}

		$age = absint( $_POST['age'] );
		$username = esc_attr( $_POST['ms-username'] );
		$email = sanitize_email( $_POST['email'] );

		if ( empty( $email ) ) {
			wp_send_json_error( __( 'Please enter a valid email address', 'ms' ) );
		}

		if ( empty( $age ) || empty( $email ) || empty( $username ) ) {
			wp_send_json_error( __( 'All fields are required.', 'ms' ) );
		}


		if ( $status = $this->user_has_application( $username ) ) {
			wp_send_json_error( sprintf( __( 'You already have an active application with a status of %s', 'ms' ), $status ) );
		}

		wp_send_json_success( 'Passed all tests' );


	}

	public function user_has_application( $username ) {
		$post_query = get_posts( array(
			'post_type' => 'mc_applications',
			'post_status' => 'any',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => 'mc-username',
					'value' => $username,
				),
			),
			'fields' => 'ids',
		) );

		$status = false;
		if ( ! empty( $post_query ) ) {
			$id = array_shift( $post_query );
			$status = get_post_meta( $id, 'application_status', true );
		}

		return $status;
	}

	public function enqueue_scripts() {

		wp_localize_script( 'minecraft_suite', 'mc_l10n', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );

		wp_enqueue_style( 'minecraft_suite' );
		wp_enqueue_script( 'minecraft_suite' );
	}

	public function admin_scripts() {
//		wp_enqueue_style( 'minecraft_suite' );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  0.1.0
	 * @return null
	 */
	function _activate() {
		// Make sure any rewrite functionality has been loaded
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  0.1.0
	 * @return null
	 */
	function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 * @return null
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'minecraft-suite', false, dirname( $this->basename ) . '/languages/' );
			wp_register_style( 'minecraft_suite', $this->url( "assets/css/minecraft-suite{$this->min}.css" ), false, self::VERSION );
			wp_register_script( 'minecraft_suite', $this->url( "assets/js/minecraft-suite{$this->min}.js" ), array( 'jquery' ), self::VERSION, true );
		}
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  0.1.0
	 * @return boolean
	 */
	public static function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('')

		// We have met all requirements
		return true;
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.1.0
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {
			// Display our error
			echo '<div id="message" class="error">';
			echo '<p>' . sprintf( __( 'Minecraft Suite is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'minecraft-suite' ), admin_url( 'plugins.php' ) ) . '</p>';
			echo '</div>';
			// Deactivate our plugin
			deactivate_plugins( $this->basename );

			return false;
		}

		return true;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  0.1.0
	 * @param  string  $filename Name of the file to be included
	 * @return bool    Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/'. $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	public static function views( $filename ) {
		$file = self::dir( 'views/'. $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  0.1.0
	 * @param  string $path (optional) appended path
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  0.1.0
	 * @param  string $path (optional) appended path
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the Minecraft_Suite object and return it.
 * Wrapper for Minecraft_Suite::get_instance()
 *
 * @since  0.1.0
 * @return Minecraft_Suite  Singleton instance of plugin class.
 */
function minecraft_suite() {
	return Minecraft_Suite::get_instance();
}

// Kick it off
minecraft_suite();
