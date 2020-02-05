# use tabs, not spaces

SHELL = /bin/bash

CURRENT_FILENAME := $(lastword $(MAKEFILE_LIST))
SELF_DIR := $(dir $(lastword $(MAKEFILE_LIST)))
include $(SELF_DIR)versions.mk
export $(shell sed 's/=.*//' $(SELF_DIR)versions.mk)

include $(SELF_DIR)config.mk

.PHONY: help
help:
	@echo "You can use one of these commands:";
	@$(MAKE) -s list

.PHONY: project-new
project-new:
	@$(MAKE) -s check-requirements
	@sed -i '' 's/PROJECT_SLUG/$(PROJECT_SLUG)/g' ./docker/nginx/conf/default.conf
	@sed -i '' 's/PROJECT_DOMAIN_DEV/$(PROJECT_DOMAIN_DEV)/g' ./docker/nginx/conf/default.conf
	@sed -i '' 's/PROJECT_DOMAIN_PROD/$(PROJECT_DOMAIN_PROD)/g' ./docker/nginx/conf/default.conf

	@sed -i '' 's/PROJECT_SLUG/$(PROJECT_SLUG)/g' ./project/pm2.json

	@sed -i '' 's/PROJECT_SLUG/$(PROJECT_SLUG)/g' ./versions.mk

	@sed -i '' 's/PROJECT_SLUG/$(PROJECT_SLUG)/g' ./docker-compose.yml
	@sed -i '' 's/PROJECT_DOMAIN_DEV/$(PROJECT_DOMAIN_DEV)/g' ./docker-compose.yml
	@sed -i '' 's/PROJECT_DOMAIN_PROD/$(PROJECT_DOMAIN_PROD)/g' ./docker-compose.yml

	@sed -i '' 's/PROJECT_SLUG/$(PROJECT_SLUG)/g' ./docker-compose-prod.yml
	@sed -i '' 's/PROJECT_DOMAIN_DEV/$(PROJECT_DOMAIN_DEV)/g' ./docker-compose-prod.yml
	@sed -i '' 's/PROJECT_DOMAIN_PROD/$(PROJECT_DOMAIN_PROD)/g' ./docker-compose-prod.yml

	@mv ./docker/nginx/conf/default.conf ./docker/nginx/conf/$(PROJECT_SLUG).conf

	@cp .env.dist .env

	@rm -rf .git

.PHONY: toolset
toolset:
	@$(MAKE) -s check-requirements
	@$(MAKE) -s network-create
	@docker run -v $$(pwd)/project:/var/www/html -w="/var/www/html" -e USER_ID="$$(id -u)" -e GROUP_ID="$$(id -g)" \
		--env SSH_AUTH_SOCK=/ssh-agent --rm --network $(NETWORK) -ti $(DOCKER_IMAGE_TOOLSET)

.PHONY: up-dev
up-dev:
	@$(MAKE) -s check-requirements
	@$(MAKE) -s network-create
	@$(shell cat $(SELF_DIR)versions.mk) docker-compose up -d

.PHONY: down-dev
down-dev:
	@$(MAKE) -s check-requirements
	@$(shell cat $(SELF_DIR)versions.mk) docker-compose down

.PHONY: up-prod
up-prod:
	@$(MAKE) -s check-requirements
	@$(MAKE) -s network-create
	@$(shell cat $(SELF_DIR)versions.mk) docker-compose -f docker-compose-prod.yml up -d

.PHONY: down-prod
down-prod:
	@$(MAKE) -s check-requirements
	@$(shell cat $(SELF_DIR)versions.mk) docker-compose -f docker-compose-prod.yml down

.PHONY: network-create
network-create:
	@$(MAKE) -s check-requirements
	@docker network ls|grep $(NETWORK) > /dev/null || docker network create --driver bridge $(NETWORK)

.PHONY: network-delete
network-delete:
	@$(MAKE) -s check-requirements
	@docker network rm $(NETWORK)

# build
@$(shell docker-compose -f docker-compose.yml config --services | sed 's/^/build-/'):
	@$(shell cat $(SELF_DIR)versions.mk) docker-compose build $(shell echo $@ | sed -e s/build-//g )
	@$(shell sed 's/=.*/=latest/' $(SELF_DIR)versions.mk) docker-compose -f docker-compose.yml \
		build $(shell echo $@ | sed -e s/build-//g )

# push
@$(shell docker-compose -f docker-compose.yml config --services | sed 's/^/push-/'):
	@$(shell cat $(SELF_DIR)versions.mk) docker-compose push $(shell echo $@ | sed -e s/push-//g )
	@$(shell sed 's/=.*/=latest/' $(SELF_DIR)versions.mk) docker-compose -f docker-compose.yml \
		push $(shell echo $@ | sed -e s/push-//g )

.PHONY: build-all
build-all:
	@$(MAKE) -s check-requirements
	@$(shell cat $(SELF_DIR)versions.mk) docker-compose build
	@$(shell sed 's/=.*/=latest/' $(SELF_DIR)versions.mk) docker-compose build

.PHONY: push-all
push-all:
	@$(MAKE) -s check-requirements
	@$(shell cat $(SELF_DIR)versions.mk) docker-compose push
	@$(shell sed 's/=.*/=latest/' $(SELF_DIR)versions.mk) docker-compose push


.PHONY: build-and-push-all
build-and-push-all:
	@$(MAKE) -s build-all
	@$(MAKE) -s push-all

.PHONY: check-requirements
check-requirements:
	@[ "${PROJECT_SLUG}" ] || ( echo ">> PROJECT_SLUG is not set. Edit config.mk"; exit 1 )
	@[ "${PROJECT_DOMAIN_DEV}" ] || ( echo ">> PROJECT_DOMAIN_DEV is not set. Edit config.mk"; exit 1 )
	@[ "${PROJECT_DOMAIN_PROD}" ] || ( echo ">> PROJECT_DOMAIN_PROD is not set. Edit config.mk"; exit 1 )

.PHONY: list
list:
	@$(MAKE) -pRrq -f $(lastword $(CURRENT_FILENAME)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# \
		Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'
