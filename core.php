<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Facebook Core 
 * 
 * Provides access to the latest Facebook PHP SDK
 * 
 * @author Muntasir Mohiuddin
 */

// Include the Facebook PHP SDK Class
require_once "facebook_sdk/facebook.php";

class Facebook_Core extends Facebook {
	/**
	 * @var Facebook configuration
	 */
	protected static $config;
	
	
	/** 
	 * Constructor of the Facebook API
	 * 
	 * @return Facebook instance
	 */
	function __construct($config){
		self::$config = $config;
		parent::__construct(array(
		  'appId'  	=> self::$config['app_id'],
		  'secret' 	=> self::$config['secret'],
		  'file_upload'	=> self::$config['file_upload'],
		));
	}	


	/**
	 * Returns Facebook config variable. This is a generic function so it would work such as config_<config_name>.
	 * Call to get a config variable is only possible from a non-static context as config is set only during __construct call.
	 *
	 * @var name string name of the config 
	 * @return mixed returns the config value from the config array
	 **/	
	static function config($name) {
		return self::$config[$name];
	}

	
	/**
	 * Basic me call 
	 **/
	function me() {
		return $this->api('/me');
	}
	

	/**
	 * Gets user's friends limited by limit and offset
	 *
	 * @var limit int number of friends to get
	 * @var limit int offset on the limit
	 **/
	function friends($limit=null, $offset=null) {
		return $this->api('/me/friends/?1' . (($limit) ? "&limit=" . $limit : '') . (($offset) ? "&offset=" . $offset : ''));
	}


	/**
	 * Gets user's profile album
	 *
	 * @var fb_user_id int Facebook user ID. 
	 * @return mixed profile album information from Facebook
	 **/
	function get_profile_album($fb_user_id) {
		$q = 'SELECT aid, object_id, owner, visible, owner, can_upload FROM album WHERE type="profile" AND owner="' . $fb_user_id . '"';
		
		$response = $this->api("fql?q=" . urlencode($q));
		
		if(!empty($response['data'][0])) {
			return $response['data'][0];	
		}
		
		return null;
	}
	
	
	/**
	 * Gets user's album(s)
	 *
	 * @var fb_user_id int Facebook user ID. 
	 * @return mixed profile album information from Facebook
	 **/
	function get_albums($fb_user_id) {
		$return = array();
		$album_photos = array();
		
		$queries = array(
			'albums'	=> "SELECT aid, object_id, type, visible, owner, cover_pid, cover_object_id, visible, photo_count, video_count FROM album WHERE owner='" . $fb_user_id . "'",
			'album_covers'	=> "SELECT src_big, src_small, images, aid FROM photo WHERE pid IN (SELECT cover_pid FROM #albums)",
			'photos' => "SELECT src_big, src_small, images, aid FROM photo WHERE aid IN (SELECT aid FROM #albums)",
		);

		$results = $this->api(array(
					'method' => 'fql_multiquery',
					'queries' => $queries
				));
		
		
		$albums = $results[0]['fql_result_set'];
		$album_covers = $results[1]['fql_result_set'];
		$photos = $results[2]['fql_result_set'];
		
		if(is_array($photos)){
			foreach($photos as $photo){
				$album_photos[$photo['aid']][] = $photo;
			}
		}
		
		
		
		foreach($albums as $i=>$data){
			$entry = $data;
			
			if(!empty($album_covers[$i])) {
				$entry['cover'] = $album_covers[$i];
			}
			
			if(!empty($album_photos[$data['aid']])) {
				$entry['photos'] = $album_photos[$data['aid']];
			}
			
			$return[] = $entry;
		}
		
		return $return;
	}
	
	
	/**
	 * Make batch API call to Facebook graph API end point
	 *
	 * @var batch_data mixed array of variables consisting method and relative_url
	 * @return mixed returns an array consisting the result of batch api call.
	 **/
	public function api_batch($batch_data) {
		$post_url = self::$DOMAIN_MAP['graph'];
		
		$post_data = http_build_query(array(
			'access_token' => $this->get_access_token(),
			'batch' => json_encode($batch_data)
		));
		
		$params = array('http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded',
			'content' => $post_data
		));
		
		$context = stream_context_create($params);
		$responses = json_decode(@file_get_contents($post_url, false, $context), true);
		
		$return = array();
		
		foreach($responses as $response) {
			$return[] = json_decode($response['body'], true); 
		}
		
		return $return;
	}
	

	###########
	# WRAPERS #
	###########
	
	/**
	 * Override non-static function references. If 
	 *
	 * @var name string function name with underscore. this parameter is case sensitive
	 * @var arguments mixed array of arguments
	 **/	
	public function __call($name, $arguments) {
		$return = null;
		
		if(preg_match('/^config_/', $name)>0){
			$return = $this->config(str_replace("config_", "", $name));			
		} else {
			$new_name = $this->get_new_function_name($name);
			
			if(method_exists($this, $new_name)){
				$return = call_user_func(array($this, $new_name), $arguments);
			} else {
				trigger_error("Fatal error: no function by name " . $new_name . " or " . $name, E_USER_ERROR);
			}
		}
		return $return;
	}

	/**
	 * Override static function references.
	 *
	 * @var name string function name with underscore. this parameter is case sensitive
	 * @var arguments mixed array of arguments
	 **/	
	public static function __callStatic($name, $arguments) {
		$return = null;
		
		$new_name = self::$get_new_function_name($name);
		
		if(method_exists($this, $new_name)){
			$return = call_user_func(array(self, $new_name), $arguments);
		} else {
			trigger_error("Fatal error: no function by name " . $new_name . " or " . $name, E_USER_ERROR);
		}
		
		return $return;
	}

	/**
	 * Convert the underscored function name to a CamelCased funtion name
	 *
	 * @var name string function name with underscore
	 * @return string CamelCase function name
	 **/	
	protected function get_new_function_name($name){
		// replace _ with white space to create words
		// upper case words
		$new_names = explode(" ", ucwords(str_replace("_", " ", $name)));
		
		// make the first character of the funcation name lower case
		$new_name = strtolower(array_shift($new_names)) . implode("", $new_names); // to make sure it works with version lower than PHP 5.3
		
		return $new_name;
	}

	
}



