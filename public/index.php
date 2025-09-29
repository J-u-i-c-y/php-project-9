<?php

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use Dotenv\Dotenv;
use Illuminate\Support\Collection;
use Hexlet\Code\Url;
use Hexlet\Code\UrlRepo;
use Hexlet\Code\UrlValidator;
use Hexlet\Code\UrlNormalize;
use Hexlet\Code\Check;
use Hexlet\Code\CheckRepo;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

session_start();

$container = new Container();
$container->set('renderer', function () {
        return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
    }
);

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$container->set(\PDO::class, function () {
        $databaseUrl = parse_url($_ENV['DATABASE_URL']);

        $user = $databaseUrl['user'];
        $pass = $databaseUrl['pass'];
        $host = $databaseUrl['host'];
        $port = $databaseUrl['port'];
        $dbName = ltrim($databaseUrl['path'], '/');
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $host,
            $port,
            $dbName,
            $user,
            $pass
        );

        $conn = new PDO($conStr, $user, $pass);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

// Главная страница с формой
$app->get('/', function ($request, $response) {
        return $this->get('renderer')->render($response, 'index.phtml');
    }
)->setName('urls.index');

$app->post('/urls', function ($request, $response) use ($router) {
        $urlRepo = $this->get(UrlRepo::class);
        // $urlData = $request->getParsedBodyParam('url');

        // $userData = $request->getParsedBodyParam('user');
        $parsedBody = $request->getParsedBody();
        $urlData = $parsedBody['url'] ?? [];


        $validator = new UrlValidator();
        $errors = $validator->validate($urlData);

        if (count($errors) === 0) {
            $normalize = new UrlNormalize();
            $urlData['name'] = $normalize->normalize($urlData['name']);
            $url = Url::fromArray([$urlData['name']]);

            if ($urlRepo->isNameExists($url)) {
                $id = $url->getId();
                $this->get('flash')->addMessage('success', 'Страница уже существует');

                return $response
                        ->withHeader('Location', $router->urlFor('urls.show', ['id' => $id]))
                        ->withStatus(302);
            }

            $urlRepo->save($url);
            $id = $url->getId();
            $this->get('flash')->addMessage('success', 'Страница успешно добавлена');

            return $response
                        ->withHeader('Location', $router->urlFor('urls.show', ['id' => $id]))
                        ->withStatus(302);
        }

        $params = [
            'urlData' => $urlData,
            'errors' => $errors
        ];

        return $this->get('renderer')->render($response->withStatus(422), 'index.phtml', $params);
    }
);

// Страница со списком URL-ов
$app->get('/urls', function ($request, $response) {
        $urlRepo = $this->get(UrlRepo::class);
        $urls = $urlRepo->getEntities();
        $params = ['urls' => $urls];

        return $this->get('renderer')->render($response, 'store.phtml', $params);
    }
)->setName('urls.store');

// Страница одного URL по ID
$app->get('/urls/{id}', function ($request, $response, $args) {
        $urlRepo = $this->get(UrlRepo::class);
        $id = $args['id'];
        $url = $urlRepo->find($id);

        if (is_null($url)) {
            return $this->get('renderer')->render($response->withStatus(404), '404.phtml');
        }

        $checkRepo = $this->get(CheckRepo::class);
        $checks = $checkRepo->findByUrlId($id);
        $flash = $this->get('flash')->getMessages();
        $params = ['url' => $url, 'flash' => $flash, 'checks' => $checks];

        return $this->get('renderer')->render($response, 'show.phtml', $params);
    }

)->setName('urls.show');

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router) {
        $urlId = $args['url_id'];
        $check = Check::fromArray([$urlId]);
        $checkRepo = $this->get(CheckRepo::class);
        $urlRepo = $this->get(UrlRepo::class);
        $url = $urlRepo->find($urlId);
        $checkWithRequestStatus = $check->checkStatus($url->getName());

        if (is_null($checkWithRequestStatus)) {
            $this->get('flash')->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
        } else {
            $parsedCheck = $checkWithRequestStatus->parseHtml($url->getName());
            $checkRepo->save($parsedCheck);
            $this->get('flash')->addMessage('success', 'Страница успешно проверена');
        }

        return $response
            ->withHeader('Location', $router->urlFor('urls.show', ['id' => $urlId]))
            ->withStatus(302);
    }
);

$app->run();
