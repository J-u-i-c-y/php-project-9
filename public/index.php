<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$renderer = new PhpRenderer(__DIR__ . '/../templates');

$app->get('/', function ($request, $response, $args) use ($renderer) {
    $data = [
        'title' => 'Главная страница',
    ];

    return $renderer->render($response, 'index.php', $data);
});

$app->run();
