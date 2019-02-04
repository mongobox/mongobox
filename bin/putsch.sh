#!/bin/bash

cd /var/www/mongobox
php app/console jukebox:putsch:clean --env=prod --debug
