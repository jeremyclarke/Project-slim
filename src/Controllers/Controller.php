<?php
/**
 * Created by PhpStorm.
 * User: jerem
 * Date: 11/04/2017
 * Time: 20:07
 */

namespace App\Controllers;


class Controller
{
    protected $db;
    protected $twig;
    protected $mail;
    protected $rlib;

    public function __construct($db, $twig, $mail, $rlib)
    {
        $this->db = $db;
        $this->twig = $twig;
        $this->mail = $mail;
        $this->rlib = $rlib;
    }
}



