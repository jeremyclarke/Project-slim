<?php

namespace App\Controllers\Mail;
//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);

class Message
{
    private $mailer;

    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    public function to($address)
    {
        $this->mailer->addAddress($address);
    }

    public function subject($subject)
    {
        $this->mailer->Subject = $subject;
    }

    public function body($body)
    {
        $this->mailer->Body = $body;
    }
}
