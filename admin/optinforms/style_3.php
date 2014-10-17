<?php $custom_button = get_option($shortname."_custom_button"); ?>
<div id="53bd61eca2431" class="optin-box optin-box-20" style="margin-right: auto;margin-left: auto;">

	<h2><?php echo get_option($shortname . '_form_headline') ?></h2>
	<p class="description"><?php echo get_option($shortname . '_form_content') ?>
</p>
<form action="<?php echo get_option($shortname . '_form_action') ?>" method="<?php echo get_option($shortname . '_form_method3') ?>" class="cf op-optin-validation">
	<div style="display:none">
			<?php echo urldecode(get_option($shortname . '_form_hidden_fields')); ?>
	</div>
	<input type="text" name="<?php echo get_option($shortname . '_form_email') ?>" placeholder="Enter your email address" value="">
	
<?php if($custom_button): ?>
		 			<button type="submit" >
					<img src="<?php echo $custom_button; ?>">
					</button>
		 <?php else: ?>
		 		   <button type="submit" class="default-button"><span><?php echo get_option($shortname . '_form_button_text') ?></span></button>	

		<?php endif; ?>
	
</form>


	<!-- <button type="submit" class="default-link"><?php echo get_option($shortname . '_form_button_text') ?></button>	 -->
	<p class="privacy">We value your privacy and would never spam you</p>
</div>