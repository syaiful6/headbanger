.PHONY: tests fixcs

tests:
	phpunit

fixcs:
	php-cs-fixer fix .
