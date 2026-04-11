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
fi

echo "Initializing default Plugins..."
for plugin in ${SITE_PLUGINS//,/ }; do
    if wp plugin is-installed "$plugin" --url="${SITE_URL}"; then
        echo " - $plugin is already installed."
        continue
    fi

    wp plugin install "$plugin" --url="${SITE_URL}" --activate
done

echo "Initializing default Themes..."
for theme in ${SITE_THEMES//,/ }; do
    if wp theme is-installed "$theme" --url="${SITE_URL}"; then
        echo " - $theme is already installed."
        continue
    fi

    wp theme install "$theme" --url="${SITE_URL}"
done

wp theme activate ${SITE_DEFAULT_THEME} --url="${SITE_URL}"

echo "Site URL: ${SITE_URL}"
