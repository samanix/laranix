#!/usr/bin/env sh
LARANIX=${TRAVIS_BUILD_DIR}
APP_ROOT=${HOME}/laranix

cd ${HOME}

composer self-update

# Get test repo
git clone --depth=50 --branch=${TRAVIS_BRANCH} https://github.com/laranix/tests.git laranix

cd ${APP_ROOT}
cp .env.travis .env
composer update --prefer-dist --no-interaction --prefer-stable --no-suggest

rm -fr ${APP_ROOT}vendor/samanix/laranix/

ln -s ${LARANIX} ${APP_ROOT}/laranix

composer update --prefer-dist --no-interaction --prefer-stable --no-suggest

mysql -e 'CREATE DATABASE travis_test; set global max_connections = 1001;'

php artisan key:generate
