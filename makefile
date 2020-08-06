local-dev-install:
	cp .docker.env.example .docker.env
	docker-compose build
	docker-compose up -d
	docker-compose exec lychee composer install
	cp .env.example .env
	docker-compose exec lychee php artisan key:generate
	docker-compose exec lychee php artisan migrate --force
	docker-compose exec lychee php artisan db:seed --force

reset-local:
	docker-compose down -v
	docker-compose up -d

composer:
	rm -r vendor  2> /dev/null || true
	composer install --prefer-dist --no-dev

dist: dist-clean
	@zip -r Lychee.zip Lychee

test:
	@if [ -x "vendor/bin/phpunit" ]; then \
		./vendor/bin/phpunit --verbose --stop-on-failure; \
	else \
		echo ""; \
		echo "Please install phpunit:"; \
		echo ""; \
		echo "  composer install"; \
		echo ""; \
	fi

formatting:
	@rm .php_cs.cache 2> /dev/null || true
	@if [ -x "vendor/bin/php-cs-fixer" ]; then \
		./vendor/bin/php-cs-fixer fix -v --config=.php_cs; \
	else \
		echo ""; \
		echo "Please install php-cs-fixer:"; \
		echo ""; \
		echo "  composer install"; \
		echo ""; \
	fi
