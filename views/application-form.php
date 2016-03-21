<form method="POST" id="ms-application-form">
	<div class="status_message"></div>
	<?php wp_nonce_field( 'mcs-nonce' ); ?>
	<input type="hidden" name="action" value="minecraft-server-suite"/>

	<p><label for="server-id"><?php _e( 'Which server are you applying to?', 'ms' ); ?></label><br />
	<select name="server-id" id="server-id">
		<option><?php _e( '-- Select A Server --', 'ms' ); ?></option>
		<?php foreach ( minecraft_suite()->get_servers() as $server_key => $server_name ) : ?>
			<option value="<?php echo $server_key; ?>"><?php echo $server_name; ?></option>
		<?php endforeach; ?>
	</select></p>

	<p><label for="mc-username"><?php _e( 'Minecraft Username', 'ms' ); ?></label>
	<input type='text' id="mc-username" name="ms-username"/></p>

	<p><label for="age"><?php _e( 'Your Age', 'ms' ); ?></label>
	<input type='number' id="age" name="age"/></p>

	<p><label for="email"><?php _e( 'eMail Address', 'ms' ); ?></label>
	<input type='text' id="email" name="email"/></p>

	<p><label for="age"><?php _e( 'Why do you want to play on the server?', 'ms' ); ?></label>
	<textarea name="reason" id="reason"></textarea></p>

	<?php do_action( 'ms_after_application_fields' ); ?>
	<input type="button" class="button-primary mcs-submit" value="<?php _e( 'Submit', 'wmc' ); ?>"/>
</form>
