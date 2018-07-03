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

test_report_url         = $(WEB_PROTOCOL)$(NGINX_HOST):$(SSL_PORT)/phpunit/index.html
app_url                 = $(WEB_PROTOCOL)$(NGINX_HOST):$(SSL_PORT)/
app_src_dir             = $(shell pwd)/$(APP_SRC_DIR)
mysql_admin_url         = http://$(NGINX_HOST):$(NON_SSL_PORT)/
elasticsearch_admin_url = http://$(NGINX_HOST):5601/
repo_ssh_url            = git@github.com:zanematthew/docker-nginx-php-mysql.git
app_php_docs            = $(WEB_PROTOCOL)$(NGINX_HOST):$(SSL_PORT)/documentation/app/index.html
app_api_docs            = $(WEB_PROTOCOL)$(NGINX_HOST):$(SSL_PORT)/docs/index.html

artisan: ## Laravel's artisan command.
	@docker-compose exec php \
	php artisan $(arg)

app-info: ## Display info such as; URLs, DB connection, etc.
	@echo "+-----------------------------------------------+"
	@echo "| Application is now available.                 |"
	@echo "+-----------------------------------------------+"
	@echo "App Name            : $(APP_NAME)"
	@echo "---"
	@echo "APP URL             : $(app_url)"
	@echo "MySQL Dashboard     : $(mysql_admin_url)"
	@echo "Kibana Dasboard     : $(elasticsearch_admin_url)"
	@echo "PHPUnit Report      : $(test_report_url)"
	@echo "---"
	@echo "Host                : $(NGINX_HOST)"
	@echo "---"
	@echo "PHP Version         : $(PHP_VERSION)"
	@echo "---"
	@echo "MySQL Host          : $(MYSQL_HOST)"
	@echo "MySQL Port          : $(MYSQL_PORT)"
	@echo "MySQL DB            : $(MYSQL_DATABASE)"
	@echo "MySQL Root User     : $(MYSQL_ROOT_USER)"
	@echo "MySQL Root Password : $(MYSQL_ROOT_PASSWORD)"
	@echo "MySQL User          : $(MYSQL_USER)"
	@echo "MySQL Password      : $(MYSQL_PASSWORD)"
	@echo "MySQL Dumps         : $(shell pwd)/$(MYSQL_DUMPS_DIR)$(MYSQL_DUMPS_FILE)"
	@echo "---"
	@echo "Elasticsearch       : http://elasticsearch:9200"
	@echo "---"
	@echo "Redis Host          : redis"
	@echo "Redis Password      : "
	@echo "Redis Port          : 6379"
	@echo "---"
	@echo "App source Dir      : $(app_src_dir)"
	@echo "---"
	@echo "Documentation PHP  : $(app_php_docs)"
	@echo "Documentation API  : $(app_api_docs)"

# apidoc:
# 	@docker-compose exec -T php ./services/web/src/app/vendor/bin/apigen generate app/src --destination app/doc
# 	@make resetOwner

build:
	@echo "Building custom images..."
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
	@make start-dev-admin
	# clone the repo
	# @make clone-repo
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
	@make artisan arg="passport:install"
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
	@echo "| Compile assests for local development         |"
	@echo "+-----------------------------------------------+"
	@make npm arg="run dev"
	@make build-documentation
	@make build-documentation-api
	@echo "+-----------------------------------------------+"
	@echo "| Available commands                            |"
	@echo "+-----------------------------------------------+"
	@make help
	@echo "+-----------------------------------------------+"
	@echo "| Available Services:                           |"
	@echo "+-----------------------------------------------+"
	@make app-info

build-documentation: ## Generic PHP Documentation
	@echo "+-----------------------------------------------+"
	@echo "| Building PHP Documentation                    |"
	@echo "+-----------------------------------------------+"
	@docker pull phpdoc/phpdoc
	@docker run --rm -v $(shell pwd)/src/:/data phpdoc/phpdoc

build-documentation-api: ## API Documenation.
	@echo "+-----------------------------------------------+"
	@echo "| Building API Documentation                    |"
	@echo "+-----------------------------------------------+"
	@make artisan arg="api:generate --routePrefix='api/*'"

