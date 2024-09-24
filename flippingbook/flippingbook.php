<?php
/**
 * FlippingBook
 *
 * @package           FlippingBook
 * @author            FlippingBook
 * @copyright         2021 FlippingBook
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         FlippingBook
 * Plugin URI:          https://flippingbook.com/wordpress
 * Description:         FlippingBook plugin allows you to easily embed flipbooks made with FlippingBook into your Wordpress posts and pages.
 * Version:             2.0.1
 * Requires at least:   3.2
 * Requires PHP:        5.5.0
 * Author:              FlippingBook
 * Author URI:          http://flippingbook.com
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         flippingbook
 */

function stringify_array(string $separator, array $array): string
{
	$string = '';
	foreach ($array as $i => $a) {
		if (is_array($a)) {
			$string .= stringify_array($separator, $a);
		} else {
			$string .= $i.$separator.$a;
			if ($i < count($array) - 1) {
				$string .= $separator;
			}
		}
	}

	return $string;
}

class Flippingbook {
    public $default_cache_timeout = 86400;
	public $minimal_cache_timeout = 3600;
    public $default_oembed_ratio = '16:9';

    function __construct() {
	    $flippingbook_options = get_option( 'flippingbook_options' );

		/**
		 * Adding FlippingBook oEmbed providers to Wordpress allow list
		 */

		wp_oembed_add_provider( 'https://online.flippingbook.com/view/*', 'https://online.flippingbook.com/oembed' );
		wp_oembed_add_provider( 'https://cld.bz/*', 'https://cld.bz/__oembed' );
		wp_oembed_add_provider( 'https://*.cld.bz/*', 'https://cld.bz/__oembed' );

	    add_filter( 'oembed_dataparse', array( $this, 'process_flipingbook_oembed' ), 10, 2 );

		if ( !empty($flippingbook_options) ) {
		    if ( !empty($flippingbook_options['custom_domain']) ) {
			    wp_oembed_add_provider( 'https://'.$flippingbook_options['custom_domain'].'/*', 'https://online.flippingbook.com/oembed' );
			}
        }

		add_shortcode( 'flippingbook', array( $this, 'flippingbook_shortcode_handler' ) );
	    add_action( 'admin_init', array( $this, 'flippingbook_settings_init' ) );
	    add_action( 'admin_menu', array( $this, 'flippingbook_options_page' ) );
	}

	function process_flipingbook_oembed($return, $data) {
		if ( 'FlippingBook' === $data->provider_name || 'FlippingBook Cloud' === $data->provider_name) {
			$flippingbook_options = get_option( 'flippingbook_options' );
		    if ( !('FlippingBook Cloud' === $data->provider_name && !$this->check_publisher_version($return)) && !empty($flippingbook_options && !empty($flippingbook_options['responsive_oembed']) && '1' === $flippingbook_options['responsive_oembed']) ) {
			    $ratio = !empty( $flippingbook_options['oembed_ratio'] ) ? $flippingbook_options['oembed_ratio'] : $this->default_oembed_ratio;
			    $return = $this->process_oembed_html($return, $ratio);
            }
			$return = $this->add_embed_method($return, 'oEmbed');
		}
		return $return;
	}


	function check_publisher_version($html) {
        $pattern = '/data-fb\w{1}-version=\"([\d.]*)\"/';
        preg_match($pattern, $html, $matches);
        return isset($matches[1]) && version_compare($matches[1], '2022.1.100', '>=');
    }

	function process_oembed_html($html, $ratio) {
		$patterns = array ( '/data-fb(\w)-width="\w*%?"/', '/data-fb(\w)-height="\w*%?"/' );
		$replacements = array( 'data-fb${1}-width="100%"', 'data-fb${1}-height="auto" data-fb${1}-ratio="'.$ratio.'"' );
		return preg_replace($patterns, $replacements, $html);
	}

	function add_embed_method ($html, $method) {
		$patterns = array ( '/data-fb(\w)-version="([\w.]*)"/' );
		$replacements = array( 'data-fb${1}-version="${2}" data-fb${1}-method="'.$method.'"' );
		return preg_replace($patterns, $replacements, $html);
	}

