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
	 * Basic me call 
	 **/
	function me() {
		return $this->api('/me');
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
	 * Override non-static function references.
	 *
	 * @var name string function name with underscore. this parameter is case sensitive
	 * @var $arguments mixed array of arguments
	 **/	
	public function __call($name, $arguments) {
		
		$return = null;
		$new_name = $this->get_new_function_name($name);
		
		if(method_exists($this, $new_name)){
			$return = call_user_func(array($this, $new_name), $arguments);
		} else {
			trigger_error("Fatal error: no function by name " . $new_name . " or " . $name, E_USER_ERROR);
		}
		
		return $return;
	}

	/**
	 * Override static function references.
	 *
	 * @var name string function name with underscore. this parameter is case sensitive
	 * @var $arguments mixed array of arguments
	 **/	
	public static function __callStatic($name, $arguments) {
		
		$return = null;
		$new_name = sefl::$get_new_function_name($name);
		
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



