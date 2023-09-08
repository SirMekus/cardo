<?php

namespace App\Http\Responses;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse extends Controller implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function toResponse($request)
    {
        $data["status"] = true;
		$data["redirect_url"] = route('verification.notice');
		$data["replace"] = true;
        
		return response($data);
    }
}