	function fix_size_for_oembed($args) {
		$patterns = array ( '/px/', '/\d*%/', '/auto/');
		$replacements = array( '', '500', '500');

		$args['width'] = preg_replace($patterns, $replacements, $args['width']);
		$args['height'] = preg_replace($patterns, $replacements, $args['height']);

        return $args;
    }

	function process_shortcode_size($html, $ratio = NULL, $width = NULL, $height = NULL) {
		if ( $ratio && !$width && !$height ) {
			$patterns = array ( '/data-fb(\w)-width="\w*%?"/', '/data-fb(\w)-height="\w*%?"/' );
			$replacements = array( 'data-fb${1}-width="100%"', 'data-fb${1}-height="auto" data-fb${1}-ratio="'.$ratio.'"' );
			return preg_replace($patterns, $replacements, $html);
		}
        if ( $ratio ) {
	        $patterns = array ( '/data-fb(\w)-height="(\w*%?)"/' );
	        $replacements = array( 'data-fb${1}-height="${2}" data-fb${1}-ratio="'.$ratio.'"' );
	        $html = preg_replace($patterns, $replacements, $html);
        }
		if ( $width ) {
			$patterns = array ( '/data-fb(\w)-width="\w*%?"/' );
			$replacements = array( 'data-fb${1}-width="'.$width.'"' );
			$html = preg_replace($patterns, $replacements, $html);
		}
		if ( $height ) {
			$patterns = array ( '/data-fb(\w)-height="\w*%?"/' );
			$replacements = array( 'data-fb${1}-height="'.$height.'"' );
			$html = preg_replace($patterns, $replacements, $html);
		}
		return $html;
	}

	/**
     * Handler for a [flippingbook] shortcode
     *
	 * @param array $attrs
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string - HTML embed code for flipbook
	 */
	function flippingbook_shortcode_handler( $attrs, $content, $tag ): string {
		$wp_oembed = _wp_oembed_get_object();
		$url = parse_url( $content );

		$options = get_option('flippingbook_options');
		$shortcode_hash = sha1($content . '_' . (is_array($attrs) ? stringify_array('_', $attrs) : $attrs));
		$embed_transient = get_transient( 'flippingbook_'.$shortcode_hash );

		if ( !empty( $embed_transient ) ){
		    return $embed_transient['embed_html'];
		}

		if ( ! empty( $url ) ) {
			$a = shortcode_atts(
				array(
					'width'     => NULL,
					'height'    => NULL,
					'lightbox'  => true,
					'title'     => NULL,
					'mode'      => NULL,
					'page'      => NULL,
					'wheel'     => NULL,
					'analytics' => NULL,
					'ratio'     => NULL,
					'thumbnail' => NULL,
					'legacy'    => NULL
				), $attrs, $tag );

			$orig_a = $a;
			$a = $this->fix_size_for_oembed($a);
//			add_filter('https_ssl_verify', '__return_false');
			$content = trim($content);
			$oembed_url = $wp_oembed->discover( $content );
			if (! $oembed_url ) {
				return __('Error embedding FlippingBook shortcode, please check the flipbook url. ('.$content.')', 'flippingbook');
			}
			$oembed_url_parts = explode("?", $oembed_url);
			$provider_url = $oembed_url_parts[0];
			$fetch_url = $provider_url . '?url='. $content . '&' . http_build_query($a);
			$embed_data = $wp_oembed->__call('_fetch_with_format', array( $fetch_url, 'json' ));

			if (!is_wp_error($embed_data)) {
				if (! empty( $embed_data ) && ! empty( $embed_data->html ) && is_string( $embed_data->html ) ) {
                    $embed_html = $this->process_shortcode_size($embed_data->html, $orig_a['ratio'], $orig_a['width'], $orig_a['height']);
					$embed_html = $this->add_embed_method($embed_html, 'wp');

					$embed_transient = array(
						'publication_url' => $content,
						'attrs' => $a,
						'embed_html' => $embed_html
					);
					set_transient('flippingbook_'.$shortcode_hash, $embed_transient, DAY_IN_SECONDS );
					return $embed_html;
				} else {
					return __('Error embedding FlippingBook shortcode', 'flippingbook');
				}
			} else {
				if ( WP_DEBUG ) {
					$error_string = $embed_data->get_error_message();
					echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
				}
				return  __('Error embedding FlippingBook shortcode', 'flippingbook');
			}
		} else {
			return __('FlippingBook shortcode is not correct', 'flippingbook');
		}
	}

