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
        //
    }

    public function toResponse($request)
    {
        return response(201);
    }
}
