.DEFAULT_GOAL := help

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

include .env

####
# References:
# 	https://php.earth/docs/interop/make
# 	https://docs.docker.com/engine/reference/run/
# 	https://docs.docker.com/compose/reference/up/
# 	https://docs.docker.com/compose/reference/down/
# 	https://docs.docker.com/compose/reference/exec/
#
#
# https://www.client9.com/self-documenting-makefiles/
help:
	@echo "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m"
	@awk -F ':|##' '/^[^\t].+?:.*?##/ {\
	printf "\033[36m%-30s\033[0m %s\n", $$1, $$NF \
	}' $(MAKEFILE_LIST)

include .env

artisan: ## Laravel's artisan command.
	@docker-compose exec php \
	php artisan $(arg)

# apidoc:
# 	@docker-compose exec -T php ./web/src/app/vendor/bin/apigen generate app/src --destination app/doc
# 	@make resetOwner

build:
	@docker-compose build php
	@docker-compose build composer

build-dev: ## Build the development environment.
	@echo "+-----------------------------------------------+"
	@echo "| Building development ready environment        |"
	@echo "+-----------------------------------------------+"
	@docker-compose build
	@echo "+-----------------------------------------------+"
	@echo "| Starting services                             |"
	@echo "+-----------------------------------------------+"
	@docker-compose up -d
	# Pull the repo
	@echo "+-----------------------------------------------+"
	@echo "| Installing server-side dependencies           |"
	@echo "+-----------------------------------------------+"
	@make composer arg="install"
	@echo "+-----------------------------------------------+"
	@echo "| Verifying coding standards                    |"
	@echo "+-----------------------------------------------+"
	@make phpcs
	@echo "+-----------------------------------------------+"
	@echo "| Verifying test                                |"
	@echo "+-----------------------------------------------+"
	@make test
	@echo "+-----------------------------------------------+"
	@echo "| Installing database                           |"
	@echo "+-----------------------------------------------+"
	@make artisan arg="migrate"
	# @make artisan arg="passport:install"
	@echo "+-----------------------------------------------+"
	@echo "| Installing Elasticsearch search index pattern |"
	@echo "+-----------------------------------------------+"
	@make artisan arg="elasticsearch:install"
	@echo "+-----------------------------------------------+"
	@echo "| Installing Elasticsearch templates            |"
	@echo "+-----------------------------------------------+"
	@make artisan arg="elasticsearch:installTemplate"
	@echo "+-----------------------------------------------+"
	@echo "| Installing front-end dependencies             |"
	@echo "+-----------------------------------------------+"
	@make npm arg="install"
	@echo "+-----------------------------------------------+"
	@echo "| Turning on dev                                |"
	@echo "+-----------------------------------------------+"
	@make npm arg="run dev"
	@echo "+-----------------------------------------------+"
	@echo "| Available commands                            |"
	@echo "+-----------------------------------------------+"
	@make help
	@echo "+-----------------------------------------------+"
	@echo "| Services are ready:                           |"
	@echo "+-----------------------------------------------+"
	@echo "| APP URL (https) https://$(NGINX_HOST):44300/	|"
	@echo "| MySQL           http://$(NGINX_HOST):8080/ 	|"
	@echo "| Kibana Dasboard http://$(NGINX_HOST):5601/ 	|"
	@echo "+-----------------------------------------------+"

build-prod: ## Build production ready app.
	@echo "+-----------------------------------------------+"
	@echo "| Building development ready environment        |"
	@echo "+-----------------------------------------------+"
	@docker-compose build
	@echo "+-----------------------------------------------+"
	@echo "| Starting services                             |"
	@echo "+-----------------------------------------------+"
	@docker-compose up -d
	# Pull the repo
	@echo "+-----------------------------------------------+"
	@echo "| Installing server-side dependencies           |"
	@echo "+-----------------------------------------------+"
	@make composer arg="install"
	@echo "+-----------------------------------------------+"
	@echo "| Verifying coding standards                    |"
	@echo "+-----------------------------------------------+"
	@make phpcs
	@echo "+-----------------------------------------------+"
	@echo "| Verifying test                                |"
	@echo "+-----------------------------------------------+"
	@make test
	@echo "+-----------------------------------------------+"
	@echo "| Installing database                           |"
	@echo "+-----------------------------------------------+"
	@make artisan arg="migrate"
	# @make artisan arg="passport:install"
	@echo "+-----------------------------------------------+"
	@echo "| Installing Elasticsearch search index pattern |"
	@echo "+-----------------------------------------------+"
	@make artisan arg="elasticsearch:install"
	@echo "+-----------------------------------------------+"
	@echo "| Installing Elasticsearch templates            |"
	@echo "+-----------------------------------------------+"
	@make artisan arg="elasticsearch:installTemplate"
	@echo "+-----------------------------------------------+"
	@echo "| Installing front-end production dependencies  |"
	@echo "+-----------------------------------------------+"
	@make npm arg="install --production"
	@echo "+-----------------------------------------------+"
	@echo "| Turning on dev                                |"
	@echo "+-----------------------------------------------+"
	@make npm arg="run production"
	@echo "Done."