	/**
	 * Add the top level menu page.
	 */
	function flippingbook_options_page() {
		add_menu_page(
			__( 'FlippingBook Plugin options', 'flippingbook'),
			'FlippingBook',
			'manage_options',
			'flippingbook',
			array( $this, 'flippingbook_options_page_html' ),
			plugin_dir_url( __FILE__ ) . 'flippingbook-icon.png'
		);
	}

	/**
	 * Custom options and settings
	 */
	function flippingbook_settings_init() {
		register_setting( 'flippingbook', 'flippingbook_options' , array (
			'sanitize_callback' => array( $this, 'flippingbook_setting_sanitize' )
        ));

		add_settings_section(
			'flippingbook_section_oembed',
			__( 'FlippingBook oEmbed', 'flippingbook' ), array( $this, 'flippingbook_section_oembed_callback' ),
			'flippingbook'
		);
//		add_settings_section(
//			'flippingbook_section_shortcode',
//			__( 'Shortcode', 'flippingbook' ), array( $this, 'flippingbook_section_shortcode_callback' ),
//			'flippingbook'
//		);
		add_settings_section(
			'flippingbook_section_domain',
			__( 'Custom domain', 'flippingbook' ), array( $this, 'flippingbook_section_domain_callback' ),
			'flippingbook'
		);

		add_settings_field(
			'flippingbook_field_domain',
			__( 'Domain name', 'flippingbook' ),
			array( $this, 'flippingbook_field_domain_cb' ),
			'flippingbook',
			'flippingbook_section_domain',
			array(
				'label_for' => 'custom_domain',
				'class'     => 'flippingbook_row'
			)
		);

		add_settings_field(
			'flippingbook_field_make_oembed_responsive',
			__( 'Make oEmbed responsive', 'flippingbook' ),
			array( $this, 'flippingbook_field_make_oembed_responsive_cb' ),
			'flippingbook',
			'flippingbook_section_oembed',
			array(
				'label_for' => 'responsive_oembed',
				'class'     => 'flippingbook_row'
			)
		);

		add_settings_field(
			'flippingbook_field_default_oembed_ratio',
			__( 'oEmbed ratio', 'flippingbook' ),
			array( $this, 'flippingbook_field_default_oembed_ratio_cb' ),
			'flippingbook',
			'flippingbook_section_oembed',
			array(
				'label_for' => 'oembed_ratio',
				'class'     => 'flippingbook_row'
			)
		);

		add_settings_field(
			'flippingbook_field_clear_oembed_cache',
			__( 'Clear oEmbed cache', 'flippingbook' ),
			array( $this, 'flippingbook_field_clear_oembed_cache_cb' ),
			'flippingbook',
			'flippingbook_section_oembed',
			array(
				'label_for' => 'clear_oembed_cache',
				'class'     => 'flippingbook_row'
			)
		);
	}

