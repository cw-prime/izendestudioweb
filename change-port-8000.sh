#!/bin/bash
# Script to change Apache port from 80 to 8000

echo "Changing Apache port to 8000..."

# Update ports.conf
sudo sed -i 's/Listen 80/Listen 8000/' /etc/apache2/ports.conf

# Update virtual host configuration
sudo sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8000>/' /etc/apache2/sites-enabled/000-default.conf

# Restart Apache
sudo systemctl restart apache2

echo "Done! Apache is now running on port 8000"
echo "Access your site at: http://localhost:8000/izendestudioweb/"
