
# Project Name Here 

## Start new project

### Open config.mk and add the name to variables:
````
PROJECT_SLUG=
PROJECT_DOMAIN_DEV=
PROJECT_DOMAIN_PROD=
NETWORK=PROJECT_SLUG-net
# DELETE ONE OF THESE
DOCKER_IMAGE_TOOLSET=adclick/proj.PROJECT_SLUG:${VERSION_TOOLSET_PHP73}-adc-base-toolset-php73
DOCKER_IMAGE_TOOLSET=adclick/proj.PROJECT_SLUG:${VERSION_TOOLSET_NODEPM2}-adc-base-toolset-node-pm2
````

### Prepare new project

````
make project-new
````


## Makefile

### See available commands
````
$ make
You can use one of these commands:
build-PROJECT_SLUG-cli
build-PROJECT_SLUG-db
build-PROJECT_SLUG-nginx
build-PROJECT_SLUG-node-pm2
build-PROJECT_SLUG-toolset-node-pm2
build-PROJECT_SLUG-toolset-php
build-all
build-and-push-all
check-requirements
down-dev
down-prod
help
network-create
network-delete
project-new
push-PROJECT_SLUG-cli
push-PROJECT_SLUG-db
push-PROJECT_SLUG-nginx
push-PROJECT_SLUG-node-pm2
push-PROJECT_SLUG-toolset-node-pm2
push-PROJECT_SLUG-toolset-php
push-all
toolset
up-dev
up-prod
````
