<?php 


class Lar_Settings{


	protected $tabs = array();
	protected $key = 'lar_settings';



	/**
	* Define the pages in the constructor.
	* @since     2.0.0
	**/
	public function __construct(){

		$this->tabs = apply_filters('lar_settings_tabs',array(
				'lar_upgrade_settings' => array(
						'id' => 'lar_upgrade_settings',
						'title' => __('Upgrade','links-auto-replacer'),
						'metabox_callback' => false,
						'callback' => array($this , 'display_upgrade_page')
					),
				'lar_about_settings' => array(
						'id' => 'lar_about_settings',
						'title' => __('About','links-auto-replacer'),
						'metabox_callback' => false,
						'callback' => array($this , 'display_about_page')
					),
				

		));
		

		$this->hooks();

	}



	/**
	* Init the settings hooks.
	* @since     2.0.0
	**/
	public function hooks(){
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action('cmb2_init',array($this,'add_main_settings_page_metabox'));
		foreach($this->tabs as $tab){
			if($tab['metabox_callback'] !== false){
				add_action( 'cmb2_init',  $tab['metabox_callback']);
			}
		}
		
	}

	/**
	* register the plugin's settings.
	* @since     2.0.0
	**/
	public function init() {
		register_setting( $this->key, $this->key );
	}


	/**
	* Register the plugin settings page.
	* @since     2.0.0
	**/
	public function add_options_page() {
		$this->settings_page =   add_menu_page(__('Links Auto Replacer Lite'),__('Links Auto Replacer Lite'),'manage_options',$this->key,array($this, 'admin_page_display'));

	    foreach ($this->tabs as $key => $tab){
			if(isset($tab['callback']))
				add_submenu_page( 'lar_settings', $tab['title'], $tab['title'], 'manage_options', $key , $tab['callback']);
			else
				add_submenu_page( 'lar_settings', $tab['title'], $tab['title'], 'manage_options', $key , array($this, 'admin_page_display'));

		}
			 
	
		do_action('lar_add_custom_pages');
		
	}

	/**
	* Include main settings page template
	* @since     2.0.0
	**/
	public function admin_page_display(){
		// include partial

		require_once LAR_DIR . 'admin/partials/settings.php';
		
	}



	/**
	* Include about page template
	* @since     2.0.0
	**/
	public function display_about_page(){
		require_once LAR_DIR . 'admin/partials/about.php';

	}



	/**
	* Include upgrade page template
	* @since     2.0.0
	**/
	public function display_upgrade_page(){
		require_once LAR_DIR . 'admin/partials/upgrade.php';

	}






	/**
	* The meta box fields of the upgrade settings page
	* @since     2.0.0
	**/
	public function add_upgrade_settings_page_metabox(){

		$cmb = new_cmb2_box( array(
			'id'      => 'lar_upgrade_settings',
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		


	}



	/**
	* The meta box fields of the main settings page
	* @since     2.0.0
	**/
	public function add_main_settings_page_metabox(){



		

		$cmb = new_cmb2_box( array(
			'id'      => 'lar_settings',
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Set our CMB2 fields
		$cmb->add_field( array(
			'name'    => __( 'Enable Auto Replcement', 'links-auto-replacer' ),
			'id'      => PLUGIN_PREFIX . 'enable',
			'type'    => 'checkbox',
			//'default' => 'yes',
		) );


		


		
	}
	




	/**
	* Validate the license key.
	* @param	 string the option's key.
	* @return	 string the value of the key.
	* @since     2.0.0
	**/
	public function get_option( $key = '' ) {
		return cmb2_get_option( $this->key, $key );
	}



	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'tabs', 'key' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}










}




//$GLOBALS['lar_settings'] = new lar_Settings();