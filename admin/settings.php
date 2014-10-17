<?php



add_action('init','propanel_of_options');

$shortname = "popeditor";

if (!function_exists('propanel_of_options')) {

function propanel_of_options(){



//Theme Shortname

global $shortname;





//Populate the options array

global $tt_options;

$tt_options = get_option('of_options');



$form_style_id = get_option($shortname.'_form_style');

$form_style_img = ($form_style_id)?'<img src="'. plugins_url( '/optinforms/images/style_'.$form_style_id.'.png' , __FILE__ ) .'" />':'';



/*

//Access the WordPress Pages via an Array

$tt_pages = array();

$tt_pages_obj = get_pages('sort_column=post_parent,menu_order');    

foreach ($tt_pages_obj as $tt_page) {

$tt_pages[$tt_page->ID] = $tt_page->post_name; }

$tt_pages_tmp = array_unshift($tt_pages, "Select a page:"); 





//Access the WordPress Categories via an Array

$tt_categories = array();  

$tt_categories_obj = get_categories('hide_empty=0');

foreach ($tt_categories_obj as $tt_cat) {

$tt_categories[$tt_cat->cat_ID] = $tt_cat->cat_name;}

$categories_tmp = array_unshift($tt_categories, "Select a category:");





//Sample Array for demo purposes

$sample_array = array("1","2","3","4","5");





//Sample Advanced Array - The actual value differs from what the user sees

$sample_advanced_array = array("image" => "The Image","post" => "The Post"); 





//Folder Paths for "type" => "images"

$sampleurl = plugins_url( '/images/sample-layouts/', __FILE__ );



*/



















/*-----------------------------------------------------------------------------------*/

/* Create The Custom Site Options Panel

/*-----------------------------------------------------------------------------------*/

$options = array(); // do not delete this line - sky will fall









/* Option Page 1 - All Options */	

$options[] = array( "name" => __('Alert','popeditor'),

			"type" => "heading",

			"desc" => __('Upload a custom logo for your Website.','popeditor'));

	





$options[] = array( "name" => __('Enable Popup at HomePage','popeditor'),

			"desc" => '',

			"id" => $shortname."_enable_popup",

			"std" => "false",

			"type" => "checkbox");



$options[] = array( "name" => __('The Alert','popeditor'),

			"desc" => __('Explication texte here about alerte Explication texte here about the alerte Explication texte here about the alerte ','popeditor'),

			"id" => $shortname."_alert_text",

			"std" => "",

			"editor" => TRUE,

			"type" => "textarea");









/*$options[] = array( "name" => __('Attention','popeditor'),

			"desc" => "",

			"id" => $shortname."_sample_callout",

			"std" => "This is a callout box. This can be used to inform your clients about something important.",

			"type" => "info");

			

			



			



$options[] = array( "name" => __('Textarea','popeditor'),

			"desc" => "This is a textarea.",

			"id" => $shortname."_sample_text_area",

			"std" => "",

			"type" => "textarea");

			



$options[] = array( "name" => __('Image Upload','popeditor'),

			"desc" => __('This is an image upload field.','popeditor'),

			"id" => $shortname."_sample_image_upload",

			"std" => "",

			"type" => "upload");

					

			

$options[] = array( "name" => __('Checkbox','popeditor'),

			"desc" => __('This is a checkbox.','popeditor'),

			"id" => $shortname."_sample_checkbox",

			"std" => "true",

			"type" => "checkbox");

			

			

$options[] = array( "name" => __('Dropdown List','popeditor'),

			"desc" => __('This is a dropdown list.','popeditor'),

			"id" => $shortname."_sample_dropdown",

			"std" => "1",

			"type" => "select",

			"options" => $sample_array);

			

			

$options[] = array( "name" => __('Radio Buttons','popeditor'),

			"desc" => __('These are radio buttons.','popeditor'),

			"id" => $shortname."_sample_radio",

			"std" => "1",

			"type" => "radio",

			"options" => array(

				'Red Radio' => 'Red',

				'Green Radio' => 'Green',

				'Blue Radio' => 'Blue'

				));

		

			

$options[] = array( "name" => __('Image Radio Buttons','popeditor'),

			"desc" => __('Spice up your radio buttons by using custom images.','popeditor'),

			"id" => $shortname."_sample_image_radio",

			"std" => "option1",

			"type" => "images",

			"options" => array(

				'option1' => $sampleurl . 'sample-layout-1.png',

				'option2' => $sampleurl . 'sample-layout-2.png',

				'option3' => $sampleurl . 'sample-layout-3.png'

				));

						

				

$options[] = array( "name" => __('Color Picker','popeditor'),

			"desc" => __('This is a color picker.','popeditor'),

			"id" => $shortname."_sample_color_picker",

			"std" => "",

			"type" => "color");	

					



$options[] = array( "name" => __('Wordpress Page','popeditor'),

			"desc" => __('This displays a list of every page on your website.','popeditor'),

			"id" => $shortname."_sample_wp_pages",

			"std" => "1",

			"type" => "select",

			"options" => $tt_pages);

			

			

$options[] = array( "name" => __('Wordpress Category','popeditor'),

			"desc" => __('This displays a list of every category on your website.','popeditor'),

			"id" => $shortname."_sample_wp_category",

			"std" => "1",

			"type" => "select",

			"options" => $tt_categories);

			

			

					

					

		*/			

					

			

			



/* Option Page 2 - Sample Page */

$options[] = array( "name" => __('Light Box','popeditor'),

			"type" => "heading");

			



$options[] = array( "name" => __('Image','popeditor'),

			"desc" => __('Upload a custom logo for your Website.','popeditor'),

			"id" => $shortname."_lightbox_image",

			"std" => "",

			"type" => "upload");



$options[] = array( "name" => __('Youtube Link','popeditor'),

			"desc" => "e.g https://www.youtube.com/watch?v=mzMUTrTYD9s",

			"id" => $shortname."_youtube_link",

			"std" => "",

			"type" => "text");	



$options[] = array( "name" => __('Which to Display?','popeditor'),

			"desc" => '',

			"id" => $shortname."_which_to_display",

			"std" => "Image",

			"type" => "radio",

			"options" => array(

				'image' => 'Image',

				'youtube' => 'Youtube',

				

				));					

			

$options[] = array( "name" => __('Text (Below The Image)','popeditor'),

			"desc" => __('Text (Below The Image)','popeditor'),

			"id" => $shortname."_text_below_image",

			"std" => "",

			"editor" => TRUE,

			"type" => "textarea");





$options[] = array( "name" => __('Vertical position of the box','popeditor'),

			"desc" => "pixels",

			"id" => $shortname."_vertical_position",

			"std" => "",

			"type" => "text");



$options[] = array( "name" => __('Width','popeditor'),

			"desc" => "Lightbox width by pixels",

			"id" => $shortname."_lightbox_width",

			"std" => "700",

			"type" => "text");

			

									   





/* Option Page 2 - Sample Page */

$options[] = array( "name" => __('The Form','popeditor'),

			"type" => "heading");				







$options[] = array( "name" => __('Choose Style','popeditor'),

			"desc" => "",

			"id" => $shortname."_form_style_chooser",

			"std" => '<a class="various" href="#choose_style">

<div class="upload_button_div"><span class="button">'.__('Choose Form Style','popeditor').'</span></div>

			</a>

						<div style="display:none;" id="choose_style">

							<ul>

								<li><a href="#" onclick="set_style(1,1);"><img src="'. plugins_url( '/optinforms/images/style_1.png' , __FILE__ ) .'" /></a></li>

								<li><a href="#" onclick="set_style(2,1);"><img src="'. plugins_url( '/optinforms/images/style_2.png' , __FILE__ ) .'" /></a></li>

								<li><a href="#" onclick="set_style(3,2);"><img src="'. plugins_url( '/optinforms/images/style_3.png' , __FILE__ ) .'" /></a></li>

								<li><a href="#" onclick="set_style(4,2);"><img src="'. plugins_url( '/optinforms/images/style_4.png' , __FILE__ ) .'" /></a></li>

								<li><a href="#" onclick="set_style(5,3);"><img src="'. plugins_url( '/optinforms/images/style_5.png' , __FILE__ ) .'" /></a></li>

							</ul>

						</div>

						<div id="form_style_preview">'.$form_style_img.'</div>

			',

			"type" => "fancybox");



$options[] = array( "name" => __('Upload Custom Button','popeditor'),

			"desc" => __('Upload Custom Button','popeditor'),

			"id" => $shortname."_custom_button",

			"std" => "",

			"type" => "upload");



$options[] = array( "name" => __('Width','popeditor'),

			"desc" => "Lightbox width by pixels",

			"id" => $shortname."_form_style",

			"std" => "0",

			"type" => "hidden");





$options[] = array( "name" => __('Hidden Fields','popeditor'),

			"desc" => "Hidden Fields",

			"id" => $shortname."_form_hidden_fields",

			"std" => "0",

			"type" => "hidden");

										

					

$options[] = array( "name" => __('Form Code','popeditor'),

			"desc" => __('Paste the Code from your autoresponder ','popeditor'),

			"id" => $shortname."_form_code",

			"std" => "",

			"type" => "textarea");



$options[] = array( "name" => __('Action','popeditor'),

			"desc" => "Where to post the form",

			"id" => $shortname."_form_action",

			"std" => "",

			"type" => "text");









$options[] = array( "name" => __('Method','popeditor'),

			"desc" => "",

			"id" => $shortname."_form_method3",

			"std" => "post",

			"type" => "select",

			"options" => array(

				'POST' => 'POST',

				'GET' => 'GET'

				

				));







$options[] = array( "name" => __('Name','popeditor'),

			"desc" => "",

			"id" => $shortname."_form_name",

			"std" => "",

			"type" => "select",

			"options" => array(

				

				

				));









$options[] = array( "name" => __('Email','popeditor'),

			"desc" => "",

			"id" => $shortname."_form_email",

			"std" => "",

			"type" => "select",

			"options" => array(

			

				));



$options[] = array( "name" => __('Headline','popeditor'),

			"desc" => "Form Headline",

			"id" => $shortname."_form_headline",

			"std" => "",

			"type" => "text");



$options[] = array( "name" => __('Content','popeditor'),

			"desc" => "Form Content",

			"id" => $shortname."_form_content",

			"std" => "",

			"type" => "textarea");





$options[] = array( "name" => __('Button Text','popeditor'),

			"desc" => "Form Button Text",

			"id" => $shortname."_form_button_text",

			"std" => "Subscribe",

			"type" => "text");







$options[] = array( "name" => __('Email','popeditor'),

			"desc" => "",

			"id" => $shortname."_form_emails2",

			"std" => "post1",

			"type" => "select",

			"options" => array(

			

				));









update_option('of_template',$options); 					  

update_option('of_shortname',$shortname);



}

}



