<?php
/**
 * Links the keywords of a piece of content in one pass.
 *
 * The content is parsed once, and every text node is matched against the keywords that
 * appear in the content at all. When matches overlap, the leftmost one wins, then the
 * longest, then the keyword of the newest link.
 *
 * @since   3.0.0
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Links;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Replacer {

	/**
	 * Locales written without spaces between words, keywords there have no word boundary.
	 *
	 * @var string[]
	 */
	const CONCATENATED_WORDS_LOCALES = array( 'ja', 'zh_CN', 'zh_HK', 'zh_TW' );

	/**
	 * Elements whose text is never linked.
	 *
	 * @var string[]
	 */
	const SKIPPED_ELEMENTS = array( 'a', 'pre', 'script', 'style', 'textarea' );

	/**
	 * The keyword map, see KeywordMap::get_map().
	 *
	 * @var array
	 */
	private $map;

	/**
	 * The ID of the post being displayed, 0 if there is none.
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Max links per keyword, -1 for no limit.
	 *
	 * @var int
	 */
	private $limit;

	/**
	 * Lowercase names of the elements whose text is never linked.
	 *
	 * @var string[]
	 */
	private $skipped_elements;

	/**
	 * The link attributes, by link ID. `false` for a link that must not be linked.
	 *
	 * @var array
	 */
	private $resolved = array();

	/**
	 * @param array $map     The keyword map, see KeywordMap::get_map().
	 * @param int   $post_id The ID of the post being displayed.
	 */
	public function __construct( array $map, $post_id ) {
		$this->map     = $map;
		$this->post_id = (int) $post_id;

		$limit       = intval( lar()->get_option( LAR_LITE_PLUGIN_PREFIX . 'per_post_limit' ) );
		$this->limit = $limit > 0 ? $limit : -1;

		$excluded               = (string) lar()->get_option( LAR_LITE_PLUGIN_PREFIX . 'excluded_html_tags' );
		$excluded               = array_filter( array_map( 'trim', explode( ',', strtolower( $excluded ) ) ) );
		$this->skipped_elements = array_merge( self::SKIPPED_ELEMENTS, $excluded );
	}

	/**
	 * Link the keywords of the content.
	 *
	 * @param string $content The HTML content.
	 *
	 * @return string The content with the keywords linked.
	 */
	public function replace( $content ) {
		$keywords = $this->candidate_keywords( $content );
		if ( empty( $keywords ) ) {
			return $content;
		}

		libxml_use_internal_errors( true );
		$doc = new DOMDocument();
		$doc->loadHTML( '<?xml encoding="UTF-8"><body>' . $content . '</body>' );
		libxml_clear_errors();
		$body = $doc->getElementsByTagName( 'body' )->item( 0 );
		if ( ! $body ) {
			return $content;
		}

		$used_counts = array();
		$linked      = false;
		foreach ( $this->text_nodes( $body ) as $node ) {
			$matches = $this->find_matches( $node->nodeValue, $keywords, $used_counts );
			if ( empty( $matches ) ) {
				continue;
			}
			$this->replace_text_node( $doc, $node, $matches, $keywords );
			$linked = true;
		}

		// Nothing changed: keep the content exactly as it was.
		if ( ! $linked ) {
			return $content;
		}

		$content = '';
		foreach ( $body->childNodes as $child ) {
			$content .= $doc->saveHTML( $child );
		}
		return $content;
	}

	/**
	 * The keywords that can be in the content, so the others aren't matched in every text node.
	 *
	 * @param string $content The HTML content.
	 *
	 * @return array The keywords with their `pattern`, keyed by their priority.
	 */
	private function candidate_keywords( $content ) {
		$text       = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
		$lower_text = self::lowercase( $text );

		$candidates = array();
		foreach ( $this->map['keywords'] as $index => $keyword ) {
			$found = $keyword['case_sensitive']
				? strpos( $text, $keyword['keyword'] ) !== false
				: strpos( $lower_text, self::lowercase( $keyword['keyword'] ) ) !== false;
			if ( ! $found || $this->resolve( $keyword['link'] ) === false ) {
				continue;
			}

			$keyword['pattern']   = self::pattern( $keyword['keyword'], $keyword['case_sensitive'] );
			$candidates[ $index ] = $keyword;
		}

		return $candidates;
	}

	/**
	 * The text nodes that can be linked, in document order.
	 *
	 * @param DOMNode   $root  The root node.
	 * @param DOMText[] $nodes The text nodes found so far.
	 *
	 * @return DOMText[]
	 */
	private function text_nodes( DOMNode $root, array &$nodes = array() ) {
		foreach ( $root->childNodes as $child ) {
			if ( $child instanceof DOMText && ! ( $child instanceof DOMCdataSection ) ) {
				$nodes[] = $child;
			} elseif ( $child instanceof DOMElement && ! in_array( strtolower( $child->nodeName ), $this->skipped_elements, true ) ) {
				$this->text_nodes( $child, $nodes );
			}
		}
		return $nodes;
	}

	/**
	 * Find the keyword matches of a text that will be linked.
	 *
	 * @param string $text        The text.
	 * @param array  $keywords    The candidate keywords.
	 * @param array  $used_counts How many times each keyword was linked so far, updated.
	 *
	 * @return array The matches in text order, each with `start`, `length` and `keyword`.
	 */
	private function find_matches( $text, array $keywords, array &$used_counts ) {
		$found = array();
		foreach ( $keywords as $index => $keyword ) {
			if ( $this->limit !== -1 && isset( $used_counts[ $index ] ) && $used_counts[ $index ] >= $this->limit ) {
				continue;
			}
			if ( ! preg_match_all( $keyword['pattern'], $text, $matches, PREG_OFFSET_CAPTURE ) ) {
				continue;
			}
			foreach ( $matches[1] as $match ) {
				$found[] = array(
					'start'   => $match[1],
					'length'  => strlen( $match[0] ),
					'keyword' => $index,
				);
			}
		}

		usort(
			$found,
			function ( $a, $b ) {
				if ( $a['start'] !== $b['start'] ) {
					return $a['start'] - $b['start'];
				}
				if ( $a['length'] !== $b['length'] ) {
					return $b['length'] - $a['length'];
				}
				return $a['keyword'] - $b['keyword'];
			}
		);

		$selected = array();
		$position = 0;
		foreach ( $found as $match ) {
			$index = $match['keyword'];
			if ( $match['start'] < $position ) {
				continue;
			}
			if ( $this->limit !== -1 && isset( $used_counts[ $index ] ) && $used_counts[ $index ] >= $this->limit ) {
				continue;
			}
			$selected[]            = $match;
			$used_counts[ $index ] = ( isset( $used_counts[ $index ] ) ? $used_counts[ $index ] : 0 ) + 1;
			$position              = $match['start'] + $match['length'];
		}

		return $selected;
	}

	/**
	 * Replace a text node with the text and the links of its matches.
	 *
	 * @param DOMDocument $doc      The document.
	 * @param DOMText     $node     The text node.
	 * @param array       $matches  The matches, see find_matches().
	 * @param array       $keywords The candidate keywords.
	 */
	private function replace_text_node( DOMDocument $doc, DOMText $node, array $matches, array $keywords ) {
		$text     = $node->nodeValue;
		$fragment = $doc->createDocumentFragment();
		$position = 0;

		foreach ( $matches as $match ) {
			if ( $match['start'] > $position ) {
				$fragment->appendChild( $doc->createTextNode( substr( $text, $position, $match['start'] - $position ) ) );
			}

			$a = $doc->createElement( 'a' );
			foreach ( $this->resolve( $keywords[ $match['keyword'] ]['link'] ) as $name => $value ) {
				$a->setAttribute( $name, $value );
			}
			$a->appendChild( $doc->createTextNode( substr( $text, $match['start'], $match['length'] ) ) );
			$fragment->appendChild( $a );

			$position = $match['start'] + $match['length'];
		}

		if ( $position < strlen( $text ) ) {
			$fragment->appendChild( $doc->createTextNode( substr( $text, $position ) ) );
		}

		$node->parentNode->replaceChild( $fragment, $node );
	}

	/**
	 * The attributes of the links of an auto link.
	 *
	 * @param int $link_id The link ID.
	 *
	 * @return array|false The attributes by name, or false if the link must not be used.
	 */
	private function resolve( $link_id ) {
		if ( array_key_exists( $link_id, $this->resolved ) ) {
			return $this->resolved[ $link_id ];
		}

		$meta = isset( $this->map['links'][ $link_id ] ) ? $this->map['links'][ $link_id ] : null;
		$url  = $meta ? self::link_url( $meta ) : '';
		if ( $url === '' ) {
			$this->resolved[ $link_id ] = false;
			return false;
		}

		$attributes = array(
			'class' => 'lar-automated-link',
			'href'  => $url,
		);

		/**
		 * Filters extra attributes of the auto links, as an HTML attributes string.
		 * The filter is given the link ID as its value, an unfiltered ID is ignored.
		 *
		 * @param string|int $attributes The attributes.
		 * @param int        $post_id    The ID of the post being displayed.
		 */
		$extra = apply_filters( 'lar_add_extra_atts', $link_id, $this->post_id );
		if ( is_string( $extra ) && ! preg_match( '/^\d*$/', trim( $extra ) ) ) {
			// The plugin's own attributes win, so the filter can't break the link.
			$attributes = array_merge( self::parse_attributes( $extra ), $attributes );
		}

		$rel = array();
		if ( $meta['do_follow'] !== 'on' ) {
			$rel[] = 'nofollow';
		}
		if ( $meta['sponsored'] === 'on' ) {
			// What Google asks for on paid and affiliate links.
			$rel[] = 'sponsored';
		}
		$target = $meta['open_in'] === '_blank' ? '_blank' : '_self';
		if ( $target === '_blank' ) {
			$rel[] = 'noopener';
		}
		if ( $rel ) {
			$attributes['rel'] = implode( ' ', $rel );
		}
		$attributes['target'] = $target;

		$this->resolved[ $link_id ] = $attributes;
		return $attributes;
	}

	/**
	 * The URL a link points to: its short link when it has a slug, else its URL.
	 *
	 * @param array $meta The link meta.
	 *
	 * @return string The URL, empty when it is not a http(s) URL.
	 */
	public static function link_url( array $meta ) {
		if ( $meta['slug'] !== '' ) {
			return self::short_link( $meta['slug'] );
		}
		return esc_url_raw( $meta['url'], array( 'http', 'https' ) );
	}

	/**
	 * The short link of a slug.
	 *
	 * @param string $slug The slug.
	 *
	 * @return string
	 */
	public static function short_link( $slug ) {
		return get_option( 'permalink_structure' ) != ''
			? site_url( '/go/' . rawurlencode( $slug ) )
			: site_url( '/index.php?go=' . rawurlencode( $slug ) );
	}

	/**
	 * Parse an HTML attributes string, e.g. ` class="a" data-id="1"`.
	 *
	 * @param string $attributes The attributes string.
	 *
	 * @return array The attribute values by name.
	 */
	private static function parse_attributes( $attributes ) {
		libxml_use_internal_errors( true );
		$doc = new DOMDocument();
		$doc->loadHTML( '<?xml encoding="UTF-8"><body><a ' . $attributes . '></a></body>' );
		libxml_clear_errors();

		$parsed = array();
		$a      = $doc->getElementsByTagName( 'a' )->item( 0 );
		if ( $a ) {
			foreach ( $a->attributes as $attribute ) {
				// Event handlers and URLs from a filter are not trusted.
				if ( stripos( $attribute->name, 'on' ) === 0 || in_array( strtolower( $attribute->name ), array( 'href', 'style' ), true ) ) {
					continue;
				}
				$parsed[ $attribute->name ] = $attribute->value;
			}
		}
		return $parsed;
	}

	/**
	 * The regular expression of a keyword. The keyword is the first capture group.
	 *
	 * @param string $keyword        The keyword.
	 * @param bool   $case_sensitive Whether the keyword matches only its exact case.
	 *
	 * @return string
	 */
	public static function pattern( $keyword, $case_sensitive ) {
		$word      = preg_quote( $keyword, '/' );
		$modifiers = ( $case_sensitive ? '' : 'i' ) . 'u';

		if ( in_array( get_locale(), self::CONCATENATED_WORDS_LOCALES, true ) ) {
			return '/(' . $word . ')(?![\w-])/' . $modifiers;
		}
		return '/\b(' . $word . ')(?![\w-])/' . $modifiers;
	}

	/**
	 * Lowercase a UTF-8 string.
	 *
	 * @param string $text The text.
	 *
	 * @return string
	 */
	private static function lowercase( $text ) {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
	}
}
