<?php


class Lar_Stats{

	private $geo_service_url = 'http://api.netimpact.com/qv1.php?';
	private $country = '';
	private $referer = '';
	private $browser = '';
	private $platform = '';

	public function __construct(){
		$this->country = $this->get_country();
		$this->referer = $this->get_referer();
		$this->browser = $this->get_browser(); //$this->get_browser();
		$this->platform = $this->browser['platform']; //$this->get_browser();


	}


	/**
	* Using the netimpact API to get the user's country.
	* @return	 string the country.
	* @since    2.0.0
	**/
	private function get_country(){
		
		$data['key'] = '5lQslWDX8NbSyWYX';
		$data['qt'] = 'geoip';
		$data['d'] = 'json';
		$data['q'] = $this->get_client_ip();

		$response = wp_remote_get($this->geo_service_url. http_build_query($data));
		if(is_wp_error($response)){
			return 'unknown';
		}

		$body = json_decode( wp_remote_retrieve_body( $response ),true);
		
		return (isset($body[0][6]))?$body[0][6]:'unknown';
	}	



	/**
	* Get the referer.
	* @return	 string the referer.
	* @since    2.0.0
	**/
	private function get_referer(){
		return $_SERVER["HTTP_REFERER"];
	}


	/**
	* Get the user's browser    
	* @return	 string the browser.
	* @since    2.0.0
	**/
	private function get_browser( ){
			
			$u_agent = $_SERVER['HTTP_USER_AGENT']; 
		    $bname = 'Unknown';
		    $platform = 'Unknown';
		    $version= "";

		    //First get the platform?
		    if (preg_match('/linux/i', $u_agent)) {
		        $platform = 'linux';
		    }
		    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		        $platform = 'mac';
		    }
		    elseif (preg_match('/windows|win32/i', $u_agent)) {
		        $platform = 'windows';
		    }
		    
		    // Next get the name of the useragent yes seperately and for good reason
		    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
		    { 
		        $bname = 'Internet Explorer'; 
		        $ub = "MSIE"; 
		    } 
		    elseif(preg_match('/Firefox/i',$u_agent)) 
		    { 
		        $bname = 'Mozilla Firefox'; 
		        $ub = "Firefox"; 
		    } 
		    elseif(preg_match('/Chrome/i',$u_agent)) 
		    { 
		        $bname = 'Google Chrome'; 
		        $ub = "Chrome"; 
		    } 
		    elseif(preg_match('/Safari/i',$u_agent)) 
		    { 
		        $bname = 'Apple Safari'; 
		        $ub = "Safari"; 
		    } 
		    elseif(preg_match('/Opera/i',$u_agent)) 
		    { 
		        $bname = 'Opera'; 
		        $ub = "Opera"; 
		    } 
		    elseif(preg_match('/Netscape/i',$u_agent)) 
		    { 
		        $bname = 'Netscape'; 
		        $ub = "Netscape"; 
		    } 
		    
		    // finally get the correct version number
		    $known = array('Version', $ub, 'other');
		    $pattern = '#(?<browser>' . join('|', $known) .
		    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		    if (!preg_match_all($pattern, $u_agent, $matches)) {
		        // we have no matching number just continue
		    }
		    
		    // see how many we have
		    $i = count($matches['browser']);
		    if ($i != 1) {
		        //we will have two since we are not using 'other' argument yet
		        //see if version is before or after the name
		        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
		            $version= $matches['version'][0];
		        }
		        else {
		            $version= $matches['version'][1];
		        }
		    }
		    else {
		        $version= $matches['version'][0];
		    }
		    
		    // check if we have a number
		    if ($version==null || $version=="") {$version="?";}
		    
		    return array(
		        'userAgent' => $u_agent,
		        'name'      => $bname,
		        'version'   => $version,
		        'platform'  => $platform,
		        'pattern'    => $pattern
		    );

	}


	/**
	* Get the user's IP.
	* @return	 string the IP address.
	* @since    2.0.0
	**/
	private function get_client_ip() {
	    $ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	       $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}





	/**
	* Saving the stats
	* @param     integer link ID
	* @since    2.0.0
	**/
	public function save($link_id){

		static $times = 0;
		$times++;
		if($times != 1) return;
		if(get_post_type( $link_id )!=='lar_link') return;

		$old_stats = get_post_meta($link_id, PLUGIN_PREFIX.'stats');

		$today = date('d-m-Y');

		if(empty($old_stats)){
			$new_stats['countries'][$this->country] = 1;
			$new_stats['referers'][$this->referer] = 1;
			$new_stats['browsers'][$this->browser['name']] = 1;
			$new_stats['platforms'][$this->platform] = 1;
			$new_stats['visits'][$today] = 1;
			$new_stats['total_visits'] = 1;
		
			add_post_meta($link_id, PLUGIN_PREFIX.'stats', $new_stats);
		}else{
			
			
			$new_stats['countries'][$this->country] = $old_stats[0]['countries'][$this->country]+1;
			$new_stats['visits'][$today] = $old_stats[0]['visits'][$today]+1;
			$new_stats['total_visits'] = $old_stats[0]['total_visits'] +1;
			
			if($this->browser != ''){
				
				$new_stats['browsers'][$this->browser['name']] = $old_stats[0]['browsers'][$this->browser['name']]+1;

			}
			if($this->referer != ''){
				$new_stats['referers'][$this->referer] = $old_stats[0]['referers'][$this->referer]+1;
				
			}
			if($this->platform != ''){
				$new_stats['platforms'][$this->platform] = $old_stats[0]['platforms'][$this->platform]+1;
			}


			
			
			
			update_post_meta( $link_id, PLUGIN_PREFIX.'stats' ,$new_stats);
		}

	}

	/**
	* Static method to get the url for stats for a specific link.
	* @param	 integer link ID.
	* @return	 string stats URL.
	* @since    2.0.0
	**/
	public static function get_stats_link($link_id){
		return admin_url('admin.php?page=lar_link_stats&id='.$link_id);
	}







}