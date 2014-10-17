<?php



/*-----------------------------------------------------------------------------------*/

/* Add default options after activation */

/*-----------------------------------------------------------------------------------*/

//@since 2.0 Mod by denzel, replace the previous functions that does not work..

function propanel_default_settings_install(){



if(is_admin()):

 

	global $pagenow;

	

	// check if we are on theme activation page and activated is true.

	if(@$pagenow == 'themes.php' && @$_GET['activated'] == true):



	//if we are on theme activation page, do the following..

	

		$template = get_option('of_template');



			foreach($template as $t):

				@$option_name = $t['id'];

				@$default_value = $t['std'];

				$value_check = get_option("$option_name");

				if($value_check == ''){

				  update_option("$option_name","$default_value");

				}	

		

			endforeach;

	endif; //end if $pagenow

  

endif; //end if is_admin check



}

add_action('init','propanel_default_settings_install',90);







/*-----------------------------------------------------------------------------------*/

/* Admin Backend */

/*-----------------------------------------------------------------------------------*/

function propanel_siteoptions_admin_head() { ?>



<script type="text/javascript">

jQuery(function(){

var message = '<p><strong>Activation Successful!</strong> This theme\'s settings are located under <a href="<?php echo admin_url('admin.php?page=siteoptions'); ?>">Appearance > Site Options</a>.</p>';

jQuery('.themes-php #message2').html(message);

});

</script>

    

    

    <?php }



add_action('admin_head', 'propanel_siteoptions_admin_head');
add_action( 'init', 'cmb_initialize_cmb_meta_boxes', 9999 );
/**
 * Initialize the metabox class.
 */
function cmb_initialize_cmb_meta_boxes() {

	if ( ! class_exists( 'cmb_Meta_Box' ) )
		require_once 'cmw/init.php';

}
add_filter( 'cmb_meta_boxes', 'cmb_enable_popeditor_metabox' );
function cmb_enable_popeditor_metabox(){
	// Start with an underscore to hide fields from custom fields list
	
	$prefix = '_popeditor_';
	$post_types = get_post_types( '', 'names' ); 
	/**
	 * Sample metabox to demonstrate each field type included
	 */
	$meta_boxes['popeditor_metabox'] = array(
		'id'         => 'popeditor_metabox',
		'title'      => __( 'PopEditor Settings', 'popeditor' ),
		'pages'      => $post_types, // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		// 'cmb_styles' => true, // Enqueue the CMB stylesheet on the frontend
		'fields'     => array(
			array(
				'name' => __( 'Enable PopEditor', 'popeditor' ),
				'desc' => __( '', 'popeditor' ),
				'id'   => $prefix . 'enable_popeditor',
				'type' => 'checkbox',
			),
       
		
		),
	);


	return $meta_boxes;
}
?>