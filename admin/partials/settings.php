<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap cmb2-options-page <?php echo esc_attr( $this->key ); ?>">
	<?php include plugin_dir_path( __FILE__ ) . 'menu.php'; ?>
	<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only reads which tab is open.
		$lar_page   = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : 'lar_settings';
		$metabox_id = ( $lar_page !== 'lar_settings' && isset( $this->tabs[ $lar_page ]['id'] ) ) ? $this->tabs[ $lar_page ]['id'] : 'lar_settings';
		cmb2_metabox_form( $metabox_id, $this->key );
	?>
</div>
