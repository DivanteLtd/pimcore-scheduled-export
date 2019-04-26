#!/bin/bash

set -eu

PROJECT_DIR="$( cd "$(dirname "$0")" ; pwd -P )/../tmp"
DEPENDENCIES="$( cd "$(dirname "$0")" ; pwd -P )/dependencies.txt"

PACKAGE_NAME="ScheduledExportBundle"
BUNDLE_NAME="DivanteScheduledExportBundle"

DB_HOST=${DB_HOST-localhost}
DB_PORT=${DB_PORT-3306}
DB_USERNAME=${DB_USERNAME-root}
DB_PASSWORD=${DB_PASSWORD-root}
DB_DATABASE=${DB_DATABASE-pimcore_test}

echo -e "\e[34m=> Start installing project \e[0m"

echo -e "\e[32m=> Clean old project files \e[0m"
rm -rf $PROJECT_DIR

echo -e "\e[32m=> Cloning Pimcore Skeleton \e[0m"
git clone https://github.com/pimcore/skeleton.git $PROJECT_DIR

echo -e "\e[32m=> Copy package to project \e[0m"
cp -r src/$PACKAGE_NAME $PROJECT_DIR/src/
cp -r tests $PROJECT_DIR/tests/
cp -r phpunit.xml $PROJECT_DIR/phpunit.xml
cp -r composer.json $PROJECT_DIR/composer.local.json
cp -r scripts/ $PROJECT_DIR/scripts
cp -r scripts/config_test.yml $PROJECT_DIR/app/config/config_test.yml

cd $PROJECT_DIR

echo -e "\e[32m=> Install dependencies \e[0m"
COMPOSER_DISCARD_CHANGES=true COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --optimize-autoloader

echo -e "\e[32m=> Create Database \e[0m"

if test -z "$DB_PASSWORD"
then
    mysql --host=$DB_HOST --port=$DB_PORT --user=$DB_USERNAME \
        -e "DROP DATABASE IF EXISTS $DB_DATABASE; CREATE DATABASE $DB_DATABASE CHARSET=utf8mb4;"
else
    mysql --host=$DB_HOST --port=$DB_PORT --user=$DB_USERNAME --password=$DB_PASSWORD \
        -e "DROP DATABASE IF EXISTS $DB_DATABASE; CREATE DATABASE $DB_DATABASE CHARSET=utf8mb4;"
fi

echo -e "\e[32m=> Install Pimcore \e[0m"
if test -z "$DB_PASSWORD"
then
    vendor/bin/pimcore-install \
        --ignore-existing-config \
        --admin-username admin \
        --admin-password admin \
        --mysql-host-socket $DB_HOST \
        --mysql-database $DB_DATABASE \
        --mysql-username $DB_USERNAME \
        --mysql-port $DB_PORT \
        --no-debug \
        --no-interaction
else
    vendor/bin/pimcore-install \
        --ignore-existing-config \
        --admin-username admin \
        --admin-password admin \
        --mysql-host-socket $DB_HOST \
        --mysql-database $DB_DATABASE \
        --mysql-username $DB_USERNAME \
        --mysql-password $DB_PASSWORD \
        --mysql-port $DB_PORT \
        --no-debug \
        --no-interaction
fi

echo -e "\e[32m=> Enable Bundles \e[0m"
while read -r line; do
    bin/console pimcore:bundle:enable $line -n
done < $DEPENDENCIES

bin/console pimcore:bundle:enable $BUNDLE_NAME -n

echo -e "\e[32m=> Done! \e[0m"
