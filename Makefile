DOCKER_RUN       := docker run --rm
COMPOSER_IMAGE   := -v "$$(pwd):/app" --user $$(id -u):$$(id -g) composer
NODE_IMAGE       := -w /home/node/app -v "$$(pwd):/home/node/app" --user node atlascontentmodeler_node_image
HAS_CHROMEDRIVER := $(shell command -v chromedriver 2> /dev/null)
CURRENTUSER      := $$(id -u)
CURRENTGROUP     := $$(id -g)

.PHONY: build
build:  ## Builds all plugin assets
	@echo "Setting up Content Modeler plugin"
	$(MAKE) install-composer
	$(MAKE) build-docker
	$(MAKE) install-npm
	$(MAKE) build-npm

.PHONY: build-docker
build-docker: build-docker-node build-docker-phpunit

.PHONY: build-docker-node
build-docker-node:
	if [ ! "$$(docker images | grep atlascontentmodeler_node_image)" ]; then \
		echo "Building the Node image"; \
		docker build \
			-f .docker/Dockerfile-node \
			--build-arg UID=$(CURRENTUSER) \
			--build-arg GID=$(CURRENTUSER) \
			-t atlascontentmodeler_node_image .; \
	fi

.PHONY: build-docker-phpunit
build-docker-phpunit:
	if [ ! "$$(docker images | grep atlascontentmodeler_phpunit_image)" ]; then \
		echo "Building the PhpUnit image"; \
		docker build \
			-f .docker/Dockerfile-phpunit \
			-t atlascontentmodeler_phpunit_image .; \
	fi

.PHONY: build-npm
build-npm: | install-npm
	@echo "Building plugin assets"
	$(DOCKER_RUN) $(NODE_IMAGE) npm run build

.PHONY: clean-docker
clean-docker:
	if [ "$$(docker images | grep atlascontentmodeler_phpunit_image)" ]; then \
		docker rmi $$(docker images --format '{{.Repository}}:{{.Tag}}' | grep 'atlascontentmodeler_phpunit_image'); \
	fi
	if [ "$$(docker images | grep atlastcontentmodeler_node_image)" ]; then \
		docker rmi $$(docker images --format '{{.Repository}}:{{.Tag}}' | grep 'atlastcontentmodeler_node_image'); \
	fi

.PHONY: clean-e2e
clean-e2e:
	@echo "Cleaning leftovers from end-to-end tests"
	find tests/_output/ -type f -not -name '.gitignore' -delete
	rm -f .env.testing;
	if [ "$$(docker ps | grep atlas-content-modeler_wordpress)" ]; then \
		docker-compose -f ./docker-compose.yml down; \
	fi

.PHONY: help
help:  ## Display help
	@awk -F ':|##' \
		'/^[^\t].+?:.*?##/ {\
			printf "\033[36m%-30s\033[0m %s\n", $$1, $$NF \
		}' $(MAKEFILE_LIST) | sort

.PHONY: install-composer
install-composer:
	if [ ! -d ./vendor/ ]; then \
		echo "installing composer dependencies for plugin"; \
		$(DOCKER_RUN) $(COMPOSER_IMAGE) install --ignore-platform-reqs; \
	fi

.PHONY: install-npm
install-npm: | build-docker
	if [ ! -d ./node_modules/ ]; then \
		echo "installing node dependencies for plugin"; \
		$(DOCKER_RUN) $(NODE_IMAGE) npm install; \
	fi

.PHONY: test
test: install-npm install-composer test-js-lint test-php-lint test-js-jest test-php-unit ## Build all assets and run all testing except end-to-end testing

.PHONY: test-build
test-build: build test-js-lint test-php-lint test-js-jest test-php-unit ## Run all testing except end-to-end testing

.PHONY: test-all
test-all: install-npm install-composer test-js-lint test-php-lint test-js-jest test-php-unit test-e2e ## Run all testing

.PHONY: test-all-build
test-all-build: build test-js-lint test-php-lint test-js-jest test-php-unit test-e2e ## Build all assets and run all testing

.PHONE: test-e2e
test-e2e: | clean-e2e ## Run end-2-end testing (requires Chrome and Chromedriver)
ifdef HAS_CHROMEDRIVER
	@echo "Running End-to-end tests"
	cp .env.testing.sample .env.testing
	docker-compose -f ./docker-compose.yml up -d --build
	sleep 10; \
	docker-compose -f ./docker-compose.yml exec --workdir=/var/www/html/ --user=www-data wordpress wp plugin install wp-graphql --activate
	docker-compose -f ./docker-compose.yml exec --workdir=/var/www/html/ --user=www-data wordpress wp plugin activate atlas-content-modeler
	docker-compose -f ./docker-compose.yml exec --workdir=/var/www/html/wp-content/plugins/atlas-content-modeler --user=www-data wordpress wp db export tests/_data/dump.sql
	if [ -z "$(TEST)" ]; then \
		vendor/bin/codecept -vvv run acceptance; \
	else \
		vendor/bin/codecept -vvv run acceptance $(TEST); \
	fi
	$(MAKE) clean-e2e
else
	@echo "Chromedriver is not available. Please see the readme for installation instructions."
endif

.PHONY: test-js
test-js: test-js-lint test-js-jest ## Run all JavaScript testing

.PHONY: test-js-jest
test-js-jest: | install-npm  ## Run Jest tests
	$(DOCKER_RUN) \
		-w /app \
		$(NODE_IMAGE) \
		npm run test-no-watch

.PHONY: test-js-lint
test-js-lint: | install-npm ## Run JavaScript linting
	$(DOCKER_RUN) \
		-w /app \
		$(NODE_IMAGE) \
		npm run lint

.PHONY: test-lint
test-lint: test-js-lint test-php-lint ## Run both JavaScript and PHP linting

.PHONY: test-php
test-php: test-php-lint test-php-unit ## Run all PHP tests

.PHONY: test-php-lint
test-php-lint: | install-composer ## Run linting only on PHP code
	$(DOCKER_RUN) \
		-w /app \
		-v "$$(pwd):/app" \
		devwithlando/php:7.4-fpm-2 \
		bash -c "\
		composer lint \
		"

.PHONY: test-php-unit
test-php-unit: | install-composer build-docker-phpunit ## Run PHPunit tests
	if [ "$$(docker ps | grep atlas-content-modeler_docker_phpunitdatabase_1)" ]; then \
		docker-compose -f ./docker-compose-phpunit.yml down; \
	fi
	docker-compose -f ./docker-compose-phpunit.yml up -d
	if [ -z "$(TEST)" ]; then \
		docker-compose \
			-f ./docker-compose-phpunit.yml \
			exec \
			-w /app \
			phpunit \
			bash -c "composer test"; \
	else \
		docker-compose \
			-f ./docker-compose-phpunit.yml \
			exec \
			-w /app \
			phpunit \
			bash -c "vendor/bin/phpunit $(TEST)"; \
	fi
	docker-compose -f ./docker-compose-phpunit.yml down
