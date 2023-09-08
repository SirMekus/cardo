<?php

namespace App\MyClass\SiteAdmin;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class Sirmekus
{
	public function validateBeforeSignup($input)
	{
		switch ($input) {
			case "email":
				//We check if this person has been temporarily registered. If true then we should just resend a new email again
				$User = User::where('email', $input)->first();

				//If it has then we redirect to a particular section in this script. We assume this person needs a resending of the mail.
				if (!empty($User)) {
					abort(422, "Email address is already taken");
				}

				break;

			case "tel":
				//We check if this person has been temporarily registered. If true then we should just resend a new email again
				$User = User::where('phone', $input)->first();

				//If it has then we redirect to a particular section in this script. We assume this person needs a resending of the mail.
				if (!empty($User)) {
					abort(422, "Phone number already associated with a valid user and therefore is not valid");
				}

				break;

			case "school":
				//We check if this person has been temporarily registered. If true then we should just resend a new email again
				$User = User::where('school->name', $input)->first();

				//If it has then we redirect to a particular section in this script. We assume this person needs a resending of the mail.
				if (!empty($User)) {
					abort(422, "This school name has already been taken");
				}

				break;
		}
	}

	function get_country_code($country)
	{
		$country_table = config("settings.tables.country_table");

		//It should not be empty though. We wanna fetch the ISO country code of this Seller
		$result = DB::table($country_table)->select('iso')->where('local_name', $country)->limit(1)->get()->toArray();

		if (empty($result)) {
			abort(422, "Please try again");
		} else {
			$country_code = $result[0]->iso;
		}

		return $country_code;
	}


	//Helps us confirm if a given country exists and was fixed by us
	function verifyCountry($country_id, $return = false)
	{
		$country_table = config("settings.tables.country_table");

		$country = DB::table($country_table)->select('local_name')->when(
			is_numeric($country_id),
			function ($query) use ($country_id) {
				$query->where([['id', $country_id], ['type', 'co'],]);
			},
			function ($query) use ($country_id) {
				$query->where([['local_name', $country_id], ['type', 'co'],]);
			}
		)->get()->toArray();

		if (empty($country)) {
			if ($return == false) {
				abort(422, "Selected country does not exist");
			} else {
				return false;
			}
		} else {
			return trim(strtolower($country[0]->local_name));
		}
	}


	//Confirms if the given state exists (in the selected country) or was fixed
	function verifyState($country_id, $state_id, $return = false)
	{
		$country_table = config("settings.tables.country_table");

		$state = DB::table($country_table)->select('local_name')
			->when(
				is_numeric($country_id),
				function ($query) use ($country_id, $state_id) {
					$query->where([['in_location', $country_id], ['id', $state_id],]);
				},
				function ($query) use ($country_id, $state_id) {
					$query->where([['local_name', $country_id], ['local_name', $state_id],]);
				}
			)->get()->toArray();

		if (empty($state)) {
			if ($return == false) {
				abort(422, "Selected state does not exist in the selected country.");
			} else {
				return false;
			}
		} else {
			return trim(strtolower($state[0]->local_name));
		}
	}


	//This is only called when the specified "town" is one in Nigeria, in this case we wanna confirm if the town truly exists in the selected state
	function verifyTown($state_id, $town, $return = false): bool
	{
		$lga = config("settings.tables.lga");

		$nig_states = config("settings.nigeria_states");

		if (in_array($state_id, $nig_states)) {
			$result = DB::table($lga)->select('name')->whereRaw("state_id = ? and LCASE(name) = ?", [$state_id, $town])->get()->toArray();
			
			if (empty($result)) {
				if ($return == false) {
					abort(422, "Selected town does not exist in the selected state.");
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
		else
		{
			return true;
		}
	}
}
