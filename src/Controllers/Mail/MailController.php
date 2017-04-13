<?php

namespace App\Controllers\Mail;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class MailController// extends Controller
    // todo: Sweet alert response does not work if email debugging switched on
{
    private $mail;
    private $twig;

    function __construct($mailer, $twig)
    {
        $this->mail = $mailer;
        $this->twig = $twig;
    }

    function send($template, $data, $callback)
    {
        $message = new Message($this->mail);

        $this->twig->offsetSet('data', $data);

        $message->body($this->twig->fetch($template));

        call_user_func($callback, $message);

//        $this->mail->send();

        if ($this->mail->send()) {
            return true;
        }

    }
}