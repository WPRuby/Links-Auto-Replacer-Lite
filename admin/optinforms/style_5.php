<?php $custom_button = get_option($shortname."_custom_button");
$button = ($custom_button)?$custom_button:plugins_url( '/images/style_5.png' , __FILE__ );
 ?>

<div style='text-align:center;' class="optin-box" style="margin-right: auto;margin-left: auto;">

	<h2><?php echo get_option($shortname . '_form_headline') ?></h2>
	<p class="description"><?php echo get_option($shortname . '_form_content') ?>
</p>
<form  action="<?php echo get_option($shortname . '_form_action') ?>" method="<?php echo get_option($shortname . '_form_method3') ?>">
	<div style="display:none">
			<?php echo urldecode(get_option($shortname . '_form_hidden_fields')); ?>
	</div>
	
	<button type="submit" >
<img src="<?php echo $button; ?>" />
	</button>
			</form>

</div>