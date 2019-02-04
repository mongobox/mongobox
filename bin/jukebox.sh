#!/bin/bash

## Update and clean videos
cd /var/www/mongobox
php app/console jukebox:updateDataYoutube --debug --env=prod
