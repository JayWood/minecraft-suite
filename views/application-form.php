<form method="POST" id="ms-application-form">
	<?php wp_nonce_field( 'mcs-nonce' ); ?>
	<input type="hidden" name="action" value="minecraft-server-suite"/>

	<label for="mc-username"><?php _e( 'Minecraft Username', 'ms' ); ?></label>
	<input type='text' id="mc-username" name="ms-username"/>

	<label for="age"><?php _e( 'Your Age', 'ms' ); ?></label>
	<input type='number' id="age" name="age"/>

	<label for="email"><?php _e( 'eMail Address', 'ms' ); ?></label>
	<input type='text' id="email" name="email"/>

	<label for="age"><?php _e( 'Why do you want to play on the server?', 'ms' ); ?></label>
	<textarea name="reason" id="reason"></textarea>

	<?php do_action( 'ms_after_application_fields' ); ?>
	<input type="button" class="button-primary mcs-submit" value="<?php _e( 'Submit', 'wmc' ); ?>"/>
</form>
