<?php
/**
 * WPRuby branding: a header on the plugin's screens and links on the Plugins screen.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Admin;

final class Branding {

	const SUPPORT_URL = 'https://wordpress.org/support/plugin/links-auto-replacer/';

	const DOCS_URL = 'https://wpruby.com/knowledgebase_category/affiliate-butler-pro/';

	const PLUGINS_URL = 'https://wpruby.com/plugins/';

	/**
	 * The plugin's main file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * @param string $file The plugin's main file.
	 */
	public function __construct( string $file ) {
		$this->file = $file;
	}

	/**
	 * Register the hooks.
	 */
	public function register_hooks(): void {
		add_action( 'in_admin_header', array( $this, 'render_header' ) );
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );
	}

	/**
	 * The WPRuby ruby, from wpruby.com (238.5 × 169.5).
	 *
	 * @return string
	 */
	public function logo_url(): string {
		return plugins_url( 'assets/images/wpruby-logo.svg', $this->file );
	}

	/**
	 * Whether the current screen is one of the plugin's pages.
	 *
	 * @return bool
	 */
	public static function is_plugin_screen(): bool {
		global $plugin_page;
		return is_string( $plugin_page ) && isset( AdminPage::PAGES[ $plugin_page ] );
	}

	/**
	 * The header, above the screen's notices. Its style is in the admin app's stylesheet.
	 */
	public function render_header(): void {
		if ( ! self::is_plugin_screen() ) {
			return;
		}
		?>
		<div class="lar-brand-header">
			<a class="lar-brand-header__logo" href="https://wpruby.com" target="_blank" rel="noopener noreferrer">
				<img src="<?php echo esc_url( $this->logo_url() ); ?>" alt="WPRuby" width="45" height="32" />
			</a>
			<div class="lar-brand-header__title">
				<p class="lar-brand-header__name"><?php esc_html_e( 'Keywords to Links Converter', 'links-auto-replacer' ); ?></p>
				<p class="lar-brand-header__vendor">
					<?php
					printf(
						/* translators: 1: WPRuby, linked to wpruby.com, 2: plugin version. */
						esc_html__( 'by %1$s · v%2$s', 'links-auto-replacer' ),
						'<a href="https://wpruby.com" target="_blank" rel="noopener noreferrer">WPRuby<span class="screen-reader-text"> ' . esc_html__( '(opens in a new tab)', 'links-auto-replacer' ) . '</span></a>',
						esc_html( LAR_VERSION )
					);
					?>
				</p>
			</div>
			<nav class="lar-brand-header__links" aria-label="<?php esc_attr_e( 'Plugin help', 'links-auto-replacer' ); ?>">
				<?php $this->render_link( self::DOCS_URL, 'dashicons-book', __( 'Documentation', 'links-auto-replacer' ) ); ?>
				<?php $this->render_link( self::SUPPORT_URL, 'dashicons-sos', __( 'Support', 'links-auto-replacer' ) ); ?>
			</nav>
		</div>
		<?php
	}

	/**
	 * Documentation and Support links after "By WPRuby | Visit plugin site".
	 *
	 * @param string[] $meta The links.
	 * @param string   $file The plugin of the row.
	 *
	 * @return string[]
	 */
	public function row_meta( $meta, $file ): array {
		$meta = is_array( $meta ) ? $meta : array();
		if ( plugin_basename( $this->file ) !== $file ) {
			return $meta;
		}

		$meta['docs']    = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( self::DOCS_URL ), esc_html__( 'Documentation', 'links-auto-replacer' ) );
		$meta['support'] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( self::SUPPORT_URL ), esc_html__( 'Support', 'links-auto-replacer' ) );

		return $meta;
	}

	/**
	 * A link that opens in a new tab, with an icon.
	 *
	 * @param string $url   The URL.
	 * @param string $icon  The dashicon.
	 * @param string $label The text.
	 */
	private function render_link( string $url, string $icon, string $label ): void {
		printf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer"><span class="dashicons %2$s" aria-hidden="true"></span>%3$s<span class="screen-reader-text"> %4$s</span></a>',
			esc_url( $url ),
			esc_attr( $icon ),
			esc_html( $label ),
			esc_html__( '(opens in a new tab)', 'links-auto-replacer' )
		);
	}
}
