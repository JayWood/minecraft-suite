<?php

class MS_Servers extends Taxonomy_Core {
	public function __construct() {
		parent::__construct( array(
			__( 'Server', 'minecraft-suite' ),
			__( 'Servers', 'minecraft-suite' ),
			'server',
		), array(), array( 'mc-applications' ) );
	}

	public function hooks() {
		if ( minecraft_suite()->is_multicraft_enabled() ) {
			add_action( $this->taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ) );
			add_action( $this->taxonomy . '_add_form_fields', array( $this, 'add_form_fields' ) );

			add_action( 'created_' . $this->taxonomy, array( $this, 'save_term_meta' ) );
			add_action( 'edited_' . $this->taxonomy, array( $this, 'save_term_meta' ) );

			add_filter( 'manage_edit-' . $this->taxonomy . '_columns', array( $this, 'tax_columns' ) );
			add_filter( 'manage_' . $this->taxonomy . '_custom_column', array( $this, 'tax_col_content' ), 10, 3 );
		}
	}

	public function tax_col_content( $content, $col_name, $term_id ) {
		if ( 'server_id' == $col_name ) {
			$server_id = get_term_meta( $term_id, 'server_id', true );
			if ( empty( $server_id ) ) {
				return sprintf( '<span style="color: red;">%s</span>', __( 'NONE', 'minecraft-suite' ) );
			}

			return absint( $server_id );
		}

		return $content;
	}

	public function tax_columns( $cols ) {

		$cols['server_id'] = __( 'Server ID', 'minecraft-suite' );

		return $cols;
	}

	public function save_term_meta( $term_id ) {
		if ( isset( $_POST['server_id'] ) ) {
			update_term_meta( $term_id, 'server_id', absint( $_POST['server_id'] ) );
		}
	}

	public function edit_form_fields( $term ) {
		$server_id = get_term_meta( $term->term_id, 'server_id', true );
		?>
		<tr class="form-field term-group-wrap">
			<th scope="row">
				<label for="server_id"><?php _e( 'Multicraft Server ID', 'minecraft-suite' ); ?></label>
			</th>
			<td>
				<input type="text" id="server_id" name="server_id" value="<?php echo absint( $server_id ); ?>" />
			</td>
		</tr>
		<?php
	}

	public function add_form_fields() {
		?>
		<div class="form-field term-group">
			<label for="server_id"><?php _e( 'Multicraft Server ID', 'minecraft-suite' ); ?></label>
			<input type="text" id="server_id" name="server_id" />
		</div>
		<?php
	}
}