build-prod: ## Build production ready app.
	@echo "+-----------------------------------------------+"
	@echo "| Building production ready environment         |"
	@echo "+-----------------------------------------------+"
	@docker-compose build
	@echo "+-----------------------------------------------+"
	@echo "| Starting services                             |"
	@echo "+-----------------------------------------------+"
	@docker-compose up -d
	# clone the repo
	@echo "+-----------------------------------------------+"
	@echo "| Installing server-side dependencies           |"
	@echo "+-----------------------------------------------+"
	@make composer arg="install --no-dev --optimize-autoloader"
	@echo "+-----------------------------------------------+"
	@echo "| Installing database                           |"
	@echo "+-----------------------------------------------+"
	@make artisan arg="migrate --database=migration"
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
	@echo "| Compile assets for production                 |"
	@echo "+-----------------------------------------------+"
	@make npm arg="run production"
	@echo "Done."

cli: ## Connect to the terminal, starting all services, and (if not built) build development environment.
	@make start-dev-admin
	@docker-compose exec php bash

build-composer: ## Build the custom composer image.
	@echo "--Building Composer, Tagged: $(APP_NAME)_composer--"
	@docker build -t $(APP_NAME)_composer ./services/composer

build-node: ## Node.
	@echo "--Building Node--"
	@docker pull node:9.11.1-alpine

composer: ## Composer, for PHP.
	@docker run --rm \
		-v $(shell pwd)/src:/app \
		-v $(shell pwd)/services/composer/cache:/root/.composer \
		$(APP_NAME)_composer $(arg)

gen-certs:
	@docker run --rm \
	-v $(shell pwd)/services/web/etc/ssl:/certificates \
	-e "SERVER=$(NGINX_HOST)" jacoelho/generate-certificate

git: ## Git for vc.
	@docker run --rm ${APP_NAME}_git
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
	printf "  \033[36m%-30s\033[0m %s\n", $$1, $$NF \
	}' $(MAKEFILE_LIST)

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
		$(shell pwd)/src:/app \
		node \
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

#
# @todo rename...
# There should be a single command for;
# 	Initial build process
# 	continue working
# 	delete entire environment
#
clone-repo: ## Clone the latest repo.
	@docker run --rm \
		-v $(shell pwd):/git \
		-v ${HOME}/.ssh:/root/.ssh \
		alpine/git clone $(repo_ssh_url) $(APP_SRC_DIR)

remove-dev: ## Remove (delete) the entire app.
	@make stop-dev-admin
	# @echo "--Removing PHPDOC image--"
	# @docker image rm phpdoc/phpdoc
	@docker image rm $(APP_NAME)_composer
	@docker image rm composer
	@rm -rf services/composer/cache
	@docker image rm alpine/git
	@docker image rm node
	# @rm -Rf services/mysqldb/data
	# @rm -Rf services/elasticsearch/esdata1/*
	# @rm -Rf services/redis/data
	# @rm -Rf $(MYSQL_DUMPS_DIR)/*
	# @rm -Rf services/web/etc/nginx/default.conf
	# @rm -Rf services/web/etc/ssl/*

resetOwner: ## Reset the owner and group for /etc/ssl, and /services/web/src
	@$(shell chown -Rf $(SUDO_USER):$(shell id -g -n $(SUDO_USER)) $(MYSQL_DUMPS_DIR) "$(shell pwd)/etc/ssl" "$(shell pwd)/services/web" 2> /dev/null)

start-dev-admin: ## Start the docker services for development using multiple compose files.
	@docker-compose -f docker-compose.yml -f docker-compose.development.yml -f docker-compose.admin.yml up -d
	@make app-info

stop-dev-admin: ## Stop the docker services for development using multiple compose files.
	@docker-compose -f docker-compose.yml -f docker-compose.development.yml -f docker-compose.admin.yml down

test-with-report: ## Test the codebase and generate a code coverage report.
	@echo "Performing test..."
	@echo "and generating report..."
	@docker-compose exec -T \
		php ./vendor/bin/phpunit \
		--colors=always \
		--configuration ./
	@make resetOwner
	@echo "Report available at: $(test_report)"

test: ## Test the codebase.
	@echo "Performing test..."
	@docker-compose exec -T \
		php ./vendor/bin/phpunit