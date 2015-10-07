<?php



class Lar_Link{


	/**
	* Get the final url that will be replaced in the frontend.
	* @param	 integer link ID
	* @return	 string the final url.
	* @since    2.0.0
	**/
	public static function get_final_url( $link_id ){
		$link_meta = get_post_meta( $link_id );
		$link_type = isset($link_meta[PLUGIN_PREFIX.'link_type'][0])?$link_meta[PLUGIN_PREFIX.'link_type'][0]:'';
		$link_slug = isset($link_meta[PLUGIN_PREFIX.'slug'][0])?$link_meta[PLUGIN_PREFIX.'slug'][0]:'';
		$link_url = isset($link_meta[PLUGIN_PREFIX.'url'][0])?$link_meta[PLUGIN_PREFIX.'url'][0]:'';
		$link_internal_url = isset($link_meta[PLUGIN_PREFIX.'internal_url'][0])?$link_meta[PLUGIN_PREFIX.'internal_url'][0]:'';
		
		if( $link_type == 'external' OR $link_type == ''){
				if ( get_option('permalink_structure') != '' ) {
					$url = ($link_slug!= '')? site_url().'/go/'.$link_slug : $link_url;
					return '<input disabled type="text" value="'. $url .'" />';
				}else{
					$url =  ($link_slug != '')? site_url().'/index.php?go='.$link_slug : $link_url;
					return '<input disabled type="text" value="'. $url .'" />';
				}
		}elseif($link_type == 'internal'){ // if internal link
				return '<input disabled type="text" value="'. get_permalink($link_internal_url) .'" />';
		}elseif ($link_type == 'popup') {
				// @TODO
				$url = '<a href="#lar_popup_'. $link_id .'" class="open-popup-link">'.__('Preview','links-auto-replacer').'</a>'; 
				$url .= '<div id="lar_popup_'. $link_id .'" class="white-popup mfp-hide">';
				$url .= (isset($link_meta[PLUGIN_PREFIX.'popup_content'][0]))?$link_meta[PLUGIN_PREFIX.'popup_content'][0]:'';
				$url .= '</div>';
				return $url;
		}elseif ($link_type == 'popup_image') {
				$image_src = isset($link_meta[PLUGIN_PREFIX.'popup_image'][0])?$link_meta[PLUGIN_PREFIX.'popup_image'][0]:'';
				$url = '<a href="'. $image_src .'" class="lar-image-link">'.__('Preview','links-auto-replacer').'</a>';
				return $url;
		}elseif ($link_type == 'popup_gallery') {
				$image_src = isset($link_meta[PLUGIN_PREFIX.'popup_gallery'][0])?$link_meta[PLUGIN_PREFIX.'popup_gallery'][0]:'';
				$url = '<a href="'. $image_src .'" class="lar-gallery-link">'.__('Preview','links-auto-replacer').'</a>';
				return $url; 
		}
	}
}