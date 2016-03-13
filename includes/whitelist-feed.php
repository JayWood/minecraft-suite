<?php

/**
 * A JSON Feed for the Whitelist Manager
 */
class MS_Whitelist_Feed {

	/**
	 * @var Minecraft_Suite
	 */
	protected $plugin;

	/**
	 * @param Minecraft_Suite $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	public function hooks() {
		add_action( 'template_redirect', array( $this, 'display_whitelist' ) );
	}

	public function display_whitelist() {
		if ( ! isset( $_GET['mcss-whitelist-feed'] ) ) {
			return;
		}

		$query_args = array(
			'post_type'      => 'mc-applications',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'mc-username',
					'compare' => 'EXISTS',
				),
			),
		);

		if ( isset( $_GET['server'] ) ) {
			$server = esc_attr( $_GET['server'] );
			if ( ! term_exists( 'server', $server ) ) {
				wp_send_json_error( array( 'msg' => sprintf( __( "Requested server '%s' does not exist.", 'minecraft-suite' ), $server ) ) );
			}

			$query_args['server'] = $server;
		}

		$query_args           = apply_filters( 'mc_server_suite_whitelist_query', $query_args );
		$white_listed_players = get_posts( $query_args );

		if ( empty( $white_listed_players ) ) {
			wp_send_json_success( array() );
		}

		$players = array();
		foreach ( $white_listed_players as $application_id ) {
			$player_name = get_post_meta( $application_id, 'mc-username', true );
			if ( ! empty( $player_name ) ) {
				$players[] = $player_name;
			}
		}

		wp_send_json_success( $players );
	}
}
