.DEFAULT_GOAL := help

include .makeenv
export $(shell sed 's/=.*//' .makeenv)

ifeq (run,$(firstword $(MAKECMDGOALS)))
  # use the rest as arguments for "run"
  RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  # ...and turn them into do-nothing targets
  $(eval $(RUN_ARGS):;@:)
endif

help: ## This help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

up: ## Up application
	@echo Start docker-compose
	@mkdir -p docker/var/redis
	@mkdir -p docker/var/postgres
	docker-compose -f docker-compose.yml up -d --force-recreate --build
	@echo

down: ## Down application
	@echo Stop docker-compose ;\
	docker-compose -f docker-compose.yml down
	@echo

nginx: ## Shell of nginx container
	docker-compose -f docker-compose.yml exec nginx /bin/sh

php-fpm: ## Shell of php-fpm container
	docker-compose -f docker-compose.yml exec php-fpm /bin/bash

postgres: ## Shell of postgresql container
	docker-compose -f docker-compose.yml exec postgres /bin/sh

redis: ## Shell of redis container
	docker-compose -f docker-compose.yml exec redis /bin/sh

ps: ## Status containers
	@docker-compose -f docker-compose.yml ps

log: ## Container output logs
	@docker-compose -f docker-compose.yml logs --follow --tail 1

default: help

