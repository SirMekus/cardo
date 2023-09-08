<?php

namespace App\MyClass\Contracts;
use Illuminate\Support\Str;
use App\Models\PrePayment;
use App\Models\PaymentHistory;

trait Payment
{
    public function createInvoice(Array $plan)
    {
        return PrePayment::updateOrCreate(
            ['user' => $plan['user'], 'email' => $plan['email'], 'data->name' => $plan['data']['name']],
            [
                'data' => $plan['data'], 
                'type' => $plan['type'],
                'identifier' => Str::random(5),
                'reference' => Str::random(5)
            ]
        );
    }

    public function fetchInvoice($reference)
    {
        return PrePayment::where('reference', $reference)->first();
    }

    public function recordPayment(Array $record)
    {
        return PaymentHistory::create(
            [
                'user_id' => $record['user'],
                'amount' => $record['amount_paid'],
                'currency' => $record['currency'] ?? "NGN",
                'reference' => $record['reference'],
                'type' => $record['type'],
                'description' => $record['description'],
                'payment_method' => $record['payment_method'] ?? null,
                'meta' => $record['meta'] ?? null,
            ]
        );
    }
}
?>
		