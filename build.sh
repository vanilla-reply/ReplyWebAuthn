#!/bin/sh

composer install --no-dev --no-interaction --no-suggest
composer archive --dir build/ --format=zip
