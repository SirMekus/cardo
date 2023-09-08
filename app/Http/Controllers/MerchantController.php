<?php

namespace App\Http\Controllers;

use App\Models\Merchant;

class MerchantController extends Controller
{
    public function getMerchants()
    {
        return Merchant::all();
    }

    public function latestFinishedTask()
    {
        return Merchant::with(['latestTask.card', 'latestTask' => function ($query) {
            $query->where('status', 1);
        }])->get();
    }
}
