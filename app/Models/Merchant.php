<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    public function task()
    {
        return $this->hasMany(Task::class);
    }

    public function latestTask()
    {
        return $this->hasOne(Task::class)->latest('updated_at');
    }
}
