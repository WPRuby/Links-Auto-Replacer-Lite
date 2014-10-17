<?php $custom_button = get_option($shortname."_custom_button"); ?>
<div id="53bd0aa1432aa" class="optin-box optin-box-23" style="margin-right: auto;margin-left: auto;">

	<h2><?php echo get_option($shortname . '_form_headline') ?></h2>
	<form action="<?php echo get_option($shortname . '_form_action') ?>" method="<?php echo get_option($shortname . '_form_method3') ?>" class="cf op-optin-validation">
		<div style="display:none">
			<?php echo urldecode(get_option($shortname . '_form_hidden_fields')); ?>
    			
		</div>
		<input type="text" name="<?php echo get_option($shortname . '_form_name') ?>" placeholder="Enter your first name" value="">
		<input type="text" name="<?php echo get_option($shortname . '_form_email') ?>" placeholder="Enter your email address" value="" spellcheck="false">
		
<?php if($custom_button): ?>
		 			<button type="submit" >
					<img src="<?php echo $custom_button; ?>">
					</button>
		 <?php else: ?>
		 		    <button type="submit" class="default-button"><span><?php echo get_option($shortname . '_form_button_text') ?></span></button>
		
		<?php endif; ?>
			</form>
</div>
