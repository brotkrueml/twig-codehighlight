.PHONY: qa
qa: cs tests phpstan rector-dry

.PHONY: cs
cs:
	vendor/bin/ecs --fix

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
