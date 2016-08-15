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
SELENIUM = selenium
CHROME = chromenode
FIREFOX = firefoxnode

DOCKER = docker
DOCKER_BUILD = $(DOCKER) build -t

NETWORK = local
DEBUG = $(debug)

COMPOSE = docker-compose -p $(PROJECT) $(CONFIG)
RUN = $(COMPOSE) run --rm -e DEBUG=$(DEBUG)

# Print output
# For colors, see https://en.wikipedia.org/wiki/ANSI_escape_code#Colors
INTERACTIVE := $(shell tput colors 2> /dev/null)
COLOR_UP = 3
COLOR_INSTALL = 6
COLOR_READY = 5
COLOR_STOP = 1
PRINT_CLASSIC = cat
PRINT_PRETTY = sed 's/^/$(shell printf "\033[3$(2)m[%-7s]\033[0m " $(1))/'
PRINT_PRETTY_NO_COLORS = sed 's/^/$(shell printf "[%-7s] " $(1))/'
PRINT = PRINT_CLASSIC

#################################
# Profiles
#################################

CONFIG_STANDALONE = -f docker-compose.yml
CONFIG_TEST = $(CONFIG_STANDALONE) -f docker/docker-compose.test.yml

# Default
CONFIG = $(CONFIG_TEST)

.PHONY: standalone
standalone: ## Use "standalone" profile
	$(eval CONFIG = $(CONFIG_STANDALONE))
	@true

.PHONY: test
test: ## Use "test" profile
	$(eval CONFIG = $(CONFIG_TEST))
	@true

#################################
# Targets
#################################

.PHONY: behat
behat: ## Run behat test suite (options: path=mypath)
ifndef path
	$(eval pth = )
else
	$(eval pth = features/$(path))
endif
	@$(RUN) $(APP) vendor/bin/behat $(pth)

.PHONY: build
build: ## Prepare containers
	@$(DOCKER_BUILD) php-fpm docker/php-fpm
	@$(DOCKER_BUILD) nginx docker/nginx

.PHONY: clear
clear: ## Clear cache & logs
	@$(RUN) $(APP) rm -rf var/cache/* var/logs/*

.PHONY: destroy
destroy: stop ## Stop and remove containers
	@$(COMPOSE) rm --all -f $(app) 2>&1 | $(call $(PRINT),REMOVE,$(COLOR_INSTALL))

.PHONY: exec
exec: ## Open a shell in the container (options: user=www-data, cmd=bash, cont=app)
	$(eval cont ?= $(APP))
	$(eval user ?= www-data)
	$(eval cmd ?= bash)
	@$(COMPOSE) exec --user $(user) $(cont) $(cmd)

.PHONY: install
install: ready ## Install application
	@$(COMPOSE) exec $(DB) /usr/local/src/init.sh | $(call $(PRINT),INSTALL,$(COLOR_INSTALL))
	@$(RUN) --user www-data $(APP) bin/install | $(call $(PRINT),INSTALL,$(COLOR_INSTALL))

.PHONY: logs
logs: ## Dump containers logs (option: cont=app])
	@$(COMPOSE) logs -f $(cont)

.PHONY: network
network:
	@$(DOCKER) network create $(NETWORK) 2> /dev/null || true

.PHONY: pgsql
pgsql: ## Run pgsql cli
	@$(COMPOSE) exec $(DB) psql $(DB_NAME) -U events

.PHONY: phpunit
phpunit: ## Run phpunit test suite (options: filter=myfilter)
ifndef filter
	$(eval flt = )
else
	$(eval flt = --filter $(filter))
endif
	@$(RUN) $(APP) vendor/bin/phpunit $(flt)

.PHONY: ps
ps: ## List containers status
	@$(COMPOSE) ps

.PHONY: ready
ready: pretty ## Check if environment is ready
	@echo "[READY]" | $(call $(PRINT),READY,$(COLOR_READY))
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(APP):9000 ddn0/wait 2> /dev/null
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(WEB):80 ddn0/wait 2> /dev/null
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(DB):5432 ddn0/wait 2> /dev/null
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(RABBITMQ):5672 ddn0/wait 2> /dev/null

.PHONY: readytest
readytest: ready ## Check if environment is ready
	@echo "[READY] test" | $(call $(PRINT),READY,$(COLOR_READY))
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(SELENIUM):4444 ddn0/wait 2> /dev/null
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(CHROME):5900 ddn0/wait 2> /dev/null
	@docker run --rm --net=$(NETWORK) -e TIMEOUT=30 -e TARGETS=$(FIREFOX):5900 ddn0/wait 2> /dev/null

.PHONY: reset
reset: clear destroy ## Reset application
	rm -rf vendor/ var/bootstrap.php.cache app/config/parameters.yml

.PHONY: run
run: ## Execute a command in a container (options: user=www-data, cont=app, cmd="pwd")
	$(eval user ?= www-data)
	$(eval cont ?= $(APP))
ifndef cmd
	@echo "To use the 'run' target, you MUST add the 'cmd' argument"
	exit 1
endif
	@$(RUN) --user $(user) $(cont) $(cmd)

.PHONY: start
start: pretty network up install ## Start containers & install application

.PHONY: stop
stop: pretty ## Stop containers
	@$(COMPOSE) stop $(app) 2>&1 | $(call $(PRINT),STOP,$(COLOR_INSTALL))

.PHONY: test
test: readytest phpunit behat ## Run all tests

.PHONY: up
up: ## Builds, (re)creates, starts containers
	@$(COMPOSE) up -d --remove-orphans $(app) 2>&1 | $(call $(PRINT),UP,$(COLOR_UP))

.PHONY: pretty
pretty:
ifdef INTERACTIVE
	$(eval PRINT = PRINT_PRETTY)
else
	$(eval PRINT = PRINT_PRETTY_NO_COLORS)
endif
	@true

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'
