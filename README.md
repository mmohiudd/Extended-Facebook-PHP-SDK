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


[Facebook PHP SDK]: https://github.com/facebook/facebook-php-sdk
[batch requests]: http://developers.facebook.com/docs/reference/api/batch/
[naming convention]: http://en.wikipedia.org/wiki/Naming_convention_(programming)
[Kohana]: http://kohanaframework.org/3.2/guide/kohana/conventions