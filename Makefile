.DEFAULT_GOAL := help

#################################
# Configuration
#################################

PROJECT = events
APP = app
WEB = web
DB = db
RABBITMQ = rabbitmq
DB_NAME = events
DOCKER = docker
DOCKER_BUILD = $(DOCKER) build -t
COMPOSE = docker-compose -p $(PROJECT) -f docker-compose.yml
RUN = $(COMPOSE) run --rm

NETWORK = local

#################################
# Targets
#################################

.PHONY: build
build: ## Prepare containers
	@$(DOCKER_BUILD) php-fpm containers/php-fpm
	@$(DOCKER_BUILD) nginx containers/nginx

.PHONY: start
start: network up install ## Start containers & install application

.PHONY: up
up: ## Builds, (re)creates, starts containers
	@$(COMPOSE) up -d

.PHONY: install
install: ready ## Install application
	@$(COMPOSE) exec $(DB) /usr/local/src/init.sh
	@$(RUN) --user www-data $(APP) /var/www/app/bin/install

.PHONY: exec
exec: ## Open a shell in the container (options: user=www-data, cmd=bash, cont=app)
	$(eval cont ?= $(APP))
	$(eval user ?= www-data)
	$(eval cmd ?= bash)
	@$(COMPOSE) exec --user $(user) $(cont) $(cmd)

.PHONY: ps
ps: ## List containers status
	@$(COMPOSE) ps

.PHONY: logs
logs: ## Dump containers logs (option: cont=app])
	@$(COMPOSE) logs -f $(cont)

.PHONY: pgsql
pgsql: ## Run pgsql cli
	@$(COMPOSE) exec $(DB) psql $(DB_NAME) -U events

.PHONY: test
test: phpunit ## Run all tests

.PHONY: phpunit
phpunit: ## Run phpunit test suite
	@$(COMPOSE) run --rm -e DEBUG=$(DEBUG) $(APP) vendor/bin/phpunit

.PHONY: run
run: ## Execute a command in a container (options: user=www-data, cont=app, cmd="pwd"])
	$(eval user ?= www-data)
	$(eval cont ?= $(APP))
ifndef cmd
	@echo "To use the 'run' target, you MUST add the 'cmd' argument"
	exit 1
endif
	@$(RUN) --user $(user) $(cont) $(cmd)

.PHONY: stop
stop: ## Stop containers
	@$(COMPOSE) stop

.PHONY: destroy
destroy: stop ## Stop and remove containers
	@$(COMPOSE) rm -f

.PHONY: ready
ready: ## Check if environment is ready
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(APP):9000 ddn0/wait 2> /dev/null
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(WEB):80 ddn0/wait 2> /dev/null
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(DB):5432 ddn0/wait 2> /dev/null
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(RABBITMQ):5672 ddn0/wait 2> /dev/null

.PHONY: clear
clear: ## Clear cache & logs
	rm -rf var/cache/* var/logs/*

.PHONY: reset
reset: destroy clear ## Reset application
	rm -rf vendor/ var/bootstrap.php.cache app/config/parameters.yml

.PHONY: network
network:
	@$(DOCKER) network create $(NETWORK) 2> /dev/null || true

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'
