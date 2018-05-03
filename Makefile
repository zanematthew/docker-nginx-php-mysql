.DEFAULT_GOAL := help

include .env

# MySQL
MYSQL_DUMPS_DIR=data/db/dumps

####
# References:
# 	https://php.earth/docs/interop/make
# 	https://docs.docker.com/engine/reference/run/
# 	https://docs.docker.com/compose/reference/up/
# 	https://docs.docker.com/compose/reference/down/
# 	https://docs.docker.com/compose/reference/exec/
#
# We can't use this neat auto generator for
# help commands because including the .env file
# shows "Makefile" for all targets
# help:
	# @echo "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m"
	# @grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-15s\033[0m %s\n", $$1, $$2}'

help:
	@echo ""
	@echo "Usage:"
	@echo "  make [target] [arg=\"val\"...]"
	@echo ""
	@echo "Commands:"
	@echo "  artisan             Laravel Artisan"
# 	@echo "  apidoc              Generate documentation of API"
# 	@echo "  build               Build application images"
	@echo "  phpcs               Check the API with PHP Code Sniffer (PSR2)"
	@echo "  phpmd               The ever so annoying, but fun PHP Mess Detecor"
	@echo "  phpcbf              Fix dat sh!t."
	@echo "  start               Start the app (launch docker-compose in detached mode)."
	@echo "  stop                Stop the app (stop docker-compose, and remove any volumes)."
# 	@echo "  clean               Clean directories for reset"
# 	@echo "  gen-certs           Generate SSL certificates"
# 	@echo "  logs                Follow log output"
# 	@echo "  mysql-dump          Create backup of whole database"
# 	@echo "  mysql-restore       Restore backup from whole database"
# 	@echo "  test                Test application"

####
#
# Allow us to pass arguments from
#
.RECIPEPREFIX +=

####
#
# Make is by default dedicated to generating executable files from their
# sources and all target names are files in the project folder.
# Most common usage of Make are compiled languages such as C.
#
# The built-in .PHONY target defines targets which should execute their
# recipes even if the file with the same name as target is present in
# the project.
#
# When you're adding a Makefile in your project you should define all
# custom targets as phony to avoid issues if file with same name is
# present in the project.
#
.PHONY: *

artisan:
	@docker-compose exec php \
	php artisan $(arg)

# apidoc:
# 	@docker-compose exec -T php ./web/src/app/vendor/bin/apigen generate app/src --destination app/doc
# 	@make resetOwner

# clean:
# 	@rm -Rf data/db/mysql/*
# 	@rm -Rf $(MYSQL_DUMPS_DIR)/*
# 	@rm -Rf web/vendor
# 	@rm -Rf web/composer.lock
# 	@rm -Rf web/doc
# 	@rm -Rf web/report
# 	@rm -Rf etc/ssl/*


composer:
	@docker run --rm \
		-v $(shell pwd)/web/src:/app \
		-v $(shell pwd)/composer/cache:/root/.composer \
		mybmx_composer $(arg)

start:
	docker-compose up -d

stop:
	@docker-compose down -v
	# @make clean

gen-certs:
	@docker run --rm \
	-v $(shell pwd)/web/etc/ssl:/certificates \
	-e "SERVER=$(NGINX_HOST)" jacoelho/generate-certificate

# logs:
# 	@docker-compose logs -f

mysql-dump:
	@mkdir -p $(MYSQL_DUMPS_DIR)
	@docker exec $(shell docker-compose ps -q mysqldb) mysqldump --all-databases -u"$(MYSQL_ROOT_USER)" -p"$(MYSQL_ROOT_PASSWORD)" > $(MYSQL_DUMPS_DIR)/db.sql 2>/dev/null
	@make resetOwner

mysql-restore:
	@docker exec -i $(shell docker-compose ps -q mysqldb) mysql -u"$(MYSQL_ROOT_USER)" -p"$(MYSQL_ROOT_PASSWORD)" < $(MYSQL_DUMPS_DIR)/db.sql 2>/dev/null

npm-install:
	@docker run --rm -v \
		$(shell pwd)/web/src:/app \
		node \
		sh -c "cd /app ; npm install"

test: phpcs
	@docker-compose exec -T php ./vendor/bin/phpunit --colors=always --configuration ./
	@make resetOwner

resetOwner:
	@$(shell chown -Rf $(SUDO_USER):$(shell id -g -n $(SUDO_USER)) $(MYSQL_DUMPS_DIR) "$(shell pwd)/etc/ssl" "$(shell pwd)/web" 2> /dev/null)

####
#
# PHP Code Beautifier
# 	"Code is poetry", Because we are lazy, automate it.
# docker-compose exec -T \  # Disable pusedo-terminal
# 	php \                   # Use the "php" service
# 	./vendor/bin/phpcbf \   # Path to executable
# 	--standard=PSR2 \       # The standards to use
# 	app/                    # Path to your ugly code that will be automatically beautified.
#
phpcbf:
	@echo "Checking code for true definition of ugliness..."
	@docker-compose exec -T php \
		./vendor/bin/phpcbf \
		--standard=PSR2 \
		app/
	@echo "Checked."

####
#
# PHP Code Standards
# 	Standards are there for a reason, use them.
#
# https://github.com/squizlabs/PHP_CodeSniffer
# https://www.php-fig.org/psr/psr-2/
#
# docker-compose exec -T \  # Disable psuedo-terminal
# 	php \                   # Connect to the "php" service
# 	./vendor/bin/phpcs \    # Path to executable
# 	--standard=PSR2 \       # The standard to use
# 	app/                    # Where your shit code is
#
phpcs:
	@echo "Checking the standard code..."
	@docker-compose exec -T php \
		./vendor/bin/phpcs \
		--standard=PSR2 \
		app/
	@echo "Checked."

####
#
# PHP Mess Detector
# 	Because we like to annoy ourselves.
#
# https://phpmd.org/documentation/index.html
#
# docker-compose exec -T \                                    # Disable the psuedo-terminal
# 	php \                                                     # Connect to the "php" service
# 	./vendor/bin/phpmd \                                      # Path to executable
# 	app/ \                                                    # Destination of code to check
# 	text \                                                    # Desired output
# 	cleancode,codesize,controversial,design,naming,unusedcode # Rules used
#
# @todo This command results in; 'make: *** [phpmd] Error 2'
phpmd:
	@echo "Be ready to be annoyed..."
	@docker-compose exec -T php \
		./vendor/bin/phpmd \
		app/ \
		text \
		cleancode,codesize,controversial,design,naming,unusedcode

build:
	@docker-compose build php
	@docker-compose build composer

# docker run --rm -v /Users/zanekolnik/Documents/docker-nginx-php-mysql/web:/app node sh -c "cd /app ; npm run production"
