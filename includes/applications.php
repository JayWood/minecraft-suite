<?php

require_once plugin_dir_path( __FILE__ ) . 'vendor/CPT_Core/CPT_Core.php';

class MS_Applications extends CPT_Core {

	/**
	 * Instance of main class
	 * @var Minecraft_Suite null
	 */
	public $plugin = null;

	public function __construct( $plugin ) {

		$this->plugin = $plugin;

		add_shortcode( 'ms-application-form', array( $this, 'ms_shortcode_output' ) );

		parent::__construct(
			array(
				__( 'Application', 'ms' ),
				__( 'Applications', 'ms' ),
				'mc-applications',
			),
			array(
				'supports' => array( 'title' ),
			)
		);
	}

	public function ms_shortcode_output() {
		ob_start();
		$this->plugin->views( 'application-form' );
		return ob_get_clean();
	}
}
