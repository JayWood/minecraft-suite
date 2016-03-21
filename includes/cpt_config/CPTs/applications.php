<?php
class MS_Applications extends CPT_Core {

	/**
	 * Instance of main class
	 * @var Minecraft_Suite null
	 */
	public $plugin = null;

	public function __construct() {

		add_shortcode( 'ms-application-form', array( $this, 'ms_shortcode_output' ) );

		parent::__construct(
			array(
				__( 'Application', 'ms' ),
				__( 'Applications', 'ms' ),
				'mc-applications',
			),
			array(
				'supports' => array( 'title', 'editor', 'custom-fields' ),
			)
		);
	}

	public function ms_shortcode_output() {
		ob_start();
		minecraft_suite()->views( 'application-form' );
		return ob_get_clean();
	}
}
