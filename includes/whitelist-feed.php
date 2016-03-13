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
		if ( ! isset( $_GET['ms_whitelist_manager'] ) ) {
			return;
		}

		$query_args = array(
			'post_type'      => 'mc-applications',
			'post_status'    => 'draft',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'ms-username',
					'compare' => 'EXISTS',
				),
			)
		);

		wp_send_json_success( array( 'yep' ) );
	}

}
