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

	private $happened = 0;

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

		if ( ! lar()->is_enabled() ) {
			return $content;
		}

		global $post;
		if ( $post instanceof WP_Post && get_post_meta( $post->ID, 'lar_disabled', true ) === 'on' ) {
			return $content;
		}
		$post_id = $post instanceof WP_Post ? $post->ID : 0;

		$links = get_posts( array(
			'post_type'      => 'lar_link',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		foreach ($links as $link) {
			$link_meta = get_post_meta($link->ID);
			$meta = function ( $key ) use ( $link_meta ) {
				return isset( $link_meta[ LAR_LITE_PLUGIN_PREFIX . $key ][0] ) ? $link_meta[ LAR_LITE_PLUGIN_PREFIX . $key ][0] : '';
			};

			// Lite links are external links. Other types come from Affiliate Butler Pro.
			$link_type = $meta( 'link_type' );
			if ( $link_type !== 'external' && $link_type !== '' ) {
				continue;
			}

			$slug = $meta( 'slug' );
			if ( $slug !== '' ) {
				$url = get_option( 'permalink_structure' ) != '' ? site_url() . '/go/' . rawurlencode( $slug ) : site_url() . '/index.php?go=' . rawurlencode( $slug );
			} else {
				$url = $meta( 'url' );
			}
			$url = esc_url( $url );
			if ( $url === '' ) {
				continue;
			}

			$keywords = get_post_meta( $link->ID, LAR_LITE_PLUGIN_PREFIX . 'keywords', true );
			if ( ! is_array( $keywords ) ) {
				continue;
			}

			$rel    = $meta( 'do_follow' ) !== 'on' ? ' rel="nofollow"' : '';
			$target = $meta( 'open_in' ) === '_blank' ? '_blank' : '_self';

			/**
			 * Filters extra attributes of the link, as an HTML attributes string.
			 * Before 2.2.5 the filter was given the link ID as its value, so an unfiltered ID is ignored.
			 *
			 * @param string|int $attributes The attributes.
			 * @param int        $post_id    The ID of the post being displayed.
			 */
			$extra_attrs = apply_filters( 'lar_add_extra_atts', $link->ID, $post_id );
			$extra_attrs = ( is_string( $extra_attrs ) && ! preg_match( '/^\d*$/', trim( $extra_attrs ) ) ) ? ' ' . trim( $extra_attrs ) : '';

			$final_url = '<a class="lar-automated-link" href="' . $url . '"' . $extra_attrs . $rel . ' target="' . $target . '">${1}</a>';

			// Not case sensitive unless the link says so.
			$i = $meta( 'is_sensitive' ) === 'on' ? '' : 'i';

			libxml_use_internal_errors(true);
			foreach ($keywords as $keyword) {
				if ( ! is_string( $keyword ) || trim( $keyword ) === '' ) {
					continue;
				}

				$keyword = html_entity_decode( stripslashes( wptexturize( $keyword ) ), ENT_QUOTES, 'UTF-8' );
				$post_content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );

				if ($this->count_occurencies($post_content, preg_quote($keyword, '/'), $i) === 0) {
					continue;
				}

				$doc = new DOMDocument();
				@$doc->loadHTML('<?xml encoding="UTF-8"><body>'.$content.'</body>');
				$doc->encoding = 'UTF-8';

				$per_post_limit = intval(lar()->get_option(LAR_LITE_PLUGIN_PREFIX.'per_post_limit'));
				if ($per_post_limit === 0) { $per_post_limit = -1; }
				$this->happened = 0;
				$this->showDOMNode($doc, preg_quote($keyword, '/'), $final_url, $i, $per_post_limit);

				// Serialize node by node, so UTF-8 text is kept as is and escaped text stays escaped.
				$body = $doc->getElementsByTagName('body')->item(0);
				if ( $body === null ) {
					continue;
				}
				$content = '';
				foreach ($body->childNodes as $child){
					$content .= $doc->saveHTML($child);
				}
			}
			libxml_clear_errors();
		}

		// Replace Content Filter
		$content = apply_filters('lar_replace_content', $content);

		return $content;
	}

	/**
	 * Helper function that is used to get text nodes from the DomNode and replace the keywords
	 * It's used in $this -> lar_auto_replace_links()
	 *
	 * @param DOMNode $domNode        The node.
	 * @param string  $word           The keyword, quoted for a regular expression.
	 * @param string  $replacement    The link markup, `${1}` is the matched keyword.
	 * @param string  $case_sensitive `i` for a case insensitive match.
	 * @param int     $per_post_limit Max links, -1 for no limit.
	 *
	 * @return     DOMNode The replaced Node.
	 * @since    1.5.0
	 */
	public function showDOMNode(DOMNode $domNode,$word,$replacement,$case_sensitive, $per_post_limit = -1) {
		$excluded_html_tags = array_map( 'trim', explode( ',', (string) lar()->get_option( LAR_LITE_PLUGIN_PREFIX . 'excluded_html_tags' ) ) );
		// The nodes change while they are replaced, so iterate over a copy.
		foreach ( iterator_to_array( $domNode->childNodes ) as $node )
		{
			// Pass the nodes which already linked, and code blocks.
			if ( $node->nodeName == 'a' || $node->nodeName == 'pre' ) continue;
			if( in_array($node->nodeName, $excluded_html_tags, true) ) continue;

			if($node->nodeName == '#text'){
				$occurencies = $this->count_occurencies ( $node->nodeValue, $word, $case_sensitive );
				$limit = ($per_post_limit != -1)?$per_post_limit - $this->happened:$per_post_limit;
				if($occurencies > 0 && ($this->happened < $per_post_limit || $per_post_limit === -1)){
					$replacement = $this->clean_replacement($replacement);
					$this->replace_text_node( $node, $this->get_replacement_pattern( $word, $case_sensitive ), $replacement, $limit );
				}
				if($occurencies > 0){
					$this->happened  = $this->happened + $occurencies;
				}
				if($per_post_limit !== -1){
					if($this->happened >= $per_post_limit){
						return $domNode;
					}
				}
			} elseif ( $node->hasChildNodes() ) {
				$this->showDOMNode($node,$word,$replacement,$case_sensitive, $per_post_limit);
			}
		}
		return $domNode;
	}

	/**
	 * Replace the keyword matches in a text node with real DOM nodes.
	 * The text around the matches is escaped, so the post content is never decoded back into HTML.
	 *
	 * @param DOMNode $node        The text node.
	 * @param string  $pattern     The keyword pattern.
	 * @param string  $replacement The link markup, `${1}` is replaced by the matched keyword.
	 * @param int     $limit       Max replacements, -1 for unlimited.
	 *
	 * @since    2.2.5
	 */
	private function replace_text_node( DOMNode $node, $pattern, $replacement, $limit ) {
		$parts = @preg_split( $pattern, $node->nodeValue, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( ! is_array( $parts ) || count( $parts ) < 2 ) {
			return;
		}

		$xml      = '';
		$replaced = 0;
		foreach ( $parts as $index => $part ) {
			$escaped_part = htmlspecialchars( $part, ENT_QUOTES | ENT_XML1, 'UTF-8' );
			// Odd indexes are the captured keyword matches.
			if ( $index % 2 === 1 && ( $limit === -1 || $replaced < $limit ) ) {
				$xml .= str_replace( '${1}', $escaped_part, $replacement );
				$replaced++;
			} else {
				$xml .= $escaped_part;
			}
		}

		$fragment = $node->ownerDocument->createDocumentFragment();
		if ( ! @$fragment->appendXML( $xml ) || is_null( $node->parentNode ) ) {
			return;
		}
		$node->parentNode->replaceChild( $fragment, $node );
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

	/**
	 * @param $replacement
	 *
	 * @return string
	 */
	private function clean_replacement( $replacement ) {
		$concatenated_words_langs = array('ja', 'zh_CN', 'zh_HK', 'zh_TW');
		if(in_array(get_locale(), $concatenated_words_langs)){
			$replacement = trim($replacement);
		}
		return $replacement;
	}

	/**
	 * @param $word
	 * @param $case_sensitive
	 *
	 * @return string
	 */
	private function get_replacement_pattern( $word, $case_sensitive ) {
		$concatenated_words_langs = array('ja', 'zh_CN', 'zh_HK', 'zh_TW');
		if (in_array(get_locale(), $concatenated_words_langs)){
			return '/('.($word).')(?![\w-])/' . $case_sensitive;
		}

		return '/\b('.($word).')(?![\w-])/'.$case_sensitive.'u';
	}

	/**
	 * Calculate the occurrences of a word in a string.
	 *
	 * @param $str
	 * @param $word
	 * @param $case_sensitive
	 *
	 * @since    1.1.0
	 * @return int
	 */
	private function count_occurencies($str, $word, $case_sensitive = '') {
		return @preg_match_all($this->get_replacement_pattern($word, $case_sensitive), $str);
	}

}
