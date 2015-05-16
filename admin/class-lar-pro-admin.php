<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://waseem-senjer.com/product/links-auto-replacer-pro/
 * @since      2.0.0
 *
 * @package    Links_Auto_Replacer_Pro
 * @subpackage Links_Auto_Replacer_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Links_Auto_Replacer_Pro
 * @subpackage Links_Auto_Replacer_Pro/admin
 * @author     Your Name <email@example.com>
 */
class Links_Auto_Replacer_Pro_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $Links_Auto_Replacer_Pro    The ID of this plugin.
	 */
	private $Links_Auto_Replacer_Pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $last_link_id;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $Links_Auto_Replacer_Pro       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $Links_Auto_Replacer_Pro, $version ) {

		$this->Links_Auto_Replacer_Pro = $Links_Auto_Replacer_Pro;
		$this->version = $version;
		$this->last_link_id = (function_exists('gmp_strval'))?base62encode(get_option('last_lar_link_id') + 100):base62::encode(get_option('last_lar_link_id') + 100); 
		

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Links_Auto_Replacer_Pro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Links_Auto_Replacer_Pro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->Links_Auto_Replacer_Pro, plugin_dir_url( __FILE__ ) . 'css/lar-links-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->Links_Auto_Replacer_Pro.'-jqvmap', plugin_dir_url( __FILE__ ) . 'js/jqvmap/jqvmap.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->Links_Auto_Replacer_Pro.'-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css', array(), $this->version, 'all' );


	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Links_Auto_Replacer_Pro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Links_Auto_Replacer_Pro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->Links_Auto_Replacer_Pro, plugin_dir_url( __FILE__ ) . 'js/lar-links-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * The plugin use this method to register the main custom post type of the links.
	 * 
	 * @since    2.0.0
	 */
	public function register_links_post_type(){
		$labels = array(
			'name'               => _x( 'Links', 'post type general name', 'links-auto-replacer-pro' ),
			'singular_name'      => _x( 'Link', 'post type singular name', 'links-auto-replacer-pro' ),
			'menu_name'          => _x( 'Links', 'admin menu', 'links-auto-replacer-pro' ),
			'name_admin_bar'     => _x( 'Link', 'add new on admin bar', 'links-auto-replacer-pro' ),
			'add_new'            => _x( 'Add New Link', 'book', 'links-auto-replacer-pro' ),
			'add_new_item'       => __( 'Add New Link', 'links-auto-replacer-pro' ),
			'new_item'           => __( 'New Link', 'links-auto-replacer-pro' ),
			'edit_item'          => __( 'Edit Link', 'links-auto-replacer-pro' ),
			'view_item'          => __( 'View Link', 'links-auto-replacer-pro' ),
			'all_items'          => __( 'All Links', 'links-auto-replacer-pro' ),
			'search_items'       => __( 'Search Links', 'links-auto-replacer-pro' ),
			'parent_item_colon'  => __( 'Parent Links:', 'links-auto-replacer-pro' ),
			'not_found'          => __( 'No links found.', 'links-auto-replacer-pro' ),
			'not_found_in_trash' => __( 'No links found in Trash.', 'links-auto-replacer-pro' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'lar_link' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'author' )
		);

		register_post_type( 'lar_link', $args );
	}


	/**
	 * The plugin use this method to register the custom taxonomies of the links.
	 * @since    2.0.0
	 */
	public function register_taxonomies(){
		$labels = array(
			'name'                       => _x( 'Link Categories', 'taxonomy general name' ),
			'singular_name'              => _x( 'Link Category', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Link Categories' ),
			'popular_items'              => __( 'Popular Link Categories' ),
			'all_items'                  => __( 'All Link Categories' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Link Category' ),
			'update_item'                => __( 'Update Link Category' ),
			'add_new_item'               => __( 'Add New Link Category' ),
			'new_item_name'              => __( 'New Link Category Name' ),
			'separate_items_with_commas' => __( 'Separate link categories with commas' ),
			'add_or_remove_items'        => __( 'Add or remove link category' ),
			'choose_from_most_used'      => __( 'Choose from the most used links categories' ),
			'not_found'                  => __( 'No links categories found.' ),
			'menu_name'                  => __( 'Link Categories' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'models' ),
		);

		register_taxonomy( 'links_category', 'lar_link', $args );
	}


	/**
	 *	Using CMB2 library The method will build a custom meta box for the user to fill the link information.
	 * 
	 *  @since    2.0.0
	 */
	public function lar_links_register_metabox() {
		

		$add_links_box = new_cmb2_box( array(
			'id'            => PLUGIN_PREFIX . 'metabox',
			'title'         => __( 'Add new Link', 'links-auto-replacer-pro' ),
			'object_types'  => array( 'lar_link', ), // Post type
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
			// 'cmb_styles' => false, // false to disable the CMB stylesheet
			// 'closed'     => true, // true to keep the metabox closed by default
		) );


		$add_links_box->add_field( array(
			'name' => __( 'Keyword/s', 'links-auto-replacer-pro' ),
			
			'id'   => PLUGIN_PREFIX . 'keywords',
			'type' => 'text_medium',
			'repeatable' => true,
		) );

		///////////////

		$add_links_box->add_field( array(
			'name' => __( 'Link Type', 'links-auto-replacer-pro' ),
			'id'   => PLUGIN_PREFIX . 'link_type',
			'type' => 'select',
			
			'options' => array(
					  'external' => __('External','links-auto-replacer-pro'),
					  'internal' => __('Internal','links-auto-replacer-pro'),
				),
			'description' => __('','links-auto-replacer-pro'),
		) );


		$add_links_box->add_field( array(
			'name' => __( 'URL (Link)', 'links-auto-replacer-pro' ),
			'default' => 'http://',
			'id'   => PLUGIN_PREFIX . 'url',
			'type' => 'text_url',
		) );

		$add_links_box->add_field( array(
			'name' => __( 'Internal URL (Link)', 'links-auto-replacer-pro' ),
			
			'id'   => PLUGIN_PREFIX . 'internal_url',
			'type' => 'select',
			'options' => array('0' => __('Select Post/Page','links-auto-replacer-pro' ))
		) );
		////////////////
		

		$add_links_box->add_field( array(
			'name' => __( 'Dofollow?', 'links-auto-replacer-pro' ),
			'id'   => PLUGIN_PREFIX . 'do_follow',
			'type' => 'checkbox',
			'description' => __('if you checked this option, you will allow search engines to follow this link and use it in ranking.','links-auto-replacer-pro'),
		) );

		$add_links_box->add_field( array(
			'name' => __( 'Open in:', 'links-auto-replacer-pro' ),
			'id'   => PLUGIN_PREFIX . 'open_in',
			'type' => 'select',
			'default' => '_self',
			'options' => array(
					  '_self' => __('Same Window','links-auto-replacer-pro'),
					  '_blank' => __('New Window','links-auto-replacer-pro'),
				),
			'description' => __('If you checked this option, you will allow search engines to follow this link and use it in ranking.','links-auto-replacer-pro'),
		) );


		$add_links_box->add_field( array(
			'name' => __( 'Shrink?', 'links-auto-replacer-pro' ),
			'id'   => PLUGIN_PREFIX . 'shrink',
			'type' => 'checkbox',
			'description' => __('The link will be shortened (e.g example.com/go/amazon)','links-auto-replacer-pro'),
		) );


		

		$add_links_box->add_field( array(
			'name' => __( 'Slug', 'links-auto-replacer-pro' ),
			'default' => $this->last_link_id,
			'id'   => PLUGIN_PREFIX . 'slug',
			'type' => 'text_small',
			'description' => __('The slug for the shortened link','links-auto-replacer-pro').' <span id="lar_slug_example"></span>',
		));



		$add_links_box->add_field( array(
			'name' => __( 'Case Sensitive?', 'links-auto-replacer-pro' ),
			'default' => 'no',
			'id'   => PLUGIN_PREFIX . 'is_sensitive',
			'type' => 'checkbox',
			'description' => __('If you checked this option, the plugin will replace the keywords exactly according to the letters case.','links-auto-replacer-pro').' <span id="lar_slug_example"></span>',
		));
	
	}

	/**
	 * Ajax called action to validate the link data of the meta box.	
	 * @since    2.0.0
	 */
	public function pre_submit_link_validation(){

	    //simple Security check
	    check_ajax_referer( 'my_pre_submit_validation', 'security' );

	   
	    parse_str($_POST['form_data'], $link);

	    if(empty(array_filter($link[PLUGIN_PREFIX.'keywords'])))
	    {
	    	$errors['keywords'] = __('Please provide keyword/s','links-auto-replacer-pro');
	    }
	    if($link[PLUGIN_PREFIX . 'link_type'] == 'external'){
		    if($link[PLUGIN_PREFIX.'url'] == '' OR filter_var($link[PLUGIN_PREFIX.'url'], FILTER_VALIDATE_URL) === false)
		    {
		    	$errors['url'] = __('Please provide a valid url','links-auto-replacer-pro');
		    }
		}



	    // we don't want to touch the DB unless the user fill all the data right.
	    if(empty($errors)){

		    $keywords = $this->get_meta_values(PLUGIN_PREFIX . 'keywords', 'lar_link','publish',$link['post_ID']);
		    foreach ($keywords as $key => $value) {
		   		$intersect = array_intersect($link[PLUGIN_PREFIX.'keywords'], unserialize($value));
		   		if(!empty($intersect)){
		   			$errors['keywords'] = 'keyword\s ( '. implode(',', $intersect) .' ) is already exist';
		   			break;
		   		}
		    }

		    if($link[PLUGIN_PREFIX . 'link_type'] == 'external'){
			    $urls = $this->get_meta_values(PLUGIN_PREFIX . 'url', 'lar_link','publish',$link['post_ID']);
			    if(in_array($link[PLUGIN_PREFIX . 'url'], $urls)){
			    	$errors['url'] = __('URL is already exist','links-auto-replacer-pro');
			    }
		    }else{
		    	if(!is_numeric($link[PLUGIN_PREFIX . 'internal_url'])){
			    	$errors['url'] = __('Choose internal link please.','links-auto-replacer-pro');
		    	}
		    }


		    $slugs = $this->get_meta_values(PLUGIN_PREFIX . 'slug', 'lar_link','publish',$link['post_ID']);
		    if(in_array($link[PLUGIN_PREFIX . 'slug'], $slugs)){
		    	$errors['slugs'] = sprintf(__( 'Slug (%s) is already exist','links-auto-replacer-pro'),$link[PLUGIN_PREFIX . 'slug']);
		    }
		} //empty($errors)
	    	
	    if(!empty($errors)){
	    	echo 'Please correct the following errors: <ul id="lar_errors"><li>';
	    	echo implode('</li><li>', $errors);
	    	echo '</li></ul>';
	    }else{
	    	add_option('last_lar_link_id',$link['post_ID']) or update_option('last_lar_link_id',$link['post_ID']);
	    	echo '1';
	    }

	    die();
	}



	/**
	 * A helper method to get all the values sored in wp_postmeta table that has one key.
	 * @param	 string the meta key.
	 * @param	 string the post type
	 * @param	 string the post status
	 * @param	 number the post ID to exlude from the query
	 * @return 	 stdObject	All the rows with the specified key.
	 * @since    2.0.0
	 */
	public function get_meta_values( $key = '', $type = 'post', $status = 'publish', $post_ID = 0 ) {
		
	    global $wpdb;

	    if( empty( $key ) )
	        return;

	    $r = $wpdb->get_col( $wpdb->prepare( "
	        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
	        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	        WHERE pm.meta_key = '%s' 
	        AND p.post_status = '%s' 
	        AND p.post_type = '%s'
	        AND p.ID <> ".$post_ID
	        , $key, $status, $type ) );
	    
	    return $r;
	}


	/**
	 * This one is used to enqueue the scripts into our link post type page.
	 * @since    2.0.0
	 */
	public function insert_validation_nonce(){

	 	$link_slug = get_post_meta($_GET['post'], PLUGIN_PREFIX.'slug',true);
	 	$link_internal = get_post_meta($_GET['post'], PLUGIN_PREFIX.'internal_url',true);
		
		
	 	?>
		 	<script type="text/javascript">
		 		var internal_id = <?php echo ($link_internal)?$link_internal:"''"; ?>;
		 		var internal_title = '<?php echo get_the_title($link_internal); ?>';
		 		var validation_nonce = '<?php echo wp_create_nonce( 'my_pre_submit_validation' ); ?>'; 
		 		var plugin_prefix = '<?php echo PLUGIN_PREFIX; ?>'; 
		 		var last_link_id = '<?php echo ($link_slug!='')?$link_slug:$this->last_link_id; ?>'; 
		 		var home_url = '<?php echo home_url(); ?>'; 
		 	</script>
	 	<?php 
	 	wp_enqueue_script( $this->Links_Auto_Replacer_Pro.'-validation', plugin_dir_url( __FILE__ ) . 'js/lar-links-validation.js', array( 'jquery' ), $this->version, false );	 	
	 	wp_enqueue_script( $this->Links_Auto_Replacer_Pro.'-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js', array( 'jquery' ), $this->version, false );	 	
		

	}

	/**
	 * A helper method to get all the values sored in wp_postmeta table that has one key.
	 * @param	 string the meta key.
	 * @param	 string the post type
	 * @param	 string the post status
	 * @param	 integer the post ID to exlude from the query
	 * @return 	 stdObject	All the rows with the specified key.
	 * @since    2.0.0
	 */
	public function alter_the_title_bfore_saving(  $post_ID, $post = null  ){
		global $wpdb;
		
		if(get_post_type($post_ID) == 'lar_link'){
	    	if(is_array($_POST[PLUGIN_PREFIX.'keywords'])){
	    		$wpdb->update($wpdb->posts,
						  array('post_title' => implode(',', $_POST[PLUGIN_PREFIX.'keywords']),
						  		'post_status' => 'publish'),
						  array('ID' => $post_ID)
						);
	    	}
	    	

		}
			
	}

	/**
	 * Adding a new meta box to disable the auto-replacement for a specific post,page.
	 *
	 * @since    2.0.0
	 */
	public function disable_for_single_post(){
		$screens = array( 'post', 'page' );

	    foreach ( $screens as $screen ) {
	        add_meta_box( 'lar_meta', __( 'Disable Links Auto Replacer for this post', 'links-auto-replacer-pro' ), array($this,'lar_meta_callback'), $screen );
	    }
	}

	/**
	 * The callback of the disable meta box.
	 * @param	 StdObject the post object
	 * @since    2.0.0
	 */
	public function lar_meta_callback( $post ) {
	    wp_nonce_field( basename( __FILE__ ), 'lar_nonce' );

	    $lar_disabled_meta = get_post_meta( $post->ID );
	    
	    ?>
	 
	    <p>
	        
	        <input type="checkbox" name="lar_disabled" id="lar_disabled" <?php if (   $lar_disabled_meta['lar_disabled'][0] == 'on' ) echo 'checked="checked"'; ?> />
	   		<label for="lar_disabled" class="lar-row-title"><?php _e( 'Disable', 'lar-links-auto-replacer' )?></label>
	    </p>
	 
	    <?php  
	}

	/**
	 * Saving the `disable` option for each post,page.
	 * @param	 integer the post ID.
	 * @since    1.5.0
	 */
	function lar_meta_save( $post_id ) {
 		// If post_type is not post or page, just do nothing.
 		if(!in_array(get_post_type($post_id), array('post','page'))) return;
	    // Checks save status
	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ 'lar_nonce' ] ) && wp_verify_nonce( $_POST[ 'lar_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	 
	    // Exits script depending on save status
	    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
	        return;
	    }
	 
	    // Checks for input and sanitizes/saves if needed
	    if( isset( $_POST[ 'lar_disabled' ] ) ) {

	        update_post_meta( $post_id, 'lar_disabled',  'on'  );
	    }else{
	        update_post_meta( $post_id, 'lar_disabled',  'off'  );

	    }
	}




	/**
	* Add the scripts of the stats page (the map and the chartjs library)
	* @PRO
	* @since    2.0.0
	*/
	public function insert_jqvmap(){
		
	 	wp_enqueue_script( $this->Links_Auto_Replacer_Pro.'-vmap', plugin_dir_url( __FILE__ ) . 'js/jqvmap/jquery.vmap.min.js', array( 'jquery' ), $this->version, false );	 	
	 	wp_enqueue_script( $this->Links_Auto_Replacer_Pro.'-vmapworld', plugin_dir_url( __FILE__ ) . 'js/jqvmap/maps/jquery.vmap.world.js', array( 'jquery' ), $this->version, false );	 	
	 	
	 	wp_enqueue_script( $this->Links_Auto_Replacer_Pro.'-vmap-init', plugin_dir_url( __FILE__ ) . 'js/jqvmap/jquery.vmap.init.js', array( 'jquery' ), $this->version, false );	 	
	 	// add chartjs
	 	wp_enqueue_script( $this->Links_Auto_Replacer_Pro.'-chartjs', plugin_dir_url( __FILE__ ) . 'js/chartjs/Chart.min.js', array( 'jquery' ), $this->version, false );	 	
	 	wp_enqueue_script( $this->Links_Auto_Replacer_Pro.'-chartjs-init', plugin_dir_url( __FILE__ ) . 'js/chartjs.init.js', array( $this->Links_Auto_Replacer_Pro.'-chartjs' ), $this->version, false );	 	


	}


	/**
	* Change the default Wordpress colums heads for links post types.
	* @param	 array default admin colums heads.
	* @return	 array altered colums heads.
	* @since    2.0.0
	*/
	public function lar_columns_head( $defaults ) {
			unset($defaults['title']);
			unset($defaults['author']);
		    $new = array();
			foreach($defaults as $key => $title) {
			    if ($key=='taxonomy-links_category'){ // Put the Thumbnail column before the Author column
			      		
			      		$new['keywords'] = __('Keyword\s','links-auto-replacer-pro');
		    			$new['total_clicks'] = __('Total Clicks','links-auto-replacer-pro');
		    			$new['link'] = __('Link','links-auto-replacer-pro');
		    			
		    			//$new['actions'] = __('Actions','links-auto-replacer-pro');
			  		}
			    	$new[$key] = $title;
			}
			
			return $new;

		    
	}
		 
	/**
	* Change the default Wordpress colums for links post types.
	* @param	 array default admin colums.
	* @return	 array altered colums.
	* @since    2.0.0
	**/
	public function lar_columns_content($column_name, $post_ID) {

		    if ($column_name == 'total_clicks') {
		        $stats = get_post_meta($post_ID, PLUGIN_PREFIX.'stats');

		        echo (isset($stats[0]['total_visits']))?$stats[0]['total_visits']:'-';
		        
		    }

		    if($column_name == 'link'){
		    	echo '<input disabled type="text" value="'.Lar_Link::get_final_url($post_ID).'" />';
		    }

		    if($column_name == 'keywords'){
		    	$keywords = get_post_meta($post_ID, PLUGIN_PREFIX.'keywords',true);
		    	$stats = get_post_meta($post_ID, PLUGIN_PREFIX.'stats');

		    	if(!empty($keywords)){
		    		?>
		    		<strong><a class="row-title" href="<?php echo get_edit_post_link($post_ID);  ?>" title="Edit"><?php echo implode(',',$keywords); ?></a></strong>
			    		<div class="row-actions">
				    		<span class="edit"><a href="<?php echo get_edit_post_link($post_ID);  ?>" title="Edit this item">Edit</a> | </span>	
				    		<span class="trash"><a class="submitdelete" title="Move this item to the Trash" href="<?php echo get_delete_post_link($post_ID); ?>">Trash</a> | </span>
				    		<?php if($stats[0]['total_visits']>0): ?><span class="stats"><a title="View Stats" href="<?php echo Lar_Stats::get_stats_link($post_ID); ?>">Stats</a> | </span><?php endif; ?>
				    		<span class="edit"><a target="_blank" href="<?php echo Lar_Link::get_final_url($post_ID);  ?>" title="Visit the link">Visit Link</a> | </span>	
			    		</div>

		    		<?php
		    		
		    	}else{
		    		echo '-';
		    	}
		    	
		    }
	}






	/**
	*
	* This method is called through Ajax request, it's used to search for posts,pages for internal links.
	* @since    2.0.0
	*
	**/
	public function search_internal_linking(){
		$s = $_GET['q'];

		$args = array(
			'post_type' => array( 'post', 'page' ),
			's' =>$s
		);
		$posts = get_posts($args);
		 
		$result  = array();
		$i = 0;
		foreach($posts as $p){
			$single[$i]['id'] = $p->ID;
			$single[$i]['text'] = $p->post_title.' ('.ucfirst(get_post_type($p->ID)).')';
			array_push($result, $single);
			$i++;
		}
		
		echo json_encode($result[0]);
		exit;


	}
	/**
	* If the license key is not set, redirect the user to the Upgrade page.
	* @since    2.0.0
	**/
	public function check_licence(){
		if($_GET['post_type'] == 'lar_link' && lar()->get_option(PLUGIN_PREFIX.'license_email')==''){
			wp_redirect(admin_url().'admin.php?page=lar_upgrade_settings');
			exit;
		}
	}


	




}
