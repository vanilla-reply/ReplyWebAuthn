#!/bin/sh

rm .gitignore
composer install --no-dev --no-interaction --no-suggest --ignore-platform-reqs
composer archive --dir build/ --format=zip
