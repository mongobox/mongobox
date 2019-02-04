#!/usr/bin/env bash

cd /var/www/mongobox
php app/console core:rss:import --debug --env=prod
