<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    public function setExpirationAttribute($expiration)
	{
        $carbon = carbon($expiration);

        $carbon->day = 1;

		$this->attributes['expiration'] = $carbon->toDateString();
	}

    public function task()
    {
        return $this->hasMany(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
