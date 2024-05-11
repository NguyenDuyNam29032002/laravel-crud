#!/usr/bin/env bash

# Load environment variables from .env file
set -o allexport
source .env
set +o allexport

until mysql -h db -uroot -p1234 -e 'SELECT 1'; do
  echo "MySQL is unavailable - sleeping"
  sleep 3
done

# create database if database is not exists
mysql -h db -uroot -p1234 -e "CREATE DATABASE IF NOT EXISTS ${DB_DATABASE}"

# add permission to user
mysql -h db -uroot -p1234 -e "GRANT ALL ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}'"

# run migration
php artisan migrate --force


#not working ;-;
