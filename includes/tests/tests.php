<?php
class Bhu_Tests {

	public static $config = array(
		'php' => '5.4',
		'mysql' => '5.0.41',
		'wp'  => '3.8'
	);
        public static $errors = array(); // Any errors thrown.

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'BHUUFU/activation', array( __CLASS__, 'php' ) );
		add_action( 'BHUUFU/activation', array( __CLASS__, 'wp' ) );
		add_action( 'BHUUFU/activation', array( __CLASS__, 'mysql' ) );
		add_action( 'BHUUFU/activation', array( __CLASS__, 'print_notices' ) );
                do_action( 'BHUUFU/activation');
	}
        
	/**
	 * Print errors if any and exit the plugin
	 */
        public static function print_notices() {
        if (!empty(self::$errors)) {
            $error_items = '';
            foreach (self::$errors as $e) {
                $error_items .= "<li>$e</li>";
            }
            $msg = sprintf( __( '<div id="my-plugin-error" class="error"><p><strong>The &quot; %s &quot; plugin encountered errors! It cannot load!</strong><ul style="margin-left:30px;">%s</ul></p></div>', 'BHUUFU_LNG' ),BHUUFU_PLUGIN_NAME,$error_items);
            wp_die($msg);
        }
    }

	/**
	 * Check PHP version
	 */
	public static function php() {
		$php = phpversion();
		load_plugin_textdomain( 'BHUUFU_LNG', false, dirname( plugin_basename( BHUUFU_PLUGIN_FILE ) ), '/languages/' );
		$msg = sprintf( __( '<p>%s is not fully compatible with your PHP version (%s).<br />Reccomended PHP version &ndash; %s (or higher).</p><a href="%s">&larr; Return to the plugins screen</a>  ', 'BHUUFU_LNG' ),BHUUFU_PLUGIN_NAME, $php, self::$config['php'], network_admin_url( 'plugins.php' ));
		if ( version_compare( self::$config['php'], $php, '>' ) ) {
			self::$errors[] = $msg;
		}
	}

	/**
	 * Check WordPress version
	 */
	public static function wp() {
		$wp = get_bloginfo( 'version' );
		load_plugin_textdomain( 'BHUUFU_LNG', false, dirname( plugin_basename( BHUUFU_PLUGIN_FILE ) ), '/languages/' );
		$msg = sprintf( __( '<p>%s is not fully compatible with your version of WordPress (%s).<br />Reccomended WordPress version &ndash; %s (or higher).</p><a href="%s">&larr; Return to the plugins screen</a>', 'BHUUFU_LNG' ),BHUUFU_PLUGIN_NAME, $wp, self::$config['wp'], network_admin_url( 'plugins.php' ) );
		if ( version_compare( self::$config['wp'], $wp, '>' ) ) {
			self::$errors[] = $msg;
		}
	}
        
        // INPUT: minimum req'd version of MySQL, e.g. 5.0.41
        public static function mysql() {
            global $wpdb;
            load_plugin_textdomain( 'BHUUFU_LNG', false, dirname( plugin_basename( BHUUFU_PLUGIN_FILE ) ), '/languages/' );
            $result = $wpdb->get_results('SELECT VERSION() as ver');
            $msg = sprintf( __( '<p>%s is not fully compatible with your MySql version (%s).<br />Reccomended MySql version &ndash; %s (or higher).</p><a href="%s">&larr; Return to the plugins screen</a>', 'BHUUFU_LNG' ),BHUUFU_PLUGIN_NAME, $result[0]->ver, self::$config['mysql'], network_admin_url( 'plugins.php' ) );
            if ( version_compare( self::$config['mysql'], $result[0]->ver, '>' ) ) {
                    self::$errors[] = $msg;
            }
        }
}
//move this to activate class
new Bhu_Tests;