<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};
//db
$container['db'] = function ($c) {
    $db = $c->get('settings')['db'];
    try {
        $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
            $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;

    } catch (PDOException $e) {
        echo 'Problem connecting to database.<br>';
        echo $e->getMessage();
        die();
    }
};

//twig
$container['twig'] = function ($c) {
    $twig = new \Slim\Views\Twig(__DIR__ . '/../templates', [
        'cache' => false
    ]);
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $twig->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    $twig->getEnvironment()->addGlobal('rootURL', empty($_SERVER['HTTPS']) ? 'http://' . $_SERVER['SERVER_NAME'] : 'https://' . $_SERVER['SERVER_NAME']);
    $twig->getEnvironment()->addGlobal('user', $_SESSION['user']);

    return $twig;
};

// PHPMailer
$container['mailer'] = function ($c) {
    $mailer = new PHPMailer(true);
    $settings = $c->get('settings')['mail'];

    $mailer->isSMTP();

//    $mailer->SMTPDebug = 2;
//    $mailer->Debugoutput = 'html';

    $mailer->Host = $settings['host'];
    $mailer->SMTPAuth = $settings['SMTPAuth'];
    $mailer->Username = $settings['user'];
    $mailer->Password = $settings['pass'];
    $mailer->SMTPSecure = $settings['SMTPSecure'];
    $mailer->Port = $settings['port'];
    $mailer->setFrom($settings['user']);

    return new \App\Controllers\Mail\MailController($mailer, $c->twig);
};

// randomlib
$container['randomlib'] = function ($c) {
    $factory = new RandomLib\Factory;
    return $factory->getMediumStrengthGenerator();
};

//////////////////////////////////////////////////////////////////

$container['UserController'] = function ($c) {
    $db = $c->db;
    $twig = $c->twig;
    $mailer = $c->mailer;
    $rlib = $c->randomlib;

    $controller = new \App\Controllers\UserController($db, $twig, $mailer, $rlib);
    return $controller;
};

$container['FormController'] = function ($c) {
    $db = $c->db;
    $twig = $c->twig;
    $mailer = $c->mailer;
    $rlib = $c->randomlib;

    $controller = new \App\Controllers\FormController($db, $twig, $mailer, $rlib);
    return $controller;
};

$container['ObjectController'] = function ($c) {
    $db = $c->db;
    $twig = $c->twig;
    $mailer = $c->mailer;
    $rlib = $c->randomlib;

    $controller = new \App\Controllers\ObjectController($db, $twig, $mailer, $rlib);
    return $controller;
};

//
//$container['MailContoller'] = function ($c) {
//    $db = $c->db;
//    $twig = $c->twig;
//    $mailer = $c->mailer;
//    $rlib = $c->randomlib;
//
//    $controller = new \App\Controllers\Mail\MailController($db, $twig, $mailer, $rlib);
//    return $controller;
//};
