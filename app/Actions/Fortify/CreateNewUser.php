<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\MyClass\SiteAdmin\Sirmekus;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        $message =  [
            'school_owner.required' => 'Registration is strictly for individuals that are school owners. We are sorry for any inconvenience this may have caused you.',
        ];

        Validator::make($input, [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'numeric','unique:App\Models\User,phone'],
            'email' => ['required','string','email','max:255','unique:App\Models\User',],
            'password' => ['bail', 'required', 'string', 'min:8', 'confirmed'],
            'agreement' => ['bail', 'required', 'accepted'],
            'school_owner' => ['bail', 'required', 'accepted'],
        ], $message)->validate();

        $Users = new User;
        
        $Users->sch_id = Str::random(5);

		$Users->name = [
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
            ];
            
		$Users->email = $input['email'];

		$Users->password = $input['password'];
        
		$Users->phone = $input['phone_number'];

        $Users->created_at = now();

		$Users->save();

        return $Users;
    }

    public function toResponse($request)
    {
        return response(201);
    }
}
