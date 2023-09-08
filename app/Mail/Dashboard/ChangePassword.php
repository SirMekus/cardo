<?php

namespace App\Mail\Dashboard;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class ChangePassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $route;

    //public $request;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;

        $this->route = $user instanceof \App\Models\Guardian ? route('my_wards') : route('user.school');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Change of Password')->markdown('emails.dashboard.change-password');
    }
}
