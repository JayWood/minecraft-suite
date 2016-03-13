<?php
/**
 * Minecraft Suite Server Status
 * @version 0.1.0
 * @package Minecraft Suite
 */

require_once 'vendor/MC_Query/MinecraftPing.php';
require_once 'vendor/MC_Query/MinecraftPingException.php';

use xPaw\MinecraftPing;
use xPaw\MinecraftPingException;

class MS_Server_Status extends WP_Widget {

	/**
	 * Unique identifier for this widget.
	 *
	 * Will also serve as the widget class.
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $widget_slug = 'minecraft-suite-server-status';


	/**
	 * Widget name displayed in Widgets dashboard.
	 * Set in __construct since __() shouldn't take a variable.
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $widget_name = '';


	/**
	 * Default widget title displayed in Widgets dashboard.
	 * Set in __construct since __() shouldn't take a variable.
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $default_widget_title = '';


	/**
	 * Shortcode name for this widget
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected static $shortcode = 'minecraft-suite-server-status';


	/**
	 * Construct widget class.
	 *
	 * @since 0.1.0
	 * @return  null
	 */
	public function __construct() {

		$this->widget_name          = esc_html__( 'Minecraft Suite Server Status', 'minecraft-suite' );
		$this->default_widget_title = esc_html__( 'Minecraft Suite Server Status', 'minecraft-suite' );

		parent::__construct(
			$this->widget_slug,
			$this->widget_name,
			array(
				'classname'   => $this->widget_slug,
				'description' => esc_html__( 'Displays rather your server is online, or offline.', 'minecraft-suite' ),
			)
		);

		add_shortcode( self::$shortcode, array( __CLASS__, 'get_widget' ) );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $args The widget arguments set up when a sidebar is registered.
	 * @param  array $instance The widget settings as set by user.
	 *
	 * @return  null
	 */
	public function widget( $args, $instance ) {

		echo self::get_widget( array(
			'before_widget' => $args['before_widget'],
			'after_widget'  => $args['after_widget'],
			'before_title'  => $args['before_title'],
			'after_title'   => $args['after_title'],
			'title'         => $instance['title'],
			'ip_address'    => $instance['ip_address'],
			'port'          => $instance['port'],
		) );

	}


	/**
	 * Return the widget/shortcode output
	 *
	 * @since  0.1.0
	 *
	 * @param  array $atts Array of widget/shortcode attributes/args
	 *
	 * @return string       Widget output
	 */
	public static function get_widget( $atts ) {
		$widget = '';

		// Set up default values for attributes
		$atts = shortcode_atts(
			array(
				// Ensure variables
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
				'title'         => '',
				'ip_address'    => '',
				'port'          => 25565,
			),
			(array) $atts,
			self::$shortcode
		);

		if ( empty( $atts['ip_address'] ) ) {
			error_log( 'No IP address' );

			return false;
		}
		// Before widget hook
		$widget .= $atts['before_widget'];

		// Title
		$widget .= ( $atts['title'] ) ? $atts['before_title'] . esc_html( $atts['title'] ) . $atts['after_title'] : '';

		// Set the port as default just in case
		$atts['port'] = empty( $atts['port'] ) ? 25565 : $atts['port'];//
		$query_result = array();
		try {
			$query_result = new MinecraftPing( $atts['ip_address'], $atts['port'] );
		} catch ( MinecraftPingException $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( __( '%s Widget: %s' ), '[MCS]', $e->getMessage() ) );
			}
		}

		$class = ! empty( $query_result ) ? 'online' : 'offline';
		$status = ! empty( $query_result ) ? __( 'Online', 'minecraft_suite' ) : __( 'Offline', 'minecraft_suite' );

		$widget_output = "<span class='mcs_server_status $class'>$status</span>";
		$widget .= apply_filters( 'mcs_widget_status_output', $widget_output, $query_result );

		// After widget hook
		$widget .= $atts['after_widget'];

		return $widget;
	}


	/**
	 * Update form values as they are saved.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $new_instance New settings for this instance as input by the user.
	 * @param  array $old_instance Old settings for this instance.
	 *
	 * @return array  Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {

		// Previously saved values
		$instance = $old_instance;

		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['ip_address'] = $this->is_valid_ip( $new_instance['ip_address'] ) ? $new_instance['ip_address'] : '';
		$instance['port']       = ! empty( $new_instance['port'] ) ? absint( $new_instance['port'] ) : '';

		// Flush cache
		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Validates an IP address
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	private function is_valid_ip( $string ) {

		$ip_address = esc_attr( $string );
		// IP Address regex
		$reg_ex = '/^((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))*$/';
		// test input against the regular expression
		if ( preg_match( $reg_ex, $ip_address ) ) {
			return true; // it's a valid ip address
		}

		return false;
	}


	/**
	 * Back-end widget form with defaults.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $instance Current settings.
	 *
	 * @return  null
	 */
	public function form( $instance ) {

		// If there are no settings, set up defaults
		$instance = wp_parse_args( (array) $instance,
			array(
				'title'      => $this->default_widget_title,
				'ip_address' => '127.0.0.1',
				'port'       => 25565,
			)
		);

		?>
		<div class="widget minecraft_server_status">
			<p>
				<label for="mcs_title"><?php _e( 'Widget Title:', 'minecraft_suite' ); ?></label>
				<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" id="mcs_title">
			</p>
			<table class="connection-credentials">
				<tr>
					<label><?php _e( 'Connection Credentials:', 'minecraft_suite' ); ?></label>
				</tr>
				<tr>
					<td class="server-address">
						<input id="server-address" name="<?php echo $this->get_field_name( 'ip_address' ); ?>" type="text" value="<?php echo esc_attr( $instance['ip_address'] ); ?>" placeholder="<?php _e( 'IP Address', 'minecraft-suite' ); ?>"/>
					</td>
					<td class="server-port">
						<input id="server-port" name="<?php echo $this->get_field_name( 'port' ); ?>" type="text" value="<?php echo esc_attr( $instance['port'] ); ?>" placeholder="<?php _e( 'Port', 'minecraft-suite' ); ?>"/>
					</td>
				</tr>
			</table>
		</div>
		<p class="description"><?php _e( 'Input your server IP address and Port to your Minecraft server. Use 127.0.0.1 for localhost.', 'minecraft-suite' ); ?></p>
	<?php
	}
}


/**
 * Register this widget with WordPress. Can also move this function to the parent plugin.
 *
 * @since  0.1.0
 * @return  null
 */
function register_minecraft_suite_server_status() {
	register_widget( 'MS_Server_Status' );
}

add_action( 'widgets_init', 'register_minecraft_suite_server_status' );