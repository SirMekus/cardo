<?php

namespace App\Http\Controllers;

use App\Models\Task;

class TaskController extends Controller
{
    public function createTask()
    {
        request()->validate([
            'card_id' => ['required','numeric','exists:App\Models\Card,id',],
            'merchant_id' => ['required','numeric','exists:App\Models\Merchant,id'],
            ]);

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
