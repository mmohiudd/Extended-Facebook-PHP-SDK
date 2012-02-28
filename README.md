Extended Facebook PHP SDK (v1.0.0)
----------------------------------

The official [Facebook PHP SDK] is evolving with the platform. This Extended Facebook PHP SDK is what the name suggests. It simply extends the official SDK and includes common functionality such as a basic me and [batch requests] calls. 

It also has a dynamic function overloading for underscore and CamelCase [naming convention] for function names. If you like underscore naming convention like [Kohana] it is probably the SDK you should be using. 

###Usage###
To create an instance:

    require "<path to sdk>/core.php"
    
    $facebook = new Facebook_API(array(
            'app_id'  => 'YOUR_APP_ID',
            'secret' => 'YOUR_APP_SECRET',
    ));

Take look at the [Config](#Config) section for more advanced configuration functionality.

me():

    $me = $facebook->me();


Batch request:

    $batch_data = array(
        array(
                "method"=> "GET",
                "relative_url"=> "me" // get user info
        ),
        
        array(
                "method"=> "GET",
                "relative_url"=> "me/friends" // get user friends
        ),
        
        array(
                "method"=> "GET",
                "relative_url"=> "me/feed/?limit=100" // get 100 feed entries
        ),
    );		

    // this method returns JSON decoded data
    $results = $this->facebook->api_batch($batch_data);
    
    // result order is determined by batch_data array  
    $me = $results[0];
    $friends = $results[1]['data'];
    $feeds = $results[2]['data'];


###Config###
A Facebook application may require a complicated configuration setting like the following.

    $config = array(
        'app_id'                    =>'YOUR_APP_ID',
        'secret'                    =>'YOUR_APP_SECRET',
        'file_upload'               => false,
        'app_namespace'             => 'myapp',
        'page_path'                 => '',
        'page_url'                  => '',
        'page_id'                   => '',	
        'permissions'               => array('email', 'user_photos', 'friends_photos', 'read_stream', 'user_birthday', 'friends_birthday'),
        'ignore_ssl_verification'   => true,
        'cookie'                    => true,
        // use the following numbers(in minutes) to cache Facebook data. The application is going to update record after the chatted limit has reached
        'cache_threshold'           => (array(
                                        'me'        => 721,
                                        'friends'   => 721,
                                        'feed'      => 721,
        )),
    );
 
To get a config value it does not require to write a new method. The class can return any config value. The trick is to call config_<config_name>, if theres is a config variable by that name, this will return the value.

Get app_namespace:

    $app_namespace = $facebook->config_app_namespace();



[Facebook PHP SDK]: https://github.com/facebook/facebook-php-sdk
[batch requests]: http://developers.facebook.com/docs/reference/api/batch/
[naming convention]: http://en.wikipedia.org/wiki/Naming_convention_(programming)
[Kohana]: http://kohanaframework.org/3.2/guide/kohana/conventions
