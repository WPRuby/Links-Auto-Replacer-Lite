<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpruby.com/plugin/affiliate-butler-pro/
 * @since      2.0.0
 *
 * @package    Links_Auto_Replacer
 * @subpackage Links_Auto_Replacer/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Links_Auto_Replacer
 * @subpackage Links_Auto_Replacer/public
 * @author     Waseem Senjer <waseem.senjer@gmail.com>
 */
class Links_Auto_Replacer_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $Links_Auto_Replacer    The ID of this plugin.
	 */
	private $Links_Auto_Replacer;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $Links_Auto_Replacer       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $Links_Auto_Replacer, $version ) {

		$this->Links_Auto_Replacer = $Links_Auto_Replacer;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Links_Auto_Replacer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Links_Auto_Replacer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
	
		wp_enqueue_script( $this->Links_Auto_Replacer, plugin_dir_url( __FILE__ ) . 'js/lar-public.js', array( 'jquery' ), $this->version, false );

	}

	public function add_inline_styles(){
		echo "<style> .lar_social_share_icon{
 				margin: 2px;
 				}</style>";
	}

	/**
	 * This is the main function that converts the keywords to links.
	 * It's used as (the_content, the_exerpt) filters callback.
	 * @param	 string the original content
	 * @return 	 string	the converted content.
	 * @since    1.0.0
	 */
	public function lar_auto_replace_links( $content ){

		if ( ! lar()->is_enabled() || ! is_string( $content ) || $content === '' ) {
			return $content;
		}

		$post_id = get_the_ID() ? (int) get_the_ID() : 0;
		if ( $post_id && get_post_meta( $post_id, 'lar_disabled', true ) === 'on' ) {
			return $content;
		}

		// The same HTML gives the same result until a link, a setting or a post changes.
		$cache_key = md5( \LinksAutoReplacer\Links\KeywordMap::version() . '|' . $post_id . '|' . get_locale() . '|' . $content );
		$replaced  = wp_cache_get( $cache_key, 'lar_content' );
		if ( ! is_string( $replaced ) ) {
			$replacer = new \LinksAutoReplacer\Links\Replacer( \LinksAutoReplacer\Links\KeywordMap::get_map(), $post_id );
			$replaced = $replacer->replace( $content );
			wp_cache_set( $cache_key, $replaced, 'lar_content', DAY_IN_SECONDS );
		}

		// Replace Content Filter
		return apply_filters( 'lar_replace_content', $replaced );
	}

	/**
	 * This method adds a new rewrite rules for external links with slugs.
	 * @param	 array  The original rules.
	 * @return	 array The new rules.
	 * @since    1.0.0
	 */
	public function lar_setup_rewrite_rules($rules)
	{
		$new_rules = array();
	    $new_rules['^go/([^/]*)/?$'] = 'index.php?go=$matches[1]';
		$new_rules['^index.php?go=([^/]*)?$'] = 'index.php?go=$matches[1]';
	    return $new_rules + $rules;
	}


	/**
	 * This method adds a new query var [go] 
	 * @TODO Should be enhanced by removing the [go] prefix from urls with slugs.
	 *
	 * @param	 array  original vars.
	 * @return	 array The new vars with [go] added.
	 * @since    1.0.0
	 */
	public function add_go_variable($vars)
	{
	    array_push($vars, 'go');
	    return $vars;
	}


	/**
	* This method is responsible for redirecting slugged links to its original urls.
	* Also, it, it will save a new stats record.
	*
	* @since    1.0.0
	*/
	public function lar_redirect(){
		global $wp_query;
		
		if ( ! isset( $wp_query->query_vars['go'] ) || ! is_string( $wp_query->query_vars['go'] ) || $wp_query->query_vars['go'] === '' ) {
			return;
		}

		$link = get_posts( array(
			'post_type'      => 'lar_link',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => LAR_LITE_PLUGIN_PREFIX . 'slug', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => sanitize_text_field( $wp_query->query_vars['go'] ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		) );
		if ( empty( $link ) ) {
			return;
		}

		$link_url = esc_url_raw( (string) get_post_meta( $link[0]->ID, LAR_LITE_PLUGIN_PREFIX . 'url', true ), array( 'http', 'https' ) );
		if ( $link_url === '' ) {
			return;
		}

		do_action( 'lar_link_redirected', $link[0]->ID );
		// Every visit reaches the site, and the short links stay out of search results.
		nocache_headers();
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow' );
		}
		wp_redirect( $link_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- short links redirect to external URLs by design.
		exit;
		
	}

}
