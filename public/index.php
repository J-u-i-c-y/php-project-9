<?php

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use Illuminate\Support\Collection;
use Hexlet\Code\Url;
use Hexlet\Code\UrlRepo;
use Hexlet\Code\UrlValidator;
use Hexlet\Code\UrlNormalize;
use Hexlet\Code\Check;
use Hexlet\Code\CheckRepo;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

session_start();

$container = new Container();
$container->set(
    'renderer',
    function () {
        return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
    }
);
$container->set(
    'flash',
    function () {
        return new \Slim\Flash\Messages();
    }
);

$container->set(
    \PDO::class,
    function () {
        $databaseUrl = parse_url($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: '');

        $host = $databaseUrl['host'] ?? '127.0.0.1';
        $port = $databaseUrl['port'] ?? 5432;
        $dbname = ltrim($databaseUrl['path'] ?? '', '/');
        $user = $databaseUrl['user'] ?? 'postgres';
        $password = $databaseUrl['pass'] ?? '';

        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $host,
            $port,
            $dbname
        );

        $conn = new \PDO($conStr, $user, $password);
        $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $conn;
    }
);


$initFilePath = implode('/', [dirname(__DIR__), 'database.sql']);
$initSql = file_get_contents($initFilePath);
if ($initSql) {
    $container->get(\PDO::class)->exec($initSql);
}


$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);
$app->add(MethodOverrideMiddleware::class);
$router = $app->getRouteCollector()->getRouteParser();

$app->get(
    '/',
    function ($req, $res) {
        return $this->get('renderer')->render($res, 'index.phtml');
    }
)->setName('urls.index');

$app->post(
    '/urls',
    function ($req, $res) use ($router) {
    $urlRepo = $this->get(UrlRepo::class);
    $urlData = $req->getParsedBodyParam('url') ?? [];
    $errors = UrlValidator::validate($urlData);

    if (!empty($errors)) {
        $params = ['urlData' => $urlData, 'errors' => $errors];
        return $this->get('renderer')->render($res->withStatus(422), 'index.phtml', $params);
    }

    $normalize = new UrlNormalize();
    $urlData['name'] = $normalize->normalize($urlData['name']);
    $url = Url::fromArray([$urlData['name']]);

    if ($urlRepo->isNameExists($url)) {
        $this->get('flash')->addMessage('success', 'Страница уже существует');
    } else {
        $urlRepo->save($url);
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    }

    return $res->withRedirect($router->urlFor('urls.show', ['id' => (string)$url->getId()]));
})->setName('urls.create');

$app->get(
    '/urls',
    function ($req, $res) {
        $urlRepo = $this->get(UrlRepo::class);
        $urls = $urlRepo->getEntities();
        $params = ['urls' => $urls];

        return $this->get('renderer')->render($res, 'store.phtml', $params);
    }
)->setName('urls.store');

$app->get(
    '/urls/{id}',
    function ($req, $res, $args) {
        $urlRepo = $this->get(UrlRepo::class);
        $id = $args['id'];
        $url = $urlRepo->find($id);

        if (is_null($url)) {
            return $this->get('renderer')->render($res->withStatus(404), '404.phtml');
        }

        $checkRepo = $this->get(CheckRepo::class);
        $checks = $checkRepo->findAllByUrlId($id);
        $flash = $this->get('flash')->getMessages();
        $params = ['url' => $url, 'flash' => $flash, 'checks' => $checks];

        return $this->get('renderer')->render($res, 'show.phtml', $params);
    }
)->setName('urls.show');

$app->post(
    '/urls/{url_id}/checks',
    function ($req, $res, $args) use ($router) {
        $urlId = $args['url_id'];
        $urlRepo = $this->get(UrlRepo::class);
        $checkRepo = $this->get(CheckRepo::class);

        $url = $urlRepo->find($urlId);
        if (is_null($url)) {
            return $res->withStatus(404);
        }

        $check = Check::fromArray([$urlId]);
        $performedCheck = $check->checkStatus($url->getName());

        if (is_null($performedCheck)) {
            $this->get('flash')->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
            return $res->withRedirect($router->urlFor('urls.show', ['id' => (string)$urlId]));
        }

        $checkRepo->save($performedCheck);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');

        return $res->withRedirect($router->urlFor('urls.show', ['id' => (string)$urlId]));
    }
)->setName('urls.checks.create');


$app->run();