	function flippingbook_setting_sanitize( $input = NULL ) {
	    // custom_domain -- Remove protocol and check domain format
		if ( isset( $input['custom_domain'] ) && $input['custom_domain'] !== '') {
			$result = preg_replace('/.*\:\/\/?/', '', $input['custom_domain']);
		    if (!preg_match('/^(?:(?:xn--)?[a-z0-9]+((?:xn--)?-[a-z0-9]+)*\.)+[a-z]{2,}$/', $result))  {
			    $input['custom_domain'] = get_option( 'flippingbook_options' )['custom_domain'];
			    add_settings_error(
				    'flippingbook_messages',
				    'flippingbook_message',
				    __( 'Unrecognized domain name format.', 'flippingbook' ),
				    'error'
			    );
            } else {
			    $input['custom_domain'] = $result;
            }
		}

		// oembed_ratio -- check format
		if ( isset( $input['oembed_ratio'] ) && !empty($input['oembed_ratio'])) {
		    if (!preg_match('/\d+:\d+/', $input['oembed_ratio'])) {
				$input['oembed_ratio'] = get_option( 'flippingbook_options' )['oembed_ratio'];
				add_settings_error(
					'flippingbook_messages',
					'flippingbook_message',
					sprintf(__( 'Incorrect ratio format. Please use numbers separated by colon, like %s', 'flippingbook' ), '<kbd>'.$this->default_oembed_ratio.'</kbd>'),
					'warning'
				);
			}
		}

		// shortcode_cache_timeout -- check type and value
		if ( isset( $input['shortcode_cache_timeout'] ) && !empty($input['shortcode_cache_timeout'])) {
			if (!preg_match('/^\d+$/', $input['shortcode_cache_timeout'])) {
				$input['shortcode_cache_timeout'] = get_option( 'flippingbook_options' )['shortcode_cache_timeout'];
				add_settings_error(
					'flippingbook_messages',
					'flippingbook_message',
					__( 'Incorrect shortcode cache timeout format. Please only use numbers.', 'flippingbook' ),
					'warning'
				);
			} elseif ($input['shortcode_cache_timeout'] < $this->minimal_cache_timeout) {
				$input['shortcode_cache_timeout'] = get_option( 'flippingbook_options' )['shortcode_cache_timeout'];
				add_settings_error(
					'flippingbook_messages',
					'flippingbook_message',
					sprintf(__( 'Minimal shortcode cache timeout value is %s.', 'flippingbook' ), '<kbd>'.$this->minimal_cache_timeout.'</kbd>'),
					'warning'
				);
            }
		}

		if ( isset( $input['clear_oembed_cache'] ) ) {
		    global $wpdb;
		    $count_postmeta = $this->try_clear_flippingbook_oembed_postmeta_cache();
		    if ( $count_postmeta === false ) {
			    add_settings_error(
				    'flippingbook_messages',
				    'flippingbook_message',
				    sprintf(__( 'Could not clear the FlippingBook oEmbed postmeta cache. %s', 'flippingbok' ), $wpdb->last_error),
				    'error'
			    );
            }
			$count_transient = $this->try_clear_flippingbook_oembed_transient_cache();
			if ( $count_transient === false ) {
				add_settings_error(
					'flippingbook_messages',
					'flippingbook_message',
					sprintf(__( 'Could not clear the FlippingBook oEmbed transient cache. %s', 'flippingbok' ), $wpdb->last_error),
					'error'
				);
			}

			if ($count_postmeta + $count_transient === 0) {
				add_settings_error(
					'flippingbook_messages',
					'flippingbook_message',
					sprintf(__( 'FlippingBook oEmbed cache is already empty.', 'flippingbook' ), $count_postmeta + $count_transient),
					'info'
				);
			} else {
			    $total_count = $count_postmeta + $count_transient;
				add_settings_error(
					'flippingbook_messages',
					'flippingbook_message',
					sprintf(_n( 'Successfully removed %d FlippingBook oEmbed cache entry.', 'Successfully removed %d FlippingBook oEmbed cache entries.',  $total_count, 'flippingbook' ), $total_count ),
					'success'
				);
            }
		}

	    return $input;
	}

	function flippingbook_section_domain_callback( $args ) {
		?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e( 'If you have a Custom Domain set up for your FlippingBook Online account, enter it below to enable oEmbed and the [flippingbook] shortcode support.', 'flippingbook' ); ?>
            <br />
            <?php esc_html_e( 'You can get more information about custom domains in our ', 'flippingbook' ); ?>
            <a href="https://flippingbook.com/help/online/other-features-and-options/branded-urls-in-flippingbook-online"><?php esc_html_e( 'Help Center article', 'flippingbook' ); ?></a>.
        </p>
		<?php
	}

	function flippingbook_section_oembed_callback( $args ) {
		?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
           <?php esc_html_e( 'Here you can control the FlippingBook Online and FlippingBook Cloud oEmbed settings for the FlippingBook links.', 'flippingbook' ); ?>
        </p>
		<?php
	}

	function flippingbook_section_shortcode_callback( $args ) {
		?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Here you can control the [flippingbook] shortcode settings.', 'flippingbook' ); ?></p>
        <?php
	}