composer: ## Composer, for PHP.
	@docker run --rm \
		-v $(shell pwd)/web/src:/app \
		-v $(shell pwd)/composer/cache:/root/.composer \
		$(APP_NAME)_composer $(arg)

gen-certs:
	@docker run --rm \
	-v $(shell pwd)/web/etc/ssl:/certificates \
	-e "SERVER=$(NGINX_HOST)" jacoelho/generate-certificate

install: ## Install; build images(?), ssl, dependencies
	@echo "TODO"

mysql-dump: ## Export all databases to the path/file defined in the .env file.
	@echo "Exporting all databases to: $(MYSQL_DUMPS_DIR)/$(MYSQL_DUMPS_FILE)..."
	@mkdir -p $(MYSQL_DUMPS_DIR)
	@docker exec $(shell docker-compose ps -q mysqldb) \
		mysqldump --all-databases \
		-u"$(MYSQL_ROOT_USER)" \
		-p"$(MYSQL_ROOT_PASSWORD)" \
		> $(MYSQL_DUMPS_DIR)/$(MYSQL_DUMPS_FILE) \
		2>/dev/null
	@make resetOwner
	@echo "Done."

mysql-restore: ## Import all databases from the path/file defined in the .env file.
	@echo "Importing all databases from: $(MYSQL_DUMPS_DIR)/$(MYSQL_DUMPS_FILE)..."
	@docker exec -i \
		$(shell docker-compose ps -q mysqldb) \
		mysql -u"$(MYSQL_ROOT_USER)" -p"$(MYSQL_ROOT_PASSWORD)" \
		< $(MYSQL_DUMPS_DIR)/$(MYSQL_DUMPS_FILE) \
		2>/dev/null
	@echo "Done."

npm:
	@docker run --rm -v \
		$(shell pwd)/web/src:/app \
		$(APP_NAME)_node \
		sh -c "cd /app ; npm $(arg)"

phpcbf: ## PHP Code Beautifier, "Code is poetry", Because we are lazy, automate it.
	@echo "Checking code for true definition of ugliness..."
	@docker-compose exec -T php \
		./vendor/bin/phpcbf \
		--standard=PSR2 \
		app/
	@echo "Checked."

phpcs: ## PHP Code Standards are there for a reason, use them.
	@echo "Checking the standard code..."
	@docker-compose exec -T php \
		./vendor/bin/phpcs \
		--standard=PSR2 \
		app/
	@echo "Checked."

phpmd: ## Check our code for messy-ness.
	@echo "Be ready to be annoyed..."
	@docker-compose exec -T php \
		./vendor/bin/phpmd \
		app/ \
		text \
		cleancode,codesize,controversial,design,naming,unusedcode

reset: ## Revert app to pre-install state, i.e., remove db, server-side & front-end dependencies, etc.
	@rm -Rf mysqldb/data
	@rm -Rf elasticsearch/esdata1/*
	@rm -Rf redis/data
	@rm -Rf $(MYSQL_DUMPS_DIR)/*
	@rm -Rf web/etc/nginx/default.conf
	@rm -Rf web/etc/ssl/*

resetOwner: ## Reset the owner and group for /etc/ssl, and /web/src
	@$(shell chown -Rf $(SUDO_USER):$(shell id -g -n $(SUDO_USER)) $(MYSQL_DUMPS_DIR) "$(shell pwd)/etc/ssl" "$(shell pwd)/web" 2> /dev/null)

test: ## Test the codebase and generate a code coverage report.
	@echo "Performing testing..."
	@docker-compose exec -T \
		php ./vendor/bin/phpunit \
		--colors=always \
		--configuration ./
	@make resetOwner
	@echo "Report available at ${NGINX_HOST}/phpunit"