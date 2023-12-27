#!/bin/bash

if [ -x "$(command -v docker)" ]; then
    echo "Docker is installed"
else
    echo "Docker is not installed."
    echo "Installing Docker..."
    sudo apt-get update
    sudo apt-get install ca-certificates curl gnupg lsb-release
    sudo mkdir -p /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    sudo apt-get update
    sudo apt-get install docker-ce docker-ce-cli containerd.io docker-compose-plugin
fi

# Build and run the image
docker compose build && docker compose up -d

# Install composer deps
docker compose exec app composer install --no-dev --no-ansi --no-interaction --prefer-dist

# Remove package lock
docker compose exec app rm -rf package-lock.json

# Install node deps
docker compose exec app npm install

# Build the frontend like vue, react
#docker compose exec app npm run build

# Copy the production environment
docker compose exec app cp .env.example .env

# Give correct permission to the storage folder
docker compose exec app chmod o+w ./storage/ -R

# Migrate the database
# docker compose exec app php artisan migrate --seed --force --no-interaction

# Generate app key
docker compose exec app php artisan key:generate

# Make app ready for run

docker compose exec app php artisan app:ready

# Cache the config
docker compose exec app php artisan config:cache

# Cache the route
docker compose exec app php artisan route:cache

# Cache the view
docker compose exec app php artisan view:cache

docker compose exec app php artisan optimize:clear
