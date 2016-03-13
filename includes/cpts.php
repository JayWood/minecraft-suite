<?php

/**
 * Loads CPTs and Taxonomies using a drop-in file method.
 * Class MS_Cpts
 */
class MS_Cpts {

	/**
	 * @var MS_Cpts
	 */
	public static $instance = null;

	/**
	 * Retains a list of class objects
	 * @var array
	 */
	protected $classes = array();

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 *
	 * @param string $dir The directory where all cpt data is stored
	 *
	 * @return MS_Cpts A single instance of this class.
	 */
	public static function get_instance( $dir = '' ) {
		if ( null == self::$instance ) {
			self::$instance = new self( $dir );
		}

		return self::$instance;
	}


	/**
	 * Sets up all CPT core and taxonomy core files
	 *
	 * @param string     $dir
	 * @param string $vendor
	 */
	public function __construct( $dir = '', $vendor = '' ) {

		if ( empty( $dir ) ) {
			$dir = dirname( __FILE__ );
		}

		/**
		 * Allow separation of cpt configs from required files
		 * Example:
		 *      new MS_Cpts( $this->dir( 'includes/cpt_config' ), $this->dir( 'includes/vendor' ) );
		 * Will load all cpt/taxonomy classes from cpt_config{CPTs/taxonomies} directory, but require the CPT_Core and Taxonomy_Core
		 * class files from includes/vendor/{include}
		 */
		$require_dir = ! empty( $vendor ) ? $vendor : $dir;

		/**
		 * Attempt to find CPT_Core file based on $require_dir
		 * if found, then look in $dir/CPTs for class files to load
		 */
		if ( file_exists( $require_dir . '/CPT_Core/CPT_Core.php' ) ) {
			require_once( $require_dir . '/CPT_Core/CPT_Core.php' );
			// CPTs directory
			$cpt_dir = $dir . '/CPTs/';
			foreach ( glob( $cpt_dir . '*.php' ) as $filename ) {
				$this->load_file( $cpt_dir, $filename );
			}
		}

		/**
		 * Attempt to find Taxonomy_Core based on $require_dir
		 * if found, then look in $dir/taxonomies for class files to load
		 */
		if ( file_exists( $require_dir . '/Taxonomy_Core/Taxonomy_Core.php' ) ) {
			require_once( $require_dir . '/Taxonomy_Core/Taxonomy_Core.php' );
			// Taxonomies directory
			$tax_dir = $dir . '/taxonomies/';
			error_log( print_r( $tax_dir, 1 ) );
			foreach ( glob( $tax_dir . '*.php' ) as $filename ) {
				$this->load_file( $tax_dir, $filename );
			}
		}
	}

	/**
	 * Dynamically loads a file and initializes the class
	 *
	 * @param string $dir
	 * @param string $filename
	 */
	public function load_file( $dir, $filename ) {
		require_once( $filename );

		// Get the php class name (same as the file name)
		$var_name = str_ireplace( array( $dir, '.php' ), '', $filename );
		$var_name = str_replace( '-', '_', $var_name ); // Swap dashes for underscores

		// Add prefix and suffix for class name
		$class_name = 'MS_' . ucfirst( $var_name );

		// Initiate a new class to a property of this class
		$this->classes[ $var_name ] = new $class_name();
	}

	/**
	 * Used to fire hooks for any CPTs or Taxonomies which have them
	 *
	 * You should call this method wherever you include this class, for instance:
	 *          $ms_cpts = new MS_Cpts( $this->dir( 'includes/cpt_config' ), $this->dir( 'includes/vendor' ) );
	 *          $ms_cpts->hooks();
	 */
	public function hooks() {
		if ( ! empty( $this->classes ) ) {
			foreach ( $this->classes as $class ) {
				if ( method_exists( $class, 'hooks' ) ) {
					$class->hooks();
				}
			}
		}
	}
}
