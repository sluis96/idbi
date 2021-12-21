<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $group;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($group, $user)
    {
        $this->group = $group;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.notification')
                    ->subject('¡Nueva Nota Creada!');
    }
}
