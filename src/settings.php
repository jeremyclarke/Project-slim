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
        ]
    ],
];
