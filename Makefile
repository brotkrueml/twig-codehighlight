.PHONY: $(filter-out vendor,$(MAKECMDGOALS))

help:
	@printf "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m\n"
	@grep -E '^[-a-zA-Z0-9_\.\/]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-15s\033[0m %s\n", $$1, $$2}'

qa: comp lint stan rector-check tests cs ## Run all relevant code checks

comp: comp-check comp-norm ## Validate and normalize composer.json

comp-check: ## Validate composer.json
	composer validate

comp-norm: vendor ## Normalize composer.json
	composer normalize

cs: cs-php ## Check and fix coding standards

cs-check: cs-php-check ## Only check coding standards

cs-php: vendor ## Check and fix PHP coding standards
	vendor/bin/ecs check --fix

cs-php-check: vendor ## Only check PHP coding standards
	vendor/bin/ecs check

lint: lint-php

lint-php: ## Lint PHP files
	find . -type f -name '*.php' ! -path "./vendor/*" -print0 | xargs -0 -n1 -P4 php -l -n | (! grep -v "No syntax errors detected" )

rector: vendor ## Apply rector rules
	vendor/bin/rector

rector-check: vendor ## Only check against rector rules
	vendor/bin/rector --dry-run

stan: vendor ## Run static analysis
	vendor/bin/phpstan analyse

tests: tests-php-unit tests-php-mutation ## Run all tests

tests-php-mutation: vendor ## Run PHP unit tests
	vendor/bin/infection --min-msi=92

tests-php-unit: vendor ## Run PHP unit tests
	vendor/bin/phpunit --configuration=phpunit.xml.dist

vendor: composer.json $(wildcard composer.lock) ## Install PHP dependencies
	composer install
