<?php

namespace App\MyClass\SiteAdmin;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Cookie;

trait HelperTrait
{
	public $key;

	public $appKey;

	public $utility;

	function get_country_code($country)
	{
		$country_table = config("settings.tables.country_table");

		//It should not be empty though. We wanna fetch the ISO country code of this Seller
		$result = DB::table($country_table)->select('iso')->where('local_name', $country)->limit(1)->get()->toArray();

		if (empty($result)) {
			abort(402, "Please try again");
		} else {
			$country_code = $result[0]->iso;
		}

		return $country_code;
	}


	//Helps us confirm if a given country exists and was fixed by us
	function verifyCountry($country_id, $return=false)
	{
		$country_table = config("settings.tables.country_table");

		$country = DB::table($country_table)->select('local_name')->when(is_numeric($country_id), function($query) use ($country_id){
			$query->where([['id', $country_id], ['type', 'co'],]);
		},
		function($query) use ($country_id){
			$query->where([['local_name', $country_id], ['type', 'co'],]);
		})->get()->toArray();

		if (empty($country)) 
		{
			if($return == false)
			{
				abort(402, "Selected country does not exist");
			}
			else
			{
				return false;
			}
		} 
		else 
		{
			return trim(strtolower($country[0]->local_name));
		}
	}


	//Confirms if the given state exists (in the selected country) or was fixed
	function verifyState($country_id, $state_id, $return=false)
	{
		$country_table = config("settings.tables.country_table");

		//$state = DB::table($country_table)->select('local_name')->where([['in_location', $country_id], ['id', $state_id],])->get()->toArray();

		$state = DB::table($country_table)->select('local_name')->when(is_numeric($country_id), function($query) use ($country_id, $state_id){
			$query->where([['in_location', $country_id], ['id', $state_id],]);
		},
		function($query) use ($country_id, $state_id){
			$query->where([['local_name', $country_id], ['local_name', $state_id],]);
		})->get()->toArray();

		if (empty($state)) 
		{
			if($return == false)
			{
				abort(402, "Selected state does not exist in the selected country.");
			}
			else
			{
				return false;
			}
			
		} else {
			return trim(strtolower($state[0]->local_name));
		}
	}


	//This is only called when the specified "town" is one in Nigeria, in this case we wanna confirm if the town truly exists in the selected state
	function verifyTown($state_id, $town, $return=false)
	{
		$lga = config("settings.tables.lga");

		$result = DB::table($lga)->select('name')->whereRaw("state_id = ? and LCASE(name) = ?", [$state_id, $town])->get()->toArray();

		if (empty($result)) 
		{
			if($return == false)
			{
				abort(402, "Selected town does not exist in the selected state.");
			}
			else
			{
				return false;
			}
			
		} else {
			return $result[0]->name;
		}
	}
}
