.PHONY: qa
qa: cs tests infection phpstan rector-dry

.PHONY: cs
cs:
	vendor/bin/ecs --fix

.PHONY: infection
infection:
	vendor/bin/infection --min-msi=96

.PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse

.PHONY: rector
rector:
	vendor/bin/rector

.PHONY: rector-dry
rector-dry:
	vendor/bin/rector --dry-run

.PHONY: tests
tests:
	vendor/bin/phpunit --configuration=phpunit.xml.dist
