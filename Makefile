help:
	@echo "do a cat!"

install:
	composer install;
	make data;
	make assets;
	make cache;

data:
	mysql -uroot -hadclick.mysql57 < database/struct.sql;
	app/console doctrine:migrations:migrate --no-interaction
	app/console doctrine:fixtures:load --no-interaction
#	app/console doctrine:fixtures:load --no-interaction --append

assets:
	app/console assets:install --symlink
	app/console assetic:dump
	npm install

cache:
	app/console cache:clear
	app/console cache:warmup

up:
	composer install --dev
	make data
	make assets
	make cache

tests:
	app/console doctrine:schema:validate
	make spec
	make unit

unit:
	./bin/phpunit -c app

spec:
	./bin/phpspec run -c phpspec.yml
