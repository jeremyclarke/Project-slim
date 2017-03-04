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
        //echo $e->getMessage();
        die();
    }
};

//twig
$container['twig'] = function ($container) {
    $twig = new \Slim\Views\Twig(__DIR__ . '/../templates', [
        'cache' => false // todo:  turn on before live
    ]);
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $twig->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    $twig->getEnvironment()->addGlobal(
        'rootURL',
        empty($_SERVER['HTTPS']) ? 'http://' . $_SERVER['SERVER_NAME'] : 'https://' . $_SERVER['SERVER_NAME']
    );
    return $twig;
};