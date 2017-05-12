<?php
return [
    'settings' => [
        'displayErrorDetails' => false, //todo: set to false in production
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
            'pass' => '4(FjX^=AS3e~mxmM',
            'dbname' => 'project',
        ],

        // PHPMailer
        'mail' => [
            'host' => 'smtp.zoho.com',
            'SMTPAuth' => true,
            'user' => 'no-reply@jeremyclarke.co.uk',
            'pass' => '54^9P@2tyg60',
            'SMTPSecure' => 'tls',
            'port' => 587,
        ]

    ],
];
