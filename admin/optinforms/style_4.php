<?php $custom_button = get_option($shortname."_custom_button"); ?>
<div id="53bd72d12f56b" class="optin-box optin-box-4" style="margin-right: auto;margin-left: auto;">

	<h2><?php echo get_option($shortname . '_form_headline') ?></h2>
		<div class="optin-box-content">

		<p class="description">
			<?php echo get_option($shortname . '_form_content') ?>
</p>

<form action="<?php echo get_option($shortname . '_form_action') ?>" method="<?php echo get_option($shortname . '_form_method3') ?>" class="cf op-optin-validation">
	<div style="display:none">
			<?php echo urldecode(get_option($shortname . '_form_hidden_fields')); ?>
	</div>

	<div class="text-box email">
		<input type="text" name="<?php echo get_option($shortname . '_form_email') ?>" placeholder="Enter your email address" value="">
	</div>


<?php if($custom_button): ?>
		 			<button type="submit" >
					<img src="<?php echo $custom_button; ?>">
					</button>
		 <?php else: ?>
		 		<button type="submit" class="default-button"><span><?php echo get_option($shortname . '_form_button_text') ?></span></button>	
	
		<?php endif; ?>

		
	

		</form>
		<p class="privacy">
			<img src="<?php echo plugin_dir_url(__FILE__).'images/privacy.png'; ?>" alt="privacy" width="16" height="15"> We value your privacy and would never spam you</p>	
		</div>
</div>