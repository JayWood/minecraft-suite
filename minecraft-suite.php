<?php
/**
* Plugin Name: Minecraft Suite
* Plugin URI:  http://plugish.com
* Description: A collection of Minecraft tid-bits, widgets, and scripts that are intended for use on a Minecraft WordPress website.
* Version:     0.2.0
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
 * Handles scripts/styles and initializes all sub-classes
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
	 * @var MS_Cpts
	 */
	protected $cpts;

	/**
	 * @var MS_Whitelist_Feed
	 */
	public $whitelist_feed;

	/**
	 * @var MS_Server_Status
	 */
	public $server_status;

	/**
	 * @var MS_Registered_Users
	 */
	public $registered_users;

	/**
	 * Option Prefix
	 * @var string
	 */
	public $option_prefix = 'mcs_';

	/**
	 * @var MS_Settings
	 */
	public $settings;

	/**
	 * @var string
	 */
	public $options_page;

	/**
	 * @var MS_Apps
	 */
	public $apps;

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
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$this->plugin_classes();

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

		$this->cpts             = new MS_Cpts( $this->dir( 'includes/cpt_config' ), $this->dir( 'includes/vendor' ) );
		$this->server_status    = new MS_Server_Status( $this );
		$this->whitelist_feed   = new MS_Whitelist_Feed( $this );
		$this->apps             = new MS_Apps( $this );
		$this->registered_users = new MS_Registered_Users( $this );
		$this->settings         = new MS_Settings( $this );
	}

	/**
	 * Add hooks and filters
	 *
	 * @since 0.1.0
	 * @return null
	 */
	public function hooks() {

		add_action( 'init', array( $this, 'init' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		$this->cpts->hooks();
		$this->whitelist_feed->hooks();
		$this->apps->hooks();
		$this->registered_users->hooks();
	}

	public function admin_init() {
		register_setting( $this->option_prefix . 'options_group', $this->option_prefix . 'max-users', 'intval' );
		register_setting( $this->option_prefix . 'options_group', $this->option_prefix . 'email-body', 'esc_attr' );
	}

	public function admin_menu() {
		$this->options_page = add_options_page( __( 'MCS Options', 'minecraft-suite' ), __( 'MCS Options', 'minecraft-suite' ), 'manage_options', 'mcs_options', array( $this, 'render_settings_page' ) );

		foreach ( $this->get_settings_array() as $section ) {

			add_settings_section( $section['id'], $section['name'], array( $this->settings, $section['group'] ), $this->options_page );

			if ( isset( $section['fields'] ) ) {
				foreach ( $section['fields'] as $option_data ) {
					add_settings_field( $this->option_prefix . $option_data['id'], $option_data['name'], array( $this->settings, $option_data['type'] ), $this->options_page, $section['id'], array( 'id' => $this->option_prefix . $option_data['id'], 'name' => $this->option_prefix . $option_data['id'], 'desc' => $option_data['desc'] ) );
				}
			}
		}

	}

	public function get_settings_array() {
		return array(
			array(
				'id'     => 'mcs-registration',
				'name'   => __( 'Registration', 'minecraft-suite' ),
				'group'  => 'default_group',
				'fields' => array(
					array(
						'id'   => 'max-users',
						'type' => 'input_number',
						'name' => __( 'Max Users', 'minecraft-suite' ),
						'desc' => __( 'Maximum Minecraft Accounts that can be tied to a single user. Leave empty for unlimited.', 'minecraft-suite' ),
					),
				),
			),
			array(
				'id'     => 'mcs-applications',
				'name'   => __( 'Applications Settings', 'minecraft-suite' ),
				'group'  => 'default_group',
				'fields' => array(
					array(
						'id'   => 'email-body',
						'type' => 'input_textarea',
						'name' => __( 'Email Message', 'minecraft-suite' ),
						'desc' => '',
					),
				),
			),
		);
	}

	public function render_settings_page() {
		?><div class="wrap">
			<h2><?php _e( 'Minecraft Suite Options', 'minecraft-suite' ); ?></h2>
			<form method="post" action="options.php">
			<?php
				settings_fields( $this->option_prefix . 'options_group' );
				do_settings_sections( $this->options_page );
				submit_button();
			?>
			</form>
		</div><?php

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
	 * Helper method
	 */
	public function get_servers() {
		return $this->apps->_get_available_servers();
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
add_action( 'plugins_loaded', array( minecraft_suite(), 'hooks' ) );
