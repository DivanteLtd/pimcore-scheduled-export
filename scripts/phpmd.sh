#!/bin/bash

vendor/bin/phpmd src text \
    ./vendor/divante-ltd/pimcore-coding-standards/Standards/Pimcore5/rulesetmd.xml
