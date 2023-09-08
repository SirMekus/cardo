<?php

namespace App\Http\Controllers;

use App\Models\Card;

class CardController extends Controller
{
    public function createCard()
    {
        request()->validate([
            'card_number' => ['required','numeric','digits:16','unique:App\Models\Card'],
            'expiration' => ['bail', 'required', 'date'],
            'cvv' => ['bail', 'required', 'numeric','digits:3'],
            ]);

        $card = new Card;
        $card->card_number = request()->card_number;
        $card->expiration = request()->expiration;
        $card->cvv = request()->cvv;
        $card->save();

        return response('Card information was successfully saved.');
    }
}
