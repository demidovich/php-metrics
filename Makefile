.DEFAULT_GOAL := help

help: ## This help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

up: ## Up containers
	@docker-compose -f docker-compose.yml up -d --force-recreate --build

down: ## Down containers
	@docker-compose -f docker-compose.yml down

ps: ## Status of containers
	@docker-compose -f docker-compose.yml ps

log: ## Output log of containers
	@docker-compose -f docker-compose.yml logs --follow --tail 1

php: ## Shell of php container
	@docker-compose -f docker-compose.yml exec php /bin/bash

redis: ## Shell of redis container
	@docker-compose -f docker-compose.yml exec redis /bin/sh