#!/bin/bash
# Fix Apache PHP module configuration

echo "Disabling PHP 8.1 module..."
sudo a2dismod php8.1

echo "Enabling PHP 8.3 module..."
sudo a2enmod php8.3

echo "Restarting Apache..."
sudo systemctl restart apache2

echo "Checking Apache status..."
sudo systemctl status apache2 --no-pager

echo ""
echo "Apache should now be running with PHP 8.3"
echo "Access your site at: http://localhost/"
