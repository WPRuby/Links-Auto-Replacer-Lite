<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap cmb2-options-page <?php echo esc_attr( $this->key ); ?>">
	<?php include plugin_dir_path(__FILE__).'menu.php'; ?>

		<div id="lar_about">
			
			
			
			<div id="details">
			<div id="author_details">


		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Author Info', 'links-auto-replacer' ); ?></th>
				</tr>
			</thead>	
			<tr>
				<td>

					<img class="wes" src="<?php echo esc_url( LAR_URL . 'admin/images/wpruby.jpg' ); ?>" alt="WPRuby" />
					<ul>
						<li><strong>WPRuby</strong> </li>
						<li><strong>Website:</strong><a target="_blank" href="https://wpruby.com">wpruby.com</a></li>
						<li><strong>Social: 
											<a target="_blank" href="https://www.facebook.com/WPRuby/"><img class="social_img" src="<?php echo esc_url( LAR_URL . 'admin/images/social/facebook-24.png' ); ?>" alt="Facebook"></a>
											<a target="_blank" href="https://twitter.com/wprubyplugins"><img class="social_img" src="<?php echo esc_url( LAR_URL . 'admin/images/social/twitter-24.png' ); ?>" alt="Twitter"></a>
											<a target="_blank" href="https://instagram.com/wpruby_hq"><img class="social_img" src="<?php echo esc_url( LAR_URL . 'admin/images/social/instagram-24.png' ); ?>" alt="Instagram"></a>

						</strong></li>
					</ul>
				</td>
			</tr>
		</table>



					
					
					
					
					
					
				</div>
				<div id="plugin_details">



					<table class="wp-list-table widefat">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Plugin Info', 'links-auto-replacer' ); ?></th>
							</tr>
						</thead>	
						<tr>
							<td>

								<ul>
									<li><?php echo esc_html( apply_filters( 'lar_plugin_name', 'Keywords to Links Converter' ) ); ?><span></span></li>
									<li><strong><?php esc_html_e( 'Version', 'links-auto-replacer' ); ?>:</strong> <?php echo esc_html( LAR_VERSION ); ?></li>
									<li><a href="https://wpruby.com/plugin/affiliate-butler-pro/">Website</a> | <a href="https://wpruby.com/knowledgebase_category/affiliate-butler-pro/">Documentation</a> | <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=lar_link&page=lar_settings' ) ); ?>"><?php esc_html_e( 'Settings', 'links-auto-replacer' ); ?></a></li>
									
								</ul>
							</td>
						</tr>
					</table>
					
					
					
				</div>

				<div class="lar_clear"></div>
				<br/>
				<div id="lar_support">
					
					<table class="wp-list-table widefat">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Help and Support', 'links-auto-replacer' ); ?></th>
							</tr>
						</thead>	
						<tr>
							<td>
								<p>
								<?php esc_html_e( 'Found a problem, or have a feature in mind for the next release?', 'links-auto-replacer' ); ?> <a target="_blank" rel="noopener" href="https://wpruby.com/submit-ticket/"><?php esc_html_e( 'Submit a support ticket', 'links-auto-replacer' ); ?></a>.
								</p>
							</td>
						</tr>
					</table>

				</div>


			</div>


		</div>

</div> 