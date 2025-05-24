# Runnings tests
From the project root:

## Install
1. composer install

## Run the unit tests
1. vendor/bin/phpunit -c phpunit.xml.dist

## Run the functional tests
1. Start up your gearman job server
 1. For local testing, this docker command can be used: ` docker run --name gearmand --rm -d -p 4730:4730 artefactual/gearmand:latest`
1. Update the `NET_GEARMAN_TEST_SERVER` constant in `phpunit.xml.functional-dist` (if not on localhost and/or port 4730)
1. vendor/bin/phpunit -c phpunit.xml.functional-dist
