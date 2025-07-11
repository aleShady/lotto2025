<?php
return [
    'env' => ($_SERVER['HTTP_HOST'] === 'localhost') ? 'local' : 'prod',

    'urls' => [
        'local' => 'http://localhost/lotto/App/proxy.php',
        'prod' => 'https://dsantarella.altervista.org/lotto/App/proxy.php'
    ],

    'db' => [
        'host' => 'localhost',
        'name' => 'dsantarella',
        'user' => 'root',
        'pass' => ''
    ]
];
