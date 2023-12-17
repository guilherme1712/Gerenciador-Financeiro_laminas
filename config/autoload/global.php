<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

//  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ );
//  $dotenv->load();

 return [
    // 'db' => [
    //     'driver' => 'Pdo',
    //     'dsn'    => sprintf('sqlite3:%s/data/laminastutorial.db', realpath(getcwd())),
    // ],

    // 'db' => [
    //     'driver' => $_ENV['DB_DRIVER'],
    //     'dsn'    => $_ENV['DB_DSN'],
    //     'username' => $_ENV['DB_USER'],
    //     'password' => $_ENV['DB_PASSWORD'],
    // ],

    'db' => [
        'driver'   => 'Pdo',
        'dsn'      => 'mysql:host=127.0.0.1;dbname=gerenciadorFinanceiro',
        'username' => 'root',
        'password' => 'root',
        'options'  => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
            PDO::ATTR_PERSISTENT         => true,
        ],
    ],
];
