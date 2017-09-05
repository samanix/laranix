#!/usr/bin/env sh
LARANIX=${TRAVIS_BUILD_DIR}
APP_BUILD=${HOME}/laranix

cd ${HOME}

composer self-update

# Get test repo
git clone --depth=50 --branch=2.0 https://github.com/laranix/tests.git laranix

cd ${APP_BUILD}
cp .env.travis .env
composer update --prefer-dist --no-interaction --prefer-stable --no-suggest

rm -fr ${APP_BUILD}vendor/samanix/laranix/

ln -s ${LARANIX} ${APP_BUILD}/laranix

composer update --prefer-dist --no-interaction --prefer-stable --no-suggest

mysql -e 'CREATE DATABASE travis_test; set global max_connections = 1001;'