	function flippingbook_field_domain_cb( $args ) {
		$options = get_option( 'flippingbook_options' );
		?>
        <input
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="flippingbook_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                value="<?php echo !empty($options) && !empty($options[ $args['label_for'] ]) ? ($options[ $args['label_for'] ]) : (''); ?>"
                size="50"
                type="text">
        <p class="description">
			<?php esc_html_e( 'Enter your domain name without a protocol or a path. For example: ', 'flippingbook' ); ?><kbd>catalogs.your-brand.org</kbd>
        </p>
		<?php
	}

	function flippingbook_field_make_oembed_responsive_cb( $args ) {
		$options = get_option( 'flippingbook_options' );
		$checked = !empty($options[ $args['label_for'] ]) ? 1 : 0;
		?>
        <input
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="flippingbook_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                class="responsive-oembed-checkbox"
                type="checkbox"
                value="1"
                <?php checked( 1, $checked, true ) ?>
                />
        <p class="description">
			<?php esc_html_e( 'Check this box to make FlippingBook oEmbeds responsive — they will fill the container by width and retain the ratio specified in the oEmbed ratio field.', 'flippingbook' ); ?>
        </p>
        <p class="description">
            <?php esc_html_e( 'This setting will apply to all new oEmbeds. You’ll need to clear the oEmbed cache if you’d like to apply the setting to your existing FlippingBook oEmbeds.', 'flippingbook' ); ?>
        </p>
		<?php
	}

	function flippingbook_field_default_oembed_ratio_cb( $args ) {
		$options = get_option( 'flippingbook_options' );
		?>
        <input
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="flippingbook_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                value="<?php echo !empty($options) && !empty($options[ $args['label_for'] ]) ? ($options[ $args['label_for'] ]) : $this->default_oembed_ratio; ?>"
                <?php echo empty($options['responsive_oembed']) ? 'disabled' : ''; ?>
                size="10"
                type="text">
        <p class="description">
			<?php printf(esc_html__( 'Enter the width/height ratio for your responsive embed. The default value is %s.', 'flippingbook' ), ('<kbd>'.$this->default_oembed_ratio).'</kbd>'); ?>
        </p>
		<?php
	}

	function flippingbook_field_clear_oembed_cache_cb( $args ) {
		?>
        <input
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="flippingbook_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                class="clear-oembed-cache-checkbox"
                type="checkbox"
                value="1"
        />
        <p class="description">
			<?php esc_html_e( 'Check the box to clear FlippingBook oEmbed cache upon saving.', 'flippingbook' ); ?>
        </p>
		<?php
	}

	function flippingbook_options_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'flippingbook_messages', 'flippingbook_message', __( 'Settings Saved', 'flippingbook' ), 'updated' );
		}
		settings_errors( 'flippingbook_messages' );
		?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
				<?php
				settings_fields( 'flippingbook' );
				do_settings_sections( 'flippingbook' );
				submit_button( 'Save Settings' );
				?>
            </form>
        </div>
        <script type="text/javascript">
            var ratioCheckbox = document.getElementById('responsive_oembed');
            var ratioField = document.getElementById('oembed_ratio');
            if (ratioCheckbox && ratioField) {
                ratioCheckbox.addEventListener('change', function() {
                    if (ratioCheckbox.checked) {
                        ratioField.removeAttribute('disabled');
                    } else  {
                        ratioField.setAttribute('disabled', '');
                    }
                });
            }
        </script>
		<?php
	}

	function try_clear_flippingbook_oembed_postmeta_cache() {
		if (is_user_logged_in() && current_user_can('manage_options')){
			global $wpdb;
			$delres = $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '%_oembed_%' AND (meta_value LIKE '%fbo-embed%' OR meta_value LIKE '%fbc-embed%')");
			return $delres;
		}
		else return false;
	}

	function try_clear_flippingbook_oembed_transient_cache() {
		if (is_user_logged_in() && current_user_can('manage_options')){
			global $wpdb;
			$delres = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_oembed_%' AND (option_value LIKE '%fbo-embed%' OR option_value LIKE '%fbc-embed%')");
			return $delres;
		}
		else return false;
	}
}

$flippingbook = new Flippingbook();

?>