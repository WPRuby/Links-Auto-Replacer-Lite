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
				'lar_main_settings' => array(
					    'id' => 'lar_main_settings',
						'title' => __('Settings','links-auto-replacer-pro'),
						'metabox_callback' => array( $this, 'add_main_settings_page_metabox' ),
					),
				'lar_upgrade_settings' => array(
						'id' => 'lar_upgrade_settings',
						'title' => __('Upgrade','links-auto-replacer-pro'),
						'metabox_callback' => array( $this, 'add_upgrade_settings_page_metabox' ),
						'callback' => array($this , 'display_upgrade_page')
					),
				'lar_about_settings' => array(
						'id' => 'lar_about_settings',
						'title' => __('About','links-auto-replacer-pro'),
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
		$this->settings_page =   add_menu_page(__('Links Auto Replacer PRO'),__('Links Auto Replacer PRO'),'manage_options',$this->key,array($this, 'admin_page_display'));

	    foreach ($this->tabs as $key => $tab){
			if(isset($tab['callback']))
				add_submenu_page( 'lar_settings', $tab['title'], $tab['title'], 'manage_options', $key , $tab['callback']);
			else
				add_submenu_page( 'lar_settings', $tab['title'], $tab['title'], 'manage_options', $key , array($this, 'admin_page_display'));

		}
			 
		add_submenu_page( null, __('Link Stats'), __('Link Stats'), 'manage_options', 'lar_link_stats' , array($this, 'display_link_stats'));
		
		
		
	}

	/**
	* Include main settings page template
	* @since     2.0.0
	**/
	public function admin_page_display(){
		// include partial

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/settings.php';
		
	}



	/**
	* Include about page template
	* @since     2.0.0
	**/
	public function display_about_page(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/about.php';

	}



	/**
	* Include upgrade page template
	* @since     2.0.0
	**/
	public function display_upgrade_page(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/upgrade.php';

	}

	/**
	* Include the stats template
	* @since     2.0.0
	**/
	public function display_link_stats(){
		include plugin_dir_path(__FILE__).'partials/link_stats.php';	
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

		// Set our CMB2 fields
		$cmb->add_field( array(
			'name'    => __( 'License Key', 'links-auto-replacer-pro' ),
			'id'      => PLUGIN_PREFIX . 'license_key',
			'type'    => 'text',
			'sanitization_cb' => array($this,'validate_license_key'),

			//'default' => 'yes',
		) );
		$cmb->add_field( array(
			'name'    => __( 'License Email', 'links-auto-replacer-pro' ),
			'id'      => PLUGIN_PREFIX . 'license_email',
			'type'    => 'text_email',
			//'default' => 'yes',
		) );


	}



	/**
	* The meta box fields of the main settings page
	* @since     2.0.0
	**/
	public function add_main_settings_page_metabox(){



		

		$cmb = new_cmb2_box( array(
			'id'      => 'lar_main_settings',
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Set our CMB2 fields
		$cmb->add_field( array(
			'name'    => __( 'Enable Auto Replcement', 'links-auto-replacer-pro' ),
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





	/**
	* Validate the license key.
	* @param	 string the key that user will provide.
	* @return	 string|boolean the key if success, false if the validation failed.
	* @since     2.0.0
	**/
	public function validate_license_key($key){
		$email  = $_POST[PLUGIN_PREFIX.'license_email'];
		$licence_url = 'http://www.waseem-senjer.com/?';
					$activation_data['wc-api'] = 'software-api';
					$activation_data['request'] = 'activation';
					$activation_data['email'] = $email;
					$activation_data['licence_key'] = $key;
					$activation_data['secret_key'] = 'd75e50aa170d86a103e9be301ce1de8f';
					
					$activation_data['product_id'] = 'LARPRO';
					$request = wp_remote_get($licence_url.http_build_query($activation_data));
					$response = json_decode($request['body']);
					
					if($response->activated === true){
						//add_option(PLUGIN_PREFIX.'license_key',$_POST['woocommerce_auspost_auspost_key']) OR update_option('auspost_key', $_POST['woocommerce_auspost_auspost_key']);
						$this->show_message(__('Activation Succeeded, Thank you :)'),'updated'); 
						return $key;
					}else{
						$this->show_message(__('Licence Key or Email is invalid'),'error');
						return false;
					}
		
	}

	/**
	* Show `error` or `success` messages in the admin
	* @param	 string the user's message.
	* @param	 string message type (`error`,`updated`)
	* @since    2.0.0
	**/
	private function show_message( $message, $type){
		echo '<div class="' . $type . '"><p>'. $message .'</p></div>';
	}

}


function lar(){
	static $object = null;
	if ( is_null( $object ) ) {
		$object = new Lar_Settings();
	}

	return $object;
}

lar();

//$GLOBALS['lar_settings'] = new lar_Settings();