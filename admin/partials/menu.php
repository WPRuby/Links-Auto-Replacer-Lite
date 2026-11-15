<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 class="nav-tab-wrapper">
	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only reads which tab is open.
	$lar_current_tab = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
	foreach ( $this->tabs as $key => $tab ) :
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $key ) ); ?>" class="nav-tab <?php echo $lar_current_tab === $key ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $tab['title'] ); ?></a>
	<?php endforeach; ?>
</h2>
