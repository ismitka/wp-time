<?php
/*
 * Plugin Name: WP-Time
 * Plugin URI: https://www.smitka.net/wp-time
 * Update URI: https://www.smitka.net/wp-plugin/wp-time
 * Description: JavaScript init elements by Time
 * Version: 1.0
 * Author: Ivan Smitka
 * Author URI: http://www.smitka.net
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

	const UPDATE_HOST = "https://smitka.ismnb.smitka.net";

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
				/*
				This filter is applied inside a foreach loop in wp_update_plugins().
				So, if there a several plugins using the same hostname as Update URI, our function will be run for each of those other plugins.
				Better check if the loop has reached *our* plugin until we do anything.
				 */
				if ( $plugin_file == plugin_basename( __FILE__ ) ) {
					$request      = wp_remote_get( $plugin_data['UpdateURI'] );
					$request_body = wp_remote_retrieve_body( $request );
					$update       = json_decode( $request_body, true );
				}

				return $update;
			}, 10, 4 );
		}
	}

	public function update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = wp_remote_get(
			self::UPDATE_HOST . "/wp-plugin/wp-time",
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json'
				)
			)
		);

		if ( is_wp_error( $remote )
		     || wp_remote_retrieve_response_code( $remote ) !== 200
		     || empty( wp_remote_retrieve_body( $remote ) )
		) {
			return $transient;
		}

		$remote = json_decode( wp_remote_retrieve_body( $remote ) );

		// your installed plugin version should be on the line below! You can obtain it dynamically of course
		if ( $remote
		     && version_compare( self::VERSION, $remote->version, '<' )
		     && version_compare( $remote->requires, get_bloginfo( 'version' ), '<' )
		     && version_compare( $remote->requires_php, PHP_VERSION, '<' )
		) {

			$res                                 = new stdClass();
			$res->slug                           = $remote->slug;
			$res->plugin                         = plugin_basename( __FILE__ ); // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
			$res->new_version                    = $remote->version;
			$res->tested                         = $remote->tested;
			$res->package                        = $remote->download_url;
			$transient->response[ $res->plugin ] = $res;

			//$transient->checked[$res->plugin] = $remote->version;
		}

		return $transient;
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
