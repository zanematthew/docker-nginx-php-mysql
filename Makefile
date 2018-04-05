# Makefile for Docker Nginx PHP Composer MySQL

include .env

# MySQL
MYSQL_DUMPS_DIR=data/db/dumps

help:
	@echo ""
	@echo "usage: make COMMAND"
	@echo ""
	@echo "Commands:"
	@echo "  apidoc              Generate documentation of API"
	@echo "  phpcs               Check the API with PHP Code Sniffer (PSR2)"
	@echo "  phpmd               The ever so annoying, but fun PHP Mess Detecor"
	@echo "  phpcbf              Fix dat sh!t."
	@echo "  clean               Clean directories for reset"
	@echo "  composer-update     Update PHP dependencies with composer"
	@echo "  composer-install    Install PHP dependencies with composer"
	@echo "  docker-start        Create and start containers"
	@echo "  docker-stop         Stop and clear all services"
	@echo "  gen-certs           Generate SSL certificates"
	@echo "  logs                Follow log output"
	@echo "  mysql-dump          Create backup of whole database"
	@echo "  mysql-restore       Restore backup from whole database"
	@echo "  test                Test application"

init:
	@$(shell cp -n $(shell pwd)/web/composer.json.dist $(shell pwd)/web/composer.json 2> /dev/null)

artisan:
	@docker-compose exec php \
	php artisan $(cmd)

apidoc:
	@docker-compose exec -T php ./app/vendor/bin/apigen generate app/src --destination app/doc
	@make resetOwner

clean:
	@rm -Rf data/db/mysql/*
	@rm -Rf $(MYSQL_DUMPS_DIR)/*
	@rm -Rf web/vendor
	@rm -Rf web/composer.lock
	@rm -Rf web/doc
	@rm -Rf web/report
	@rm -Rf etc/ssl/*

composer-update:
	@docker run --rm -v $(shell pwd)/web:/app composer update

composer-install:
	@docker run --rm -v $(shell pwd)/web:/app composer install

docker-start: init
	docker-compose up -d

docker-stop:
	@docker-compose down -v
	@make clean

gen-certs:
	@docker run --rm -v $(shell pwd)/etc/ssl:/certificates -e "SERVER=$(NGINX_HOST)" jacoelho/generate-certificate

logs:
	@docker-compose logs -f

mysql-dump:
	@mkdir -p $(MYSQL_DUMPS_DIR)
	@docker exec $(shell docker-compose ps -q mysqldb) mysqldump --all-databases -u"$(MYSQL_ROOT_USER)" -p"$(MYSQL_ROOT_PASSWORD)" > $(MYSQL_DUMPS_DIR)/db.sql 2>/dev/null
	@make resetOwner

mysql-restore:
	@docker exec -i $(shell docker-compose ps -q mysqldb) mysql -u"$(MYSQL_ROOT_USER)" -p"$(MYSQL_ROOT_PASSWORD)" < $(MYSQL_DUMPS_DIR)/db.sql 2>/dev/null

npm-install:
	@docker run --rm -v \
		$(shell pwd)/web:/app \
		node \
		sh -c "cd /app ; npm install"

test: phpcs
	@docker-compose exec -T php ./vendor/bin/phpunit --colors=always --configuration ./
	@make resetOwner

resetOwner:
	@$(shell chown -Rf $(SUDO_USER):$(shell id -g -n $(SUDO_USER)) $(MYSQL_DUMPS_DIR) "$(shell pwd)/etc/ssl" "$(shell pwd)/web" 2> /dev/null)

phpcbf:
	@docker-compose exec -T php \
		./vendor/bin/phpcbf \
		--standard=PSR2 \
		app/

# @docker-compose exec -T php ./vendor/bin/phpcbf app/ --standard=PSR2

phpcs:
	@echo "Checking the standard code..."
	@docker-compose exec -T php \
		./vendor/bin/phpcs \
		--standard=PSR2 \
		app/

phpmd:
	@docker-compose exec -T php \
		./vendor/bin/phpmd \
		app/ \
		text \
		cleancode,codesize,controversial,design,naming,unusedcode

.PHONY: clean test phpcs init

# docker run --rm -v /Users/zanekolnik/Documents/docker-nginx-php-mysql/web:/app node sh -c "cd /app ; npm run production"