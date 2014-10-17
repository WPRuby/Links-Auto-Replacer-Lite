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



			
	

			



/* Option Page 2 - Sample Page */

$options[] = array( "name" => __('Light Box','popeditor'),

			"type" => "heading");

			






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




	






/////////////////



?>