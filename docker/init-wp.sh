#!/bin/bash

cd /var/www/html

# Check if WordPress is already installed
if wp core is-installed --url="${SITE_URL}" --allow-root; then
  echo "WordPress is already installed."
else
  echo "Installing WordPress..."
  wp core install \
    --url="${SITE_URL}" --title="${SITE_TITLE}" \
    --admin_user="${SITE_ADMIN_USER}" \
    --admin_password="${SITE_ADMIN_PASS}" \
    --admin_email="${SITE_ADMIN_EMAIL}" \
    --skip-email --allow-root

  echo "Site URL: ${SITE_URL}"
fi
