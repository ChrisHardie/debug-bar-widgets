<?php

/**
 * Plugin Name:       Debug Bar Widgets
 * Plugin URI:        https://github.com/ChrisHardie/debug-bar-widgets
 * Description:       Add a debug bar panel to display registered widgets
 * Version:           1.0.0
 * Author:            Chris Hardie
 * Author URI:        https://chrishardie.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       debug-bar-widgets
 */

defined( 'ABSPATH' ) or die( "Please don't try to run this file directly." );

if ( ! function_exists( 'debug_bar_widgets_has_parent_plugin' ) ) {
	/**
	 * Show admin notice & de-activate if debug-bar plugin not active.
	 * Adapted from https://wordpress.org/plugins/debug-bar-shortcodes/
	 */
	function debug_bar_widgets_has_parent_plugin() {
		if ( is_admin() && ( ! class_exists( 'Debug_Bar' ) && current_user_can( 'activate_plugins' ) ) ) {
			add_action( 'admin_notices', create_function( null,
				'echo \'<div class="error"><p>\' . sprintf( __( \'Activation failed: Debug Bar must be activated to use the <strong>Debug Bar Widgets</strong> Plugin. Visit your plugins page to activate.\', \'debug-bar-widgets\' ) ) . \'</p></div>\';' ) );

			deactivate_plugins( plugin_basename( __FILE__ ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
	add_action( 'admin_init', 'debug_bar_widgets_has_parent_plugin' );
}

add_filter( 'debug_bar_panels', 'debug_bar_widgets_init' );
 
/**
 * Initialize widgets debug panel
 * Adapted from https://www.yukei.net/2015/01/adding-a-new-panel-to-the-wordpress-debug-bar-plugin/
 *
 * @param array $panels Debug bar panels objects
 * @return array Debug bar panel for registered widgets
 */
function debug_bar_widgets_init( $panels ) {

	class Debug_Bar_Widgets extends Debug_Bar_Panel{

		public function init(){

			$this->title( __( 'Registered Widgets', 'debug-bar-widgets' ) );
		}

		public function render(){

			$output = '';

			$widgets = get_widget_classes();

			if ( is_array( $widgets ) && ( 0 < count( $widgets ) ) ) {

				$output .= '<div id="debug-bar-widgets"><strong><p>';
				$output .= __( 'Registered Widgets:', 'debug-bar-widgets' );
				$output .= '</strong></p><ul>';

				foreach ( $widgets as $widget_title => $widget_class ) {

					$widget_name = $widget_class->name;
					$widget_description = $widget_class->widget_options['description'];

					$output .= sprintf( "<li>%s: %s (%s)</li>",
						esc_html( $widget_title ),
						esc_html( $widget_name ),
						esc_html( $widget_description ) );
				}

				$output .= '</ul></div>';

			} else {

				$output .= __( 'No widgets registered for this site.', 'debug-bar-widgets' );

			}

			echo $output;

		}

	}

	$panels[] = new Debug_Bar_Widgets();
	return $panels;
}


/**
 * Get all registered widgets and return the callback information
 *
 * @return array    Registered widgets
 */
function get_widget_classes() {

	global $wp_registered_widgets;

	$widgets = array();

	if ( is_array( $wp_registered_widgets ) && ( 0 < count( $wp_registered_widgets ) ) ) {

		foreach ( $wp_registered_widgets as $widget ) {

			if ( ! empty( $widget['callback'] ) ) {

				if ( ! empty( $widget['callback'][0] ) ) {

					$class = get_class( $widget['callback'][0] );

					if ( ! array_key_exists( $class, $widgets ) ) {
						$widgets[$class] = $widget['callback'][0];
					}

				}
			}
		}
	}

	return $widgets;

}
