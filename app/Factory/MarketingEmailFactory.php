<?php

namespace App\Factory;

use App\Repositories\PhpMailerRepository;
use App\Repositories\LaravelMailerRepository;

class MarketingEmailFactory
{
    public $emailClient;

    public function __construct()
	{
        $this->emailClient = new LaravelMailerRepository();
        //$this->emailClient = new PhpMailerRepository();
    }
}