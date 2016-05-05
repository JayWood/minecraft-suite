<?php

/**
 * Class MS_Apps
 *
 * Handles all 'Applications' related functionality
 */
class MS_Apps {

	/**
	 * @var Minecraft_Suite
	 */
	protected $plugin;

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	public function hooks() {
		add_action( 'wp_ajax_nopriv_minecraft-server-suite', array( $this, 'handle_ajax' ) );
		add_action( 'wp_ajax_minecraft-server-suite', array( $this, 'handle_ajax' ) );
		add_action( 'admin_menu', array( $this, 'add_pending_apps_menu' ) );
		add_filter( 'gettext', array( $this, 'publish_to_approve' ), 10, 2 );
		add_action( 'publish_mc-applications', array( $this, 'send_approved_email' ), 10, 2 );
	}

	/**
	 * @param int $post_id
	 * @param WP_Post $post
	 *
	 * @author JayWood
	 */
	public function send_approved_email( $post_id, $post ) {

		if ( 'mc-applications' !== $post->post_type ) {
			return;
		}

		$user_email = get_post_meta( $post_id, 'email', true );
		$username = get_post_meta( $post_id, 'mc-username', true );
		if ( empty( $user_email ) || empty( $username ) ) {
			return;
		}

		$subject = sprintf( __( 'Whitelist Application Approved: %s', 'minecraft-suite' ), $username );
		$to[] = sprintf( '%s <%s>', $username, $user_email );
		$message = get_option( 'mcs_email-body', '' );
		$headers[] = '';

		wp_mail( $to, $subject, $message );
	}

	public function handle_ajax() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mcs-nonce' ) ) {
			wp_send_json_error( __( 'Internal Server Error', 'minecraft-suite' ) );
		}

		$age = absint( $_POST['age'] );
		$username = esc_attr( $_POST['ms-username'] );
		$email = sanitize_email( $_POST['email'] );
		$reason = wp_kses_post( $_POST['reason'] );
		$server = esc_attr( $_POST['server-id'] );

		if ( ! array_key_exists( $server, $this->_get_available_servers() ) ) {
			wp_send_json_error( __( 'The specified server does not exist, please select a valid server.', 'minecraft-suite' ) );
		}

		if ( $age > 100 ) {
			wp_send_json_error( __( 'You are WAY too old to be playing Minecraft!!!', 'minecraft-suite' ) );
		}

		if ( $age < $this->get_minimum_age() ) {
			wp_send_json_error( __( 'You do not meet the minimum age requirements to play on this server.', 'minecraft-suite' ) );
		}

		if ( empty( $email ) ) {
			wp_send_json_error( __( 'Please enter a valid email address', 'minecraft-suite' ) );
		}

		if ( empty( $age ) || empty( $email ) || empty( $username ) || empty( $reason ) ) {
			wp_send_json_error( __( 'All fields are required.', 'minecraft-suite' ) );
		}


		if ( false !== $status = $this->user_has_application( $username ) ) {
			wp_send_json_error( sprintf( __( 'You already have an active application with a status of [ %s ]', 'minecraft-suite' ), $status ) );
		}

		$insert_results = wp_insert_post( array(
			'post_type' => 'mc-applications',
			'post_status' => 'pending',
			'post_title' => sprintf( __( 'Whitelist Application for: %s', 'minecraft-suite' ), $username ),
			'post_content' => $reason,
		), true );

		if ( ! is_wp_error( $insert_results ) && ! empty( $insert_results ) ) {
			foreach ( array( 'age' => $age, 'mc-username' => $username, 'email' => $email ) as $k => $v ) {
				update_post_meta( $insert_results, $k, $v );
			}

			wp_set_object_terms( $insert_results, intval( $server ), 'server', false );

			wp_send_json_success( __( 'Success! - Your application is now pending approval by a member of the team. You will receive an email when your application has been reviewed along with our decision.', 'minecraft-suite' ) );
		}

		wp_send_json_error( __( 'An unspecified error occurred, please contact an admin, or try again later', 'minecraft-suite' ) );

	}

	/**
	 * Gets a list of servers, and returns them in an associative array.
	 * @return array
	 */
	public function _get_available_servers() {
		$terms = get_terms( 'server', array( 'hide_empty' => false, 'fields' => 'id=>name' ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			$terms = array();
		}
		return $terms;
	}

	public function get_minimum_age() {
		return 12;
	}

	/**
	 * Determines if a user already has an application or not.
	 *
	 * @since 0.2.0
	 * @param $username
	 *
	 * @return false|string False if no application, status string otherwise.
	 */
	public function user_has_application( $username ) {
		$post_query = get_posts( array(
			'post_type' => 'mc-applications',
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
			$status = get_post_status( $post_query[0] );
		}

		return $status;
	}

	/**
	 * Adds a 'pending' bubble to the applications menu item
	 *
	 * @since 0.2.0
	 */
	public function add_pending_apps_menu() {
		global $menu;

		foreach ( $menu as $key => $menu_entry ) {
			if ( isset( $menu_entry[5] ) && 'menu-posts-mc-applications' == $menu_entry[5] ) {
				$posts = get_posts( array(
					'post_type'      => 'mc-applications',
					'post_status'    => 'pending',
					'posts_per_page' => - 1,
					'fields'         => 'ids',
				) );

				if ( empty( $posts ) ) {
					return;
				}

				$post_count = count( $posts );

				$menu[ $key ][0] .= sprintf( ' <span class="update-plugins count-%1$d"><span class="plugin-count">%1$d</span></span>', $post_count );
				return;
			}
		}
	}

	/**
	 * Changes the publish button to 'Approve'
	 *
	 * @since 0.2.0
	 * @param $translated_text
	 * @param $text
	 *
	 * @return string
	 */
	public function publish_to_approve( $translated_text, $text ) {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return $translated_text;
		}

		$screen = get_current_screen();
		if ( isset( $screen->post_type ) && 'mc-applications' == $screen->post_type && 'Publish' == $text ) {
			return 'Approve';
		}

		return $translated_text;
	}
}
