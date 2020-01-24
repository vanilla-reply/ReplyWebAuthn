#!/bin/sh

phpcs --standard=PSR2 --extensions=php --warning-severity=0 .
rm .gitignore
composer install --no-dev --no-interaction --no-suggest --ignore-platform-reqs
phpunit --testdox
composer archive --dir build/ --format=zip
