<?php
return [
    'settings' => [
        'displayErrorDetails' => true, //todo: set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // DB connection
        'db' => [
            'host' => 'jeremydb.cq2ysl1zpgeh.eu-west-2.rds.amazonaws.com:3306',
            'user' => 'jeremy',
            'pass' => 'Xbs5S57g',
            'dbname' => 'project',
        ],

        // PHPMailer //todo: check what happens when mail server is broken
        'mail' => [
            'host' => 'smtp.zoho.com',
            'SMTPAuth' => true,
            'user' => 'no-reply@jeremyclarke.co.uk',
            'pass' => '54^9P@2tyg60',
            'SMTPSecure' => 'tls',
            'port' => 587,
        ]

//        'mail' => [
//            'host' => 'smtp.gmail.com',
//            'SMTPAuth' => true,
//            'user' => 'jeremyclarke100@gmail.com',
//            'pass' => 'bUf8e49h4b',
//            'SMTPSecure' => 'tls',
//            'port' => 587,
//        ]

    ],
];
