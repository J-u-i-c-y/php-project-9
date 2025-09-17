PORT ?= 8000

start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

install:
	composer install

clear-cache:
	composer clear-cache

lint:
	vendor/bin/phpcs

clean-lint:
	vendor/bin/phpcbf

test:
	phpunit --bootstrap vendor/autoload.php tests
