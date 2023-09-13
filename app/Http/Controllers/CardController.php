<?php

namespace App\Http\Controllers;

use App\Models\Card;

class CardController extends Controller
{
    public function getCards()
    {
        return Card::all();
    }

    public function createCard()
    {
        $messages = [
            'user_id.unique'=>"This user already has a card registered. Please try again with another user.",
            'card_number.unique'=>"This card already exists. Please register a new card."
        ];

        request()->validate([
            'card_number' => ['required','numeric','digits:16','unique:App\Models\Card'],
            'expiration' => ['bail', 'required', 'date'],
            'cvv' => ['bail', 'required', 'numeric','digits:3'],
            'user_id' => ['required','numeric','unique:App\Models\Card,id'],
            ], $messages);

        $card = new Card;
        $card->card_number = request()->card_number;
        $card->expiration = request()->expiration;
        $card->cvv = request()->cvv;
        $card->user_id = request()->user_id;
        $card->save();

        return response('Card information was successfully saved.');
    }
}
