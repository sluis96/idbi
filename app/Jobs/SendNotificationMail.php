<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Mail;
use App\Mail\NotificationMail; 

class SendNotificationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $destinatarios;
    protected $group;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($destinatarios, $group, $user)
    {
        $this->destinatarios = $destinatarios;
        $this->group = $group;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->destinatarios as $destinatario) {
            Mail::to($destinatario)
            ->send(new NotificationMail($this->group, $this->user));
        }
    }
}
