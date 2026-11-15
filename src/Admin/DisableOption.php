<?php
/**
 * The "Disable auto links" option of the posts: a panel in the block editor
 * (assets/src/editor) and a meta box in the classic editor.
 *
 * It's saved in the `lar_disabled` meta, `on` or `off`, as since 1.5.0.
 *
 * @package Links_Auto_Replacer
 */

namespace LinksAutoReplacer\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DisableOption {

	const META = 'lar_disabled';

	const NONCE = 'lar_disable_nonce';

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
		add_action( 'init', array( $this, 'register_meta' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_panel' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	/**
	 * The post types that have the option.
	 *
	 * @return string[]
	 */
	public static function post_types(): array {
		return (array) apply_filters( 'lar_disable_box_screens', array( 'post', 'page' ) );
	}

	/**
	 * Register the meta, so the block editor can save it.
	 */
	public function register_meta(): void {
		foreach ( self::post_types() as $post_type ) {
			// The REST API only has the meta of the post types that support custom fields.
			if ( ! post_type_supports( $post_type, 'custom-fields' ) ) {
				add_post_type_support( $post_type, 'custom-fields' );
			}
			register_post_meta(
				$post_type,
				self::META,
				array(
					'type'              => 'string',
					'single'            => true,
					'default'           => 'off',
					'show_in_rest'      => array(
						'schema' => array(
							'type' => 'string',
							'enum' => array( 'on', 'off' ),
						),
					),
					'sanitize_callback' => function ( $value ) {
						return $value === 'on' ? 'on' : 'off';
					},
					'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
	}

	/**
	 * Load the block editor panel.
	 */
	public function enqueue_editor_panel(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->post_type, self::post_types(), true ) ) {
			return;
		}

		$asset_file = plugin_dir_path( $this->file ) . 'build/editor.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;

		wp_enqueue_script( 'lar-editor', plugins_url( 'build/editor.js', $this->file ), $asset['dependencies'], $asset['version'], true );
		wp_set_script_translations( 'lar-editor', 'links-auto-replacer' );
	}

	/**
	 * Add the meta box of the classic editor.
	 */
	public function add_meta_box(): void {
		foreach ( self::post_types() as $post_type ) {
			add_meta_box(
				'lar_meta',
				__( 'Keywords to Links Converter', 'links-auto-replacer' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'default',
				// The block editor has its own panel.
				array( '__back_compat_meta_box' => true )
			);
		}
	}

	/**
	 * Render the meta box.
	 *
	 * @param \WP_Post $post The post.
	 */
	public function render_meta_box( $post ): void {
		wp_nonce_field( self::NONCE, self::NONCE );
		?>
		<p>
			<input type="checkbox" name="<?php echo esc_attr( self::META ); ?>" id="<?php echo esc_attr( self::META ); ?>" <?php checked( get_post_meta( $post->ID, self::META, true ), 'on' ); ?> />
			<label for="<?php echo esc_attr( self::META ); ?>"><?php esc_html_e( 'Disable auto links in this post', 'links-auto-replacer' ); ?></label>
		</p>
		<?php
	}

	/**
	 * Save the meta box.
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_meta_box( $post_id ): void {
		if (
			! isset( $_POST[ self::NONCE ] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ||
			wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ||
			! in_array( get_post_type( $post_id ), self::post_types(), true ) ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		update_post_meta( $post_id, self::META, isset( $_POST[ self::META ] ) ? 'on' : 'off' );
	}
}
