<?php

namespace App\Controllers\Mail;

use App\Controllers\Controller;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class MailController// extends Controller

{
    private $mail;
    private $twig;
//    function __construct($db, $twig, $mail, $rlib)
//    {
//        parent::__construct($db, $twig, $mail, $rlib);
//    }

    function __construct($mailer, $twig)
    {
        $this->mail = $mailer;
        $this->twig = $twig;
    }

    public
    function send($template, $data, $callback)
    {
        $message = new Message($this->mail);

        $this->twig->offsetSet('data', $data);

        $message->body($this->twig->fetch($template));

        call_user_func($callback, $message);

        if (!$this->mail->send()) {
            return 'Mailer Error: ' . $this->mail->ErrorInfo;
        } else {
            return true;
        }
    }
}