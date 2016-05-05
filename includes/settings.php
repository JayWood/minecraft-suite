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

	public function default_group() {}

	public function input_number( $args ) {
		?>
		<input type="number" name="<?php echo $args['id']; ?>" value="<?php echo intval( get_option( $args['id'], '' ) ); ?>" />
		<?php if ( isset( $args['desc'] ) ) : ?>
			<p class="description"><?php esc_attr_e( $args['desc'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	public function input_textarea( $args ) {
		?>
		<textarea name="<?php echo $args['id']; ?>" cols="80" rows="10"><?php echo esc_attr( get_option( $args['id'] ), '' ); ?></textarea>
		<?php if ( isset( $args['desc'] ) ) : ?>
			<p class="description"><?php esc_attr_e( $args['desc'] ); ?></p>
		<?php endif;
	}
}
