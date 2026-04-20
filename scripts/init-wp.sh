#!/usr/bin/env bash

set -euo pipefail

e_start() {
    if [[ -n "${CI:-}" ]]; then
        echo '::group::'"$@"
    else
        echo -e "> \e[1;33m$@\e[0m"
    fi
}

e_end() {
    if [[ -n "${CI:-}" ]]; then
        echo '::endgroup::'
    else
        echo ''
    fi
}

_wp() {
    if command -v wp > /dev/null 2>&1; then
        wp "$@"
    else
        vendor/bin/wp "$@"
    fi
}

e_start 'Download Core'
_wp core download --version=${WP_VERSION:-5.9}
e_end

e_start 'Configure Core'
_wp config create \
  --dbhost=${DB_HOST:-127.0.0.1:3306} --dbname=${DB_NAME:-wordpress} \
  --dbuser=${DB_USER:-sampleuser} --dbpass=${DB_PASS:-samplepass}
e_end

e_start 'Install Core'
_wp core install \
  --url="${SITE_URL:-'http://localhost'}" --title="${SITE_TITLE:-'WordPress Local'}" \
  --admin_user="${SITE_ADMIN_USER:-admin}" \
  --admin_password="${SITE_ADMIN_PASS:-secret}" \
  --admin_email="${SITE_ADMIN_EMAIL:-'admin@example.com'}" \
  --skip-email --allow-root
e_end

e_start 'Set options'
_wp option update permalink_structure "/%postname%/"
_wp option update timezone_string "${SITE_TIMEZONE:-Asia/Jakarta}"
e_end

e_start 'Install plugins'
_wp plugin install contact-form-7 --version=${CF7_VERSION:-'5.6.4'}
e_end

if _wp plugin is-active woocommerce; then
    e_start "Initializing default WooCommerce Settings..."

    _wp option update woocommerce_store_address "${WC_STORE_ADDRESS}"
    _wp option update woocommerce_store_city "${WC_STORE_CITY}"
    _wp option update woocommerce_default_country "${WC_DEFAULT_COUNTRY}"
    _wp option update woocommerce_currency "${WC_CURRENCY}"
    _wp option update woocommerce_store_postcode "${WC_STORE_POSTCODE}"

    _wp option update woocommerce_weight_unit "${WC_WEIGHT_UNIT:-kg}"
    _wp option update woocommerce_dimension_unit "${WC_DIMENSION_UNIT:-cm}"
    _wp option update woocommerce_price_thousand_sep "${WC_PRICE_THOUSAND_SEP:-.}"
    _wp option update woocommerce_price_decimal_sep "${WC_PRICE_DECIMAL_SEP:-,}"
    _wp option update woocommerce_price_num_decimals "${WC_PRICE_DECIMAL_NUM:-,}"

    # Skip the onboarding profile
    _wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json

    # Mark the task list as complete
    _wp option update woocommerce_task_list_complete yes
    e_end
fi

if [[ ${MULTISITE_ENABLED:-0} -eq 1 ]]; then
    e_start "Initializing multisite..."

    # https://developer.wordpress.org/advanced-administration/server/web-server/httpd/#multisite
    cat public/.htaccess.multisite > .htaccess
    echo 'Update .htaccess.'

    _wp core multisite-convert

    _wp plugin activate $plugins --network
    e_end
fi

e_start 'Verify'
_wp core version --extra
e_end
