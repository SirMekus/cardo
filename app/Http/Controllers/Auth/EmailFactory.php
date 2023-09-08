<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class EmailFactory extends Controller
{
	public function changeEmailPost(Request $request)
	{
		$class = instantiateAuthClass(request()->type);

		$message = ['new_email.not_in'=>"The new email address should be different from your current email address."];

        $request->validate([
			           'new_email' => ['bail', 'required', 'email:rfc', Rule::notIn([request()->user(request()->type)->email]), Rule::unique($class->getTable(),'email')->ignore(request()->user(request()->type)->id, 'id')],
		               'current_email' => ['bail', 'required', 'email:rfc', 'different:new_email',  Rule::exists($class->getTable(), 'email')->where(function ($query) {
						$query->where('id', request()->user(request()->type)->id);
					    }),],
					], $message);
		
		//This is what will be passed as link
		$link = URL::temporarySignedRoute('change-email.verify', now()->addMinutes(15), ['type' => request()->type, 'email'=>$request->new_email]);
		
		Mail::to($request->new_email)->send(new \App\Mail\Dashboard\ChangeEmail($link));
			
		return response("An email has been sent to ".$request->new_email.". Click on the link to verify access to the address and complete the email update.");
	}
	
	//If the user isn't logged n, (s)he won't be able to access this route gan so definitely the user is logged in.
	public function confirmChangeEmailLink(Request $request)
	{
		$class = instantiateAuthClass(request()->type);
		$request->validate(['email'=>['bail', 'required', 'email:rfc', Rule::unique($class->getTable())]]);

		request()->user(request()->type)->email = $request->email;
		request()->user(request()->type)->save();

		return response("Email address has been changed successfully");
	}
}