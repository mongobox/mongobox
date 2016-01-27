#!/bin/bash

cd /var/www/mongobox
php app/console tumblr:import http://mongobox.fr
