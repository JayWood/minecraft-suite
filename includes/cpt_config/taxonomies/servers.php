<?php

class MS_Servers extends Taxonomy_Core {
	public function __construct() {
		parent::__construct( array(
			__( 'Server', 'minecraft-suite'),
			__( 'Servers', 'minecraft-suite' ),
			'server'
		) );
	}
}
