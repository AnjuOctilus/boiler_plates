<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApiErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($arrData)
    {
        $this->mail     = $arrData['mail'];
        $this->message  = $arrData['message'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $branch = env('APP_ENV');
        return $this->from('developers@vandalayglobal.com')
                ->subject("Online Plevin Check : Web-Service Failure - ". $this->message)
                ->view('mail.api_error')
                ->with([
                            'userEmailAddress'  => $this->mail,
                            'userMessage'       => $this->message,
                            'branch'            => $branch,
                        ]);
    }
}
