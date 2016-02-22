<div id="ms-application-form">
	<table>
		<tr>
			<td><label for="mc-username"><?php _e( 'Minecraft Username', 'ms' ); ?></label></td>
			<td><input type='text' id="mc-username" name="ms-username" /></td>
		</tr>
		<tr>
			<td><label for="age"><?php _e( 'Your Age', 'ms' ); ?></label></td>
			<td><input type='number' id="age" name="age" /></td>
		</tr>
		<tr>
			<td><label for="email"><?php _e( 'eMail Address', 'ms' ); ?></label></td>
			<td><input type='text' id="email" name="email" /></td>
		</tr>
		<tr>
			<td><label for="age"><?php _e( 'Why do you want to play on the server?', 'ms' ); ?></label></td>
			<td><textarea name="reason" id="reason"></textarea></td>
		</tr>
		<?php do_action( 'ms_after_application_fields' ); ?>
	</table>
</div>
