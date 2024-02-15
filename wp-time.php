<?php
/*
 * Plugin Name: Time Dependent Elements
 * Plugin URI: https://www.smitka.net/wp-time
 * Update URI: https://www.smitka.net/wp-plugin/wp-time
 * Description: JavaScript init elements by Time
 * Version: 1.0
 * Author: Ivan Smitka
 * Author URI: https://www.smitka.net
 * License: The MIT License
 *
 *
 * Copyright 2023 Web4People Ivan Smitka <ivan at stimulus dot cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

class WP_Time {

	const VERSION = "1.0";

	const CRON_REGEXP = "/^(\*|[0-9,\-\/\*]+)\s+(\*|[0-9,\-\/\*]+)\s+(\*|[0-9,\-\/\*]+)\s+(\*|[0-9,\-\/\*]+)\s+(\*|[0-9,\-\/\*]+)(\s+(\*|[0-9,\-\/\*]+))?$/";

	const UPDATE_URI = "https://www.smitka.net/wp-plugin/wp-time";


	public static function init() {
		// Scripts
		if ( ! is_admin() ) { // show only in public area
			add_action( 'wp_enqueue_scripts', [
				'WP_Time',
				'enqueue_scripts'
			] );
			add_shortcode( 'wp-time', [ 'WP_Time', 'html_current' ] );
			add_shortcode( 'wp-time-on', [ 'WP_Time', 'html_on' ] );
		} else {
			add_filter( 'update_plugins_www.smitka.net', function ( $update, $plugin_data, $plugin_file, $locales ) {
				if ( $plugin_file == plugin_basename( __FILE__ ) ) {
					$update = self::getUpdate( $plugin_data['UpdateURI'] );
				}

				return $update;
			}, 10, 4 );
			add_filter( 'plugins_api', function ( $res, $action, $args ) {
				if ( 'plugin_information' !== $action ) {
					return $res;
				}
				if ( plugin_basename( __DIR__ ) !== $args->slug ) {
					return $res;
				}

				$update       = self::getUpdate( self::UPDATE_URI );
				$res          = json_decode( json_encode( $update ), false );
				$res->sections = $update["sections"];
				$res->download_link = $update["package"];

				return $res;

			}, 9999, 3 );
		}
	}

	/**
	 * @param $update_URI
	 *
	 * @return mixed
	 */
	private static function getUpdate( $update_URI ) {
		$request      = wp_remote_get( $update_URI );
		$request_body = wp_remote_retrieve_body( $request );
		$update       = json_decode( $request_body, true );

		return $update;
	}

	public static function enqueue_scripts() {
		foreach ( scandir( __DIR__ . "/dist/assets" ) as $path ) {
			$pathInfo = pathinfo( $path );
			if ( strpos( $pathInfo["filename"], "index" ) === 0 ) {
				wp_enqueue_script( 'wp-time', plugins_url( "/dist/assets/{$path}", __FILE__ ), [ 'jquery' ] );
				wp_enqueue_style( 'wp-time', plugins_url( 'static/css/time.css', __FILE__ ) );
				break;
			}
		}

	}

	public static function html_current( $args = [] ) {
		if ( ! is_array( $args ) || ! array_key_exists( "format", $args ) || empty( $format = $args["format"] ) ) {
			$format = "h:MM:ss";
		}
		ob_start();
		?>
        <span data-time="<?= $format ?>"></span>
		<?php
		return ob_get_clean();
	}

	public static function html_on( $args = [], $content = null ) {
		if ( is_array( $args ) ) {
			$on  = array_key_exists( "on", $args ) ? $args["on"] : null;
			$off = array_key_exists( "off", $args ) ? $args["off"] : null;
		}
		ob_start();
		if ( $on && $off && preg_match( self::CRON_REGEXP, $on ) && preg_match( self::CRON_REGEXP, $off ) ) {
			?>
            <span data-on-time='{"on":"<?= $on ?>","off":"<?= $off ?>"}'><?= do_shortcode( $content ) ?></span>
			<?php
		}

		return ob_get_clean();
	}
}

add_action( 'plugins_loaded', [
	'WP_Time',
	'init'
], 100 );
