<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\UsedCard;

class TaskController extends Controller
{
    public function getTasks()
    {
        return Task::all();
    }

    public function createTask()
    {
        request()->validate([
            'card_id' => ['required','numeric','exists:App\Models\Card,id',],
            'merchant_id' => ['required','numeric','exists:App\Models\Merchant,id'],
            ]);

        //Check if the card has been used for this merchant in the past. We bring the latest 'previous card id' via this sql call if it exists.
        $usedCard = Task::where('merchant_id', request()->merchant_id)->orderBy('created_at', 'desc')->first();

        //Then it is not the first time
        if($usedCard)
        {
            UsedCard::create([
                'card_id'=>$usedCard->card_id,
                'merchant_id'=>request()->merchant_id,
            ]);
        }

        $task = new Task;
        $task->card_id = request()->card_id;
        $task->merchant_id = request()->merchant_id;
        $task->save();

        return response('Card Switcher task was successfully created.');
    }

    public function marktask()
    {
        request()->validate([
            'task_id' => ['required','numeric','exists:App\Models\Task,id'],
            'status' => ['required','boolean'],
            ]);

        Task::where('id', request()->task_id)->update([
            'status'=>request()->status
        ]);

        return response('Task wass successfully marked.');
    }
}
