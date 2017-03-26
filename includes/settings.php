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

	private function display_desc_field( $args ) {
		if ( isset( $args['desc'] ) ) : ?>
			<p class="description"><?php esc_attr_e( $args['desc'] ); ?></p>
		<?php endif;
	}

	public function input_number( $args ) {
		?>
		<input type="number" name="<?php echo $args['id']; ?>" value="<?php echo intval( get_option( $args['id'], '' ) ); ?>" />
		<?php
		$this->display_desc_field( $args );
	}

	public function input_textarea( $args ) {
		?>
		<textarea name="<?php echo $args['id']; ?>" cols="80" rows="10"><?php echo esc_attr( get_option( $args['id'] ), '' ); ?></textarea>
		<?php
		$this->display_desc_field( $args );
	}

	public function input_textbox( $args ) {
		?>
		<input type="text" name="<?php echo $args['id']; ?>" value="<?php echo esc_attr( get_option( $args['id'] ), '' ); ?>" />
		<?php
		$this->display_desc_field( $args );
	}

	public function input_password( $args ) {
		?>
		<input type="password" name="<?php echo $args['id']; ?>" value="<?php echo esc_attr( get_option( $args['id'] ), '' ); ?>" />
		<?php
		$this->display_desc_field( $args );
	}

	public function input_checkbox( $args ) {
		?>
		<input type="checkbox" name="<?php echo $args['id']; ?>" value="on" <?php checked( esc_attr( get_option( $args['id'] ) ), 'on' ); ?>/>
		<?php
		$this->display_desc_field( $args );
	}
}
