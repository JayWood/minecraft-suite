<?php

class MS_Settings {

	/**
	 * @param Minecraft_Suite $plugin
	 */
	protected $plugin;

	/**
	 * @var string Options prefix
	 */
	protected $pre;

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->pre = $this->plugin->option_prefix;
	}

	public function security_group( $args ) {
	}

	public function registration_group( $args ) {
//		do_settings_fields( $this->plugin->options_page, 'mcs-registration' );
	}

	public function number_input( $args ) {
		?>
		<input type="number" name="<?php echo $args['name']; ?>" value="<?php echo intval( get_option( $args['name'], '' ) ); ?>" />
		<?php if ( isset( $args['desc'] ) ) : ?>
			<p class="description"><?php esc_attr_e( $args['desc'] ); ?></p>
		<?php endif; ?>
		<?php
	}

}