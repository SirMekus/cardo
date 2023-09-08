<?php

namespace App\MyClass\SiteAdmin;

class Utility
{
	public $rate = "rates.txt";//This is the root_path+file to the file our exchange rate(s) are saved.
	
	public $desired_currency = "NGN";
	
	public $default_timezone = "+01:00";
	
	public $jwt;
	
	public $jwt_key;
	
	public $request;
	
	public $response;
	
	public $use_file_session = true;
	
	public $allowed_tags = "<span><p><u><strike><hr><b><div><br><i><h1><h2><h3><h4><h5><h6><font><blockquote><ul><li><ol><a><sub><sup><img><table><tr><td>";
    
	public $system_timezone = "+01:00";//This is the timezone in Nigeria and our default/location/business timezone.

	public function __construct()
	{
		//$this->jwt = $jwt;
		
		//$this->jwt_key = config("settings.tokens.jwt_key");
	}
	
	
	function generate_id($length=5)
	{
		$random_generator = openssl_random_pseudo_bytes($length);
        $id = bin2hex($random_generator);// Actual identifier.
	    return $id;
    }
	
	
	function randomNumbers($length=10) 
	{
		$result = "";

        for($i = 0; $i < $length; $i++) 
		{
			$result .= mt_rand(0, 9);
        }

        return $result;
    }

  
	
	public function push_notification($device_id=[], $supplied_notification=[])
	{
		//We can override the values in any key here by setting the key-value in the parameter of this function, else new key-value will be created instead.
		$notification = ["icon"=>"https://".$_SERVER["HTTP_HOST"]."/uploads/for_site/icons/icon-72x72.png",
			             //"badge"=>"https://".$_SERVER["HTTP_HOST"]."/uploads/for_site/icons/icon-72x72.png",
		                 "vibrate"=> [500,110,500,110,450,110,200,110,170,40,450,110,200,110,170,40,500],
		                 "interact"=>true,
						 "renotify"=>true,
						 //"click_action" => $supplied_notification["click_action"],
	                     "image"=> "https://".$_SERVER["HTTP_HOST"]."/uploads/for_site/icons/icon-192x192.png"
                        ];
						
		if((!empty($supplied_notification)) && (is_array($supplied_notification)))
		{
			foreach($supplied_notification as $key=>$value)
			{
				$notification[$key] = $value;
			}
		}
		
		//API URL of FCM
        $url = 'https://fcm.googleapis.com/fcm/send';
		
		
        /*api_key available in:
        Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key*/    
		$api_key = env("server_key");
                
        $fields = ['registration_ids' => $device_id,
		           "notification" => $notification,
                   "data" => [
                              "gotoURL" => $supplied_notification["click_action"],
							  "director" => "Webloit"
                             ],
	               "webpush"=> [
	                            "headers"=>[
	                            "Urgency"=>"high"
		                        ],
                                "fcm_options"=> [
                                                "link"=> $supplied_notification["click_action"]
	                                            ]
                               ]
                  
				  ];

        //header includes Content type and api key
        $headers = array('Content-Type:application/json','Authorization:key='.$api_key);
                
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        
        curl_close($ch);
		//var_dump($result);
		//var_dump(json_encode($fields));
		return json_decode($result, true);
    }
	
	function delete_subscription($regtoken)
	{
        /*api_key available in:
        Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key*/    
		$api_key = env("server_key");
		
		//api key
        $headers = array('Authorization:key='.$api_key);
		
		$url = 'https://iid.googleapis.com/v1/web/iid/' . $regtoken;
        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_URL, $url );
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec ( $ch );
        curl_close($ch);
		echo "deleted";
        var_dump($result);
    }
		
	
	public function isReserved($name, $what='site ID')
	{
		$reserved_names = config("settings.exclusive_names");
		
		if(preg_match("/$reserved_names/", $name, $matches)) 
		{
			abort(422,"<p class='alert alert-danger'>Your $what can not be from a reserved name/company. Please choose another name. if you own this company/name then contact Webloit directly to resolve the issue.</p>");
        }
	}
		
}
?>