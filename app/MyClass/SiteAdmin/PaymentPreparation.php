<?php

namespace App\Repository\SiteAdmin;

use Illuminate\Http\Request;
use App\Repository\Payment\Paystack;
use App\Models\PrePayment;
use Illuminate\Support\Str;

class PaymentPreparation
{
	public $paystack;

	public function __construct(Paystack $paystack)
	{
		$this->paystack = $paystack;
	}

    public function viaPaystack($data)
    {
		$reference = $data['reference'];

		$amount_in_kobo = $data['amount'] * 100;

        //Now let's store the reference in the database as the pre-payment phase.
        //PrePayment::insert(['reference'=>$reference, 'data'=>$data, 'email'=>$data['email'], 'identifier'=>$data['identifier']]);

        //When the payment is successful and User has been redirected to the central "Payment Success" page this value will be stored in one of the hidden fields and will be used as the URL for verification of the transaction by javascript alongside the sent reference in the $_GET variable 
        request()->session()->put('callbackVerificationController', $data['callback']);

        $postdata =  ["email" =>$data['email'], "amount" => $amount_in_kobo, "reference" => $reference, "callback_url"=>route("default_callback"), "channels"=>["card","bank", "mobile_money", "bank_transfer"], "metadata"=>$data];

		//if(!empty($data['auth_code']))
        if(isset(request()->user()->config['auth_code']))
	    {
			$postdata["authorization_code"] = request()->user()->config['auth_code'];

			$tranx = $this->paystack->charge($postdata);

			if(!empty($tranx))
			{
				if(empty($this->paystack->last_error))
				{
					$data["status"] = true;
					$data["redirect_url"] = route("default_callback")."?reference=$reference";
					$data["msg"] = "<p class='alert alert-successful'>Authentication was successful.</p>";
			
			        return response($data);
				}
				//Else it means the authorization token is invalid (or expired) so we silently "exit" and proceed to asking the user to input his/her card detail (by taking the user to the payment gateway page).
			}
		}

        $tranx = $this->paystack->initialize($postdata);

		//dump($tranx->data->authorization_url);
		// redirect to page so User can pay
		$data["status"] = true;
		$data["redirect_url"] = $tranx->data->authorization_url;
		$data["msg"] = "<p class='alert alert-successful'>Authentication was successful.</p>";
		
        return response($data);
    }
}
?>