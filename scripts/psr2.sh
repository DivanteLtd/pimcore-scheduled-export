#!/bin/bash

vendor/bin/phpcs --config-set colors 1
vendor/bin/phpcs --extensions=php \
    --standard=./vendor/divante-ltd/pimcore-coding-standards/Standards/Pimcore5/ruleset.xml \
    ./src  -s
