#!/bin/bash

cd /var/www/mongobox
php app/console tumblr:import https://www.mongobox.fr --env=prod --debug
 
