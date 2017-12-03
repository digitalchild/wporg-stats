<?php
/**
 * Plugin Name:         WPOrg Stats 
 * Plugin URI:          https://github.com/digitalchild/wporg-stats
 * Description:         Display WordPress.org plugin or theme information on your site. 
 * Author:              Jamie Madden (https://github.com/digitalchild)
 * Author URI:          https://github.com/digitalchild
 * GitHub Plugin URI:   https://github.com/digitalchild/wporg-stats
 *
 * Version:              1.0.0
 * Requires at least:    4.4.0
 * Tested up to:         4.9.1
 *
 * Text Domain:         wporgstats
 * Domain Path:         /languages/
 *
 * @category            Plugin
 * @copyright           Copyright © 2017 Jamie Madden
 * @author              Jamie Madden
 * @package             WPOrg_Stats 
 * @license     		GPL2

WPOrg Stats is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
WPOrg Statss is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with WPOrg Stats. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.

*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class WPOrg_Stats { 

	/**
	 * WPOrg Stats version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * The single instance of the class.
	 *
	 * @var WPOrg_Stat
	 * @since 2.0
	 */
	protected static $instance = null;

	/**
	 * @var WC_Logger Reference to logging class.
	 */
	private static $log;

	/**
	 * @var bool Enable debug logging.
	 */
	public static $enable_logging;


	/**
	 * Main WPOrg Stats Instance.
	 *
	 * Ensures only one instance of WPOrg Stats is loaded or can be loaded.
	 *
	 * @since 2.0
	 * @static
	 * @return WPOrg Stats - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * WPOrg Stats Constructor.
	 */
	public function __construct() {

		$this->define_constants();
		$this->init_hooks(); 
		
		self::$enable_logging = apply_filters( 'wpps_enable_logging', true ); 

		do_action( 'wpps_loaded' );
	}

	/**
	 * Cloning is forbidden.
	 * @since 2.0
	 */
	public function __clone() {
		self::log( __( 'No cloning allowed', 'wporgstats' ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 2.0
	 */
	public function __wakeup() {
		self::log( __( 'No wakeup allowed', 'wporgstats' ) );
	}

	/**
	 * Hook into actions and filters.
	 * @since  2.0
	 */
	private function init_hooks() {
		add_shortcode( 'wpps_show_plugin_info', array( $this, 'wpps_show_plugin_info' ) ); 
		add_shortcode( 'wpps_show_theme_info', 	array( $this, 'wpps_show_theme_info' ) ); 
	}

	/**
	 * Define WC Constants.
	 */
	private function define_constants() {

		$this->define( 'WPPS_PLUGIN_FILE', __FILE__ );
		$this->define( 'WPPS_ABSPATH', dirname( __FILE__ ) . '/' );
		$this->define( 'WPPS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'WPPS_VERSION', $this->version );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the template path.
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'wpps_template_path', 'wporg-stats/' );
	}

	/*
	*	Show plugin stats from wordpress.org 
	*/ 
	public function wpps_show_plugin_info( $atts ){ 

		extract( shortcode_atts( array(
			'slug' 	=> '', 
			'info' 	=> 'downloaded'
		), $atts ) );

		// No slug provided, bail 
		if ( ! $slug ) return; 

		$stats = $this->get_wp_plugin_info( $slug ); 

		if ( is_numeric( $stats->$stat ) ){ 
			return number_format( $stats->$stat ); 
		} else { 
			return  $stats->$stat; 
		}

	}

	/*
	*	Show theme stats from wordpress.org 
	*/ 
	public function wpps_show_theme_info( $atts ){ 

		extract( shortcode_atts( array(
			'slug' 	=> '', 
			'info' 	=> 'downloaded'
		), $atts ) );

		// No slug provided, bail 
		if ( ! $slug ) return; 

		$stats = $this->get_wp_theme_info( $slug ); 

		if ( $all ){ 
			return print_r( $stats ); 
		}

		if ( is_numeric( $stats->$stat ) ){ 
			return number_format( $stats->$stat ); 
		} else { 
			return  $stats->$stat; 
		}

	}



	/**
	*	Get the plugin information from wp.org 
	* 	Cache the results to ensure fast access 
	*
	*/
	public function get_wp_plugin_info( $slug ){ 

		// $url = 'https://api.wordpress.org/plugins/info/1.0/' . $slug . '.json';
		
		$url = 'https://wordpress.org/plugins/wp-json/plugins/v1/plugin/'. $slug; 

		// Get any existing copy of our transient data
		if ( false === ( $wp_org_response = get_transient( 'wpps_plugin_' . $slug . '_results' ) ) ) {

		    $wp_org_response = wp_remote_request( $url, array( 'method' => 'GET' ) ); 

		    if ( is_wp_error( $wp_org_response ) ) {
				return $wp_org_response->get_error_message();
			}

		    set_transient( 'wpps_plugin_' . $slug . '_results', $wp_org_response, 12 * HOUR_IN_SECONDS );
		}

		return json_decode( wp_remote_retrieve_body( $wp_org_response ) ); 
	}


	/**
	*	Get theme information from wp.org 
	*	Cache the results to ensure fast access. 
	*/ 
	public function get_wp_theme_info( $slug ){ 

		$url = 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $slug; 


		// Get any existing copy of our transient data
		if ( false === ( $wp_org_response = get_transient( 'wpps_theme_' . $slug . '_results' ) ) ) {

		    $wp_org_response = wp_remote_request( $url, array( 'method' => 'GET' ) ); 

		    if ( is_wp_error( $wp_org_response ) ) {
				return $wp_org_response->get_error_message();
			}

		    set_transient( 'wpps_theme_' . $slug . '_results', $wp_org_response, 12 * HOUR_IN_SECONDS );
		}

		self::log( json_decode( wp_remote_retrieve_body( $wp_org_response ) ) ); 

		return json_decode( wp_remote_retrieve_body( $wp_org_response ) ); 

	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/wporg-stats/wporg-stats-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/wporg-stats-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wporgstats' );
		
		load_textdomain( 'wporgstats', WP_LANG_DIR . '/wporg-stats/wporgstats-' . $locale . '.mo' );
		load_plugin_textdomain( 'wporgstats', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Class logger so that we can keep our debug and logging information cleaner 
	 *
	 * @since 1.0.0 
	 * @access public
	 * 
	 * @param mixed - $data the data to go to the error log could be string, array or object
	 */
	public function log( $data = '', $pre = '' ){ 

		$trace 	= debug_backtrace( false, 2 ); 
		$caller = ( isset( $trace[ 1 ] ) ) ? array_key_exists( 'class', $trace[ 1 ] ) ? $trace[ 1 ][ 'class' ] : '' : ''; 

		if ( self::$enable_logging ){ 
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				if ( is_array( $data ) || is_object( $data ) ) { 
					
					// Output to the error log 
					error_log( '===================    ' . $pre .' : ' . $caller . '   ======================' ); 
					error_log( $caller . ' : ' . print_r( $data, true ) ); 
					error_log( '==============================================================='); 
				} else { 

					// Output to debugging log 
					error_log( '===================    '  . $pre .' : ' . $caller . '   ======================' ); 
					error_log( $caller  . ' : ' . $data );
					error_log( '==============================================================='); 
				}
			}
		}
	} 

} // End final class 

/**
 * Main instance of WPOrg Stats 
 * 
 */
function WPOrg_Stats(){ 
	return WPOrg_Stats::instance(); 
}

add_action( 'plugins_loaded', 'WPOrg_Stats' );


