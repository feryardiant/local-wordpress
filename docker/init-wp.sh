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

echo "Initializing default Options..."
wp option update permalink_structure "/%postname%/"
wp option update timezone_string "${SITE_TIMEZONE}"

if [[ ! -f ./favicon.ico ]]; then
  cp /var/www/public/favicon.ico .
fi

echo "Initializing default Plugins..."
for plugin in ${SITE_PLUGINS//,/ }; do
    if wp plugin is-installed "$plugin"; then
        echo " - $plugin is already installed."
        continue
    fi

    wp plugin install "$plugin" --activate
done

echo "Initializing default Themes..."
for theme in ${SITE_THEMES//,/ }; do
    if wp theme is-installed "$theme"; then
        echo " - $theme is already installed."
        continue
    fi

    wp theme install "$theme"
done

wp theme activate ${SITE_DEFAULT_THEME}

if [[ ${MULTISITE_ENABLED} -eq 1 ]]; then
    echo "Initializing multisite..."

    # https://developer.wordpress.org/advanced-administration/server/web-server/httpd/#multisite
    cat /var/www/public/.htaccess.multisite > .htaccess
    echo 'Update .htaccess.'

    wp core multisite-convert
fi

echo "Cleanup..."

wp plugin uninstall hello

wp theme uninstall twentytwentythree twentytwentyfour

echo "Site URL: ${SITE_URL}"