////////SETUP COOKIES///////



add_action('init','popeditor_setup_cookies');

function popeditor_setup_cookies(){

	  if(!isset($_COOKIE['popup_counter'])){

   			setcookie("popup_counter", 1, (time()+3600)*24, "/");

   		}else{

   			setcookie("popup_counter", $_COOKIE['popup_counter']+1 , (time()+3600)*24, "/");

   	  }

}


add_action('wp','test_test');
function test_test(){
	//global $post; print_r($post);
 global $shortname;
///////////////
$show_it = false;
if(is_home() || is_front_page()){  
	if(get_option($shortname . '_enable_popup') === 'true'){ 
			$show_it = true;
	}
}else{

	global $post; 
	
	 
	if(get_post_meta($post->ID,'_popeditor_enable_popeditor',true)=='on'){
		$show_it = true;
	}

}



 

   if($show_it === true AND  $_COOKIE['is_subscribed'] != 1 AND $_COOKIE['popup_counter']<2){

   		add_action('wp_footer', 'detect_user_leaving_code');

  }
}


	






function detect_user_leaving_code() {

	global $shortname;



    wp_enqueue_script(

		'fancybox',

		plugin_dir_url(__FILE__).'js/fancybox/jquery.fancybox.pack.js',

		array( 'jquery' )

	);

	 wp_enqueue_script(

		'refresh_detector',

		plugin_dir_url(__FILE__).'js/refresh_detector.js',

		array( 'jquery' )

	);



	  wp_enqueue_script(

		'jquery_cookies',

		plugin_dir_url(__FILE__).'js/jquery-cookie.js',

		array( 'jquery' )

	);



	wp_enqueue_style(

		'fancybox-css',

		plugin_dir_url(__FILE__).'js/fancybox/jquery.fancybox.css');

	wp_enqueue_style(

		'optin-styles',

		plugin_dir_url(__FILE__).'optinforms/optin.css');







	$alert_text = get_option($shortname.'_alert_text');

    $lightbox_image = get_option($shortname.'_lightbox_image');

    $lightbox_image_text = get_option($shortname.'_text_below_image');

    $form_code = get_option($shortname.'_form_code');

    $vertical_position = get_option($shortname.'_vertical_position');

    $which_to_display = get_option($shortname."_which_to_display");

    $youtube_link = get_option($shortname."_youtube_link");

    $form_style_id = get_option($shortname.'_form_style');

 









	 ?>

	

		<a style='display:none;'   class="various" href="#inline">Inline</a>

	

	<div style='display:none'>

	<div style='display:none'  id='inline'>

		

			<?php if($which_to_display =='image'): ?>

			<?php if($lightbox_image): ?>

				<img style="width:99%;" src="<?php echo $lightbox_image; ?>">

			<?php endif; ?>

		<?php elseif($which_to_display =='youtube'): ?>

				<?php if($youtube_link): 



				parse_str( parse_url( $youtube_link, PHP_URL_QUERY ), $vars );

				

				?>

				<iframe  style='width:100%;' height="315" src="//www.youtube.com/embed/<?php echo $vars['v'] ?>" frameborder="0" allowfullscreen></iframe>

			<?php endif; ?>



			<?php endif; ?>

			<?php if($lightbox_image_text): ?>

				<p style='text-align:center;'><?php echo $lightbox_image_text; ?></p>

			<?php endif; ?>

			<?php if($form_code): ?>

				<?php //echo $form_code;

					include 'optinforms/style_'.$form_style_id.'.php';

				 ?>

			<?php endif; ?>

	</div>

	</div>

    <script language="JavaScript">

    /*function hereDoc(f) {

  return f.toString().

      replace(/^[^\/]+\/\*!?/, '').

      replace(/\*\/[^\/]+$/, '');

}*/

 // window.onbeforeunload = confirmExit;

 var is_refreshed = false;

  jQuery(window).on('beforeunload', function(event){

  	if(is_refreshed === false){

		  	if(!refreshKeyPressed){

		  			jQuery('.various').trigger('click');

		  			 is_refreshed = true;

		  			return <?php echo json_encode(strip_tags(   str_replace('</p>', "\n\n", $alert_text)));  ?>;

		  	}



  	}

  		

  });

  







  jQuery(document).ready(function() {



  	 jQuery('#inline button').click(function(){



				 		jQuery.cookie("is_subscribed", 1);

				 });

	jQuery(".various").fancybox({

		/*maxWidth	: 800,

		maxHeight	: 600,*/

		fitToView	: false,

		width		: '<?php echo get_option($shortname."_lightbox_width"); ?>px',

		height		: 'auto',

		autoSize	: false,

		closeClick	: false,

		openEffect	: 'none',

		closeEffect	: 'none',

		<?php if($vertical_position): ?>

		 margin: [<?php echo $vertical_position; ?>, 0, 0, 0],

		<?php endif; ?>

		onComplete: function() {

				 jQuery(document).scrollTop(0);

				

     			 

   		} 

   		

	});

});



</script>

<?php

}



/////////////////



?>