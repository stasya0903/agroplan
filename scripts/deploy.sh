#!/bin/bash

# Directories for releases and the current version
DEPLOY_PATH="/var/www/agroplan/releases"
CURRENT_PATH="/var/www/agroplan/current"
PREVIOUS_PATH="/var/www/agroplan/previous"
NGINX_CONFIG_PATH="/var/www/agroplan/agroplan.conf"
NGINX_ENABLED_PATH="/etc/nginx/sites-enabled"
NGINX_AVAILABLE_PATH="/etc/nginx/sites-available"

# Get the current timestamp for versioning
TIMESTAMP=$(date +'%d-%m-%Y_%H:%M:%S')

# Create a new release directory
NEW_RELEASE_PATH="$DEPLOY_PATH/$TIMESTAMP"
sudo mkdir -p $NEW_RELEASE_PATH

# Clone the project into the new release directory
git clone git@github.com:stasya0902/agroplan.git $NEW_RELEASE_PATH

# If there is an existing current release, save it as the previous version
if [ -L "$CURRENT_PATH" ]; then
    PREVIOUS_RELEASE=$(readlink -f $CURRENT_PATH)
    rm -f $PREVIOUS_PATH
    ln -s $PREVIOUS_RELEASE $PREVIOUS_PATH
fi

# Remove the old symbolic link and create a new one for the current version
sudo rm -f $CURRENT_PATH
sudo ln -s $NEW_RELEASE_PATH $CURRENT_PATH

# Update the Nginx configuration
if [ -L "$NGINX_ENABLED_PATH" ]; then
    sudo rm -f $NGINX_ENABLED_PATH
fi

if [ -L "$NGINX_AVAILABLE_PATH" ]; then
    sudo rm -f $NGINX_AVAILABLE_PATH
fi
sudo ln -s $NGINX_CONFIG_PATH $NGINX_ENABLED_PATH
sudo ln -s $NGINX_CONFIG_PATH $NGINX_AVAILABLE_PATH

# Clean up old releases, keeping only the latest 5
RELEASES_COUNT=5
cd $DEPLOY_PATH
RELEASES=$(ls -dt */ | tail -n +$(($RELEASES_COUNT + 1)))

if [ -n "$RELEASES" ]; then
  echo "Deleting old releases..."
  for RELEASE in $RELEASES; do
    rm -rf "$DEPLOY_PATH/$RELEASE"
  done
else
  echo "No old releases to delete."
fi

echo "Deployment complete! New release is now active."
