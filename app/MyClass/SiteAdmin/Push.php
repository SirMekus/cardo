<?php

namespace App\Repository\SiteAdmin;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

class Push
{
	public $api_key;
	
	public $sender_id;
	
	//When we want to subscribe client to a particular group this is the URL we will use.
	public $notification_url = 'https://fcm.googleapis.com/fcm/notification';
	
	//When we want to send push notification to clients this is the URL to use
	public $send_url = 'https://fcm.googleapis.com/fcm/send';
	
	public $failed_tokens = [];
	
	public $created = false;
	
	public $homepage;
	
	
	public function __construct()
	{	
        /*api_key available in:
        Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key*/    
		$this->api_key = config("settings.tokens.server_key");
		
		$this->sender_id = config("settings.tokens.sender_id");
		
		$this->homepage = route("home");
	}

	/*
	**
	** $operation = add/remove. In reality we will be adding/removing only one token and not multiple since this operation is individual.
	** However, if we are to delete a device group then we will have to remove every individual member in the group and this operation will 
	** involve multiple tokens that will be passed as 2nd parameter.
	**
	**
	**@array $tokens 
	** ... @string
	**/
	public function group_work($group_name, $group_key, $tokens, $operation="add")
	{
		$parameters = [
			            "operation"=>$operation,
                        "notification_key_name"=>$group_name,
                        "notification_key"=>$group_key,
                        "registration_ids"=>$tokens
                      ];
					  
						  
				//header includes Content type and api key
        $headers = array('Content-Type:application/json',"Authorization:key={$this->api_key}", "project_id:{$this->sender_id}");
                
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->notification_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        $result = curl_exec($ch);
		
		curl_close($ch);
		
		$data = json_decode($result, true);
	//echo $this->sender_id;
		if(is_null($data))
		{
			$data["error"] = $result;
		}
			
		return $data;
	}
	
	
	//This is for creating device group for the first time.
	public function create_group($group_name, $tokens=[])
	{
		$param = [
			         "operation"=> "create",
                     "notification_key_name"=> $group_name,
                     "registration_ids"=> $tokens
                 ];

		
		//header includes Content type and api key
        $headers = array('Content-Type:application/json',"Authorization:key={$this->api_key}", "project_id:{$this->sender_id}");
                
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->notification_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
        $result = curl_exec($ch);
		
		curl_close($ch);
		
		$data = json_decode($result, true);
		
		return $data;
	}
	
	
	public function sendNotification($device_id, $supplied_notification=[])
	{
		//We can override the values in any key here by setting the key-value in the parameter of this function, else new key-value will be created instead.
		$notification = ["icon"=>$this->homepage.'/storage/uploads/for_site/icons/icon-72x72.png',
			             //"badge"=>"https://".$_SERVER["HTTP_HOST"]."/uploads/for_site/icons/icon-72x72.png",
		                 "vibrate"=> [500,110,500,110,450,110,200,110,170,40,450,110,200,110,170,40,500],
		                 "interact"=>true,
						 "renotify"=>true,
						 //"click_action" => $supplied_notification["click_action"],
	                     "image"=> $this->homepage.'/storage/uploads/for_site/icons/icon-192x192.png'
                        ];
						
		if((!empty($supplied_notification)) && (is_array($supplied_notification)))
		{
			foreach($supplied_notification as $key=>$value)
			{
				$notification[$key] = $value;
			}
		}
		
		if((is_array($device_id)) and (!empty($device_id)))
		{
			$key_name = "registration_ids";
		}
		
		//"device_id" is a string here. It reps a device group
		else
		{
			$key_name = "to";
		}
                
        $fields = [$key_name => $device_id,
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
        $headers = array('Content-Type:application/json','Authorization:key='.$this->api_key);
                
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->send_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        
		/*
		if($result === FALSE)
		{
			die('FCM Send Error: ' . curl_error($ch));
        }
		*/
        curl_close($ch);
		//var_dump($result);
		//var_dump(json_encode($fields));
		return json_decode($result, true);
    }
	
	
	public function get_push_token($owner)
	{
		$table = config("settings.tables.push_table");

		$result = DB::table($table)->select("token")->where('owner', $owner)->get();

		return $result;
	}
	
	//In all cases the value of the $value parameter is a "token".
	public function pushNotificationCommand($command, $value = "", $priotity = false)
	{
		$table = config("settings.tables.push_table");

		$param = [$value];

		switch ($command) 
		{
			case "insert":
				//It could be that this is fresh insertion or this is a token refresh. If a token refresh we should update the table without making a new entry in the database.
				$utility = new utility();
				
				if(Auth::guard('admin')->check())
				{
					$user_id = Auth::user()->customerid->agent_id;
		        }
		        else if(Auth::check())
		        {
					$user_id = Auth::user()->customerid;
		        }

				//If this variable is set then either of the conditions above was successfully run. The user is logged in.
				if(isset($user_id)) 
				{
					DB::table($table)->updateOrInsert(
					                ['owner' => $user_id],
									['token' => $value]
                    );
				}

				//We don't know who owns this.
				else 
				{
					$result = DB::table($table)->insert(['token' => $value]);
				}

				break;

			case "fetch":
				if (!empty($value)) 
				{
					$result = DB::table($table)->select("token")->where('owner', $value)->limit(1)->first();
				} 
				else if ($priotity == true)
				{
					$result = DB::table($table)->select("token")->where('priotity', 1)->limit(1)->first();
				} 
				else 
				{
					$result = DB::table($table)->select("token")->first();
				}

				break;

				//This is to associate a subscribed token with a now-known User who is the originator of the token. At this stage it is believed that the User is logged in - a Merchant or even an Agent
			case "update":
				//so that we won't update nothing, we first check if this token has ever been saved in db
				$result = DB::table($table)->select("token")->where('token', $value)->first();

				if (!empty($result)) 
				{
					//It means it's found in the database so now that we know the user of the token let's do this
				    if(Auth::guard('admin')->check())
				    {
						$user_id = Auth::user()->agent_id;
		            }
		            else if(Auth::check())
		            {
						$user_id = Auth::user()->customerid;
		            }

					DB::table($table)->where('token', $value)->update(['owner' => $user_id]);
				}
				//Else the User may have been subscribed in the past but network issue or so made the token not to be delivered to the server. In this case we'll add the token and the User to the database now.
				else 
				{
					$this->pushNotificationCommand("insert", $value);
				}

				break;

			case "delete":
				$result = DB::table($table)->where('token', $value)->delete();

				break;

			default:
				exit;
		}
        
		if(isset($result))
		{
			return $result;
		}
	}

	//After a push-notification event this function collects the result from the CURL and the ID's sent to the FCM server and does its's in-house analysis to figure out the msgs that were delivered from those that wasn't and then analyses the reason why they weren't. If they have a key for whatsoever reason called "error" then it means the User has either manually unsubscribe him/herself or the subscription has expired. In this case we should delete the subscription from the database, the User will have to subscribe again.
	public function checkPushResult($res, $ids = "")
	{
		//It means the push to fcm server was successful
		if ((!empty($res)) and (isset($res["multicast_id"]))) {
			//It'll give us an array of results containing the same number of ID's (sent to us). Each result in the array corresponds to an entry in the ID array since they must be the same size (count).
			$result = $res["results"];
			
			$table = config("settings.tables.push_table");

			for ($i = 0; $i < count($result); $i++) 
			{
				//It means the message didn't go through and it's surely because the token has expired or the User has unsubscribed from the browser.
				if (isset($result[$i]["error"])) 
				{
					//The i-th value in the result array maps to the i-th value in the ID's returned from the FCM server
					if (is_array($ids)) 
					{
						$this->pushNotificationCommand("delete", $ids[$i]);
					} 
					else 
					{
						$this->pushNotificationCommand("delete", $ids);
					}
				}
			}
		}

		//In this case the notification was sent to a device group and it was not successful. The tokens in the device group that didn't get delivery will be sent back to us and then we should delete from the database.
		else if (isset($res["failed_registration_ids"])) {
			$tokens = json_decode($res["failed_registration_ids"], true);

			for ($i = 0; $i < count($tokens); $i++) {
				//It means the message didn't go through and it's surely because the token has expired or the User has unsubscribed from the browser.
				$this->push_notification_command("delete", $tokens[$i]);
			}
		}
	}
}

?>