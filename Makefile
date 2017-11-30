help:
	@echo "do a cat!"

install:
	composer install;
	make data;
	make assets;
	make cache;

data:
	mysql -uroot -hadclick.mysql57 < database/struct.sql;
	bin/console doctrine:migrations:migrate --no-interaction
	bin/console doctrine:fixtures:load --no-interaction

queues:
	rabbitmqadmin --host listbroking.rabbitmq declare exchange name=deliver-extraction type=direct --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare exchange name=run-extraction type=direct --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare exchange name=deduplicate-extraction type=direct --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare exchange name=lock-extraction type=direct --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare exchange name=opposition-list-import type=direct --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare exchange name=staging-contact-import type=direct --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare queue name=deliver-extraction durable=true --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare queue name=run-extraction durable=true --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare queue name=deduplicate-extraction durable=true --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare queue name=lock-extraction durable=true --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare queue name=opposition-list-import durable=true --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare queue name=staging-contact-import durable=true --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare binding source=deliver-extraction destination_type="queue" destination=deliver-extraction routing_key=""  --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare binding source=run-extraction destination_type="queue" destination=run-extraction routing_key=""  --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare binding source=deduplicate-extraction destination_type="queue" destination=deduplicate-extraction routing_key=""  --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare binding source=lock-extraction destination_type="queue" destination=lock-extraction routing_key=""  --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare binding source=opposition-list-import destination_type="queue" destination=opposition-list-import routing_key=""  --username=admin --password=admin
	rabbitmqadmin --host listbroking.rabbitmq declare binding source=staging-contact-import destination_type="queue" destination=staging-contact-import routing_key=""  --username=admin --password=admin

assets:
	bin/console assets:install --symlink
	bin/console assetic:dump
	npm install

cache:
	bin/console cache:clear
	bin/console cache:warmup

up:
	composer install --dev
	make data
	make assets
	make cache

tests:
	bin/console doctrine:schema:validate
	make spec
	make unit

unit:
	vendor/phpunit/phpunit/phpunit --configuration app/phpunit.xml.dist

spec:
	./bin/phpspec run -c phpspec.yml
