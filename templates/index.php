<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <!-- Подключаем Bootstrap через CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="my-4"><?= htmlspecialchars($title) ?></h1>

        <div class="alert alert-primary" role="alert">
            Добро пожаловать на главную страницу нашего сайта, выполненную с использованием <strong>Bootstrap 5</strong>.
        </div>

        <p class="lead">Это пример простого шаблона с подключением Bootstrap через CDN.</p>

        <footer class="mt-5">
            <p>&copy; <?= date('Y') ?> Мой сайт</p>
        </footer>
    </div>

    <!-- Подключаем JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
