#!/usr/bin/env bash

set -euo pipefail

. "$(dirname "$0")/_util.sh"

declare -A plugins_map

                  #                 Plugin  Woo     Blocksy
                  # CF7     JetPack Check   Comm.   Comp.
plugins_map['5.9']='5.6.4	13.6.1	none	8.1.4	2.1.38'
plugins_map['6.0']='5.7.7	13.6.1	none	8.4.0	2.1.38'
plugins_map['6.1']='5.7.7	13.6.1	none	8.4.0	2.1.38'
plugins_map['6.2']='5.8.7	13.6.1	1.0.0	8.4.0	2.1.38'
plugins_map['6.3']='5.9.1	13.6.1	1.0.0	9.2.3	2.1.38'
plugins_map['6.4']='5.9.1	13.6.1	1.9.0	9.2.3	2.1.38'
plugins_map['6.5']='5.9.1	14.0.0	1.9.0	10.0.3	2.1.38'
plugins_map['6.6']='6.0.0	14.5.0	1.9.0	10.0.3	2.1.38'
plugins_map['6.7']='6.1.0	15.4.0	1.9.0	10.6.2	2.1.38'
plugins_map['6.8']='6.1.0	15.7.1	1.9.0	10.6.2	2.1.38'
plugins_map['6.9']='6.2.0	15.7.1	1.9.0	10.6.2	2.1.38'
plugins_map['7.0']='6.2.0	15.7.1	1.9.0	10.6.2	2.1.38'

if [[ -f "$PWD/.env" ]]; then
    . "$PWD/.env"
fi

WP_VERSION=${WP_VERSION:-'5.9'}
# Reduce to major.minor for map lookup
wp_version_key=$(echo "${WP_VERSION}" | awk -F. '{printf "%s.%s", $1, $2}')
wp_plugins=(${plugins_map[${wp_version_key}]//\t/ })

declare -A plugin_supports

# ContactForm7
plugin_supports['contact-form-7']="${wp_plugins[0]:-6.2.0}"
# JetPack
plugin_supports['jetpack']="${wp_plugins[1]:-15.7.1}"
# PluginCheck
plugin_supports['plugin-check']="${wp_plugins[2]:-1.9.0}"
# WooCommerce
plugin_supports['woocommerce']="${wp_plugins[3]:-10.6.2}"
# BlocksyCompanion
plugin_supports['blocksy-companion']="${wp_plugins[4]:-2.1.38}"

ASSET_DIR=${ASSET_DIR:-"$PWD/assets"}
INSTALL_DIR=${INSTALL_DIR:-"$PWD/docker/volumes/wordpress"}

SITE_URL=${SITE_URL:-'http://localhost'}

if [[ ${WP_RESET:-0} -eq 1 ]]; then
    e_start "Reset WordPress Core"
    rm -rf "$INSTALL_DIR"
    e_end
fi

if [[ ! -d "${INSTALL_DIR}" ]]; then
    e_start 'Download WordPress Core'
    _wp core download --version=${WP_VERSION}
    e_end
fi

if [[ ! -f "${INSTALL_DIR}/wp-config.php" ]]; then
    e_start 'Configure WordPress Core'
    _wp config create \
        --dbhost=${DB_HOST:-127.0.0.1:3306} --dbname=${DB_NAME:-wordpress} \
        --dbuser=${DB_USER:-sampleuser} --dbpass=${DB_PASS:-samplepass}
    e_end
fi

if _wp core is-installed --url="${SITE_URL}" --allow-root; then
  echo "WordPress is already installed."
else
    e_start 'Install WordPress Core'
    _wp core install \
        --url="${SITE_URL}" --title="${SITE_TITLE:-'WordPress Local'}" \
        --admin_user=${SITE_ADMIN_USER:-admin} \
        --admin_password=${SITE_ADMIN_PASS:-secret} \
        --admin_email=${SITE_ADMIN_EMAIL:-'admin@example.com'} \
        --skip-email --allow-root
    e_end

    e_start 'Set up default options'
    _wp option update permalink_structure "/%postname%/"
    _wp option update timezone_string "${SITE_TIMEZONE:-Asia/Jakarta}"
    e_end

    if [[ ! -f "$INSTALL_DIR/favicon.ico" ]]; then
        cp "$ASSET_DIR/favicon.ico" "$INSTALL_DIR/favicon.ico"
    fi
fi

installed_plugins=''

if [[ -n "${SITE_PLUGINS:-}" ]]; then
    e_start 'Set up default Plugins'
    SITE_PLUGINS=${SITE_PLUGINS:-''}
    plugins=''

    for plugin in ${SITE_PLUGINS//,/ }; do
        if _wp plugin is-installed "$plugin"; then
            echo " - $plugin is already installed."
            continue
        fi

        if [[ -n "${plugin_supports[$plugin]:-}" ]]; then
            _wp plugin install "$plugin" --version=${plugin_supports[$plugin]} --activate

            installed_plugins="$installed_plugins $plugin"
            continue
        fi

        plugins="$plugins $plugin"
    done

    if [[ -n "$plugins" ]]; then
        _wp plugin install $plugins --activate

        installed_plugins="$installed_plugins $plugins"
    fi
    e_end
fi

if _wp plugin is-active woocommerce; then
    e_start "Set up WooCommerce"
    _wp option update woocommerce_store_address "${WC_STORE_ADDRESS:-'Jl. Example No. 123'}"
    _wp option update woocommerce_store_city "${WC_STORE_CITY:-'Batang'}"
    _wp option update woocommerce_default_country "${WC_DEFAULT_COUNTRY:-'ID:JT'}"
    _wp option update woocommerce_currency "${WC_CURRENCY:-'IDR'}"
    _wp option update woocommerce_store_postcode "${WC_STORE_POSTCODE:-'12345'}"

    _wp option update woocommerce_weight_unit "${WC_WEIGHT_UNIT:-kg}"
    _wp option update woocommerce_dimension_unit "${WC_DIMENSION_UNIT:-cm}"
    _wp option update woocommerce_price_thousand_sep "${WC_PRICE_THOUSAND_SEP:-.}"
    _wp option update woocommerce_price_decimal_sep "${WC_PRICE_DECIMAL_SEP:-,}"
    _wp option update woocommerce_price_num_decimals "${WC_PRICE_DECIMAL_NUM:-0}"

    # Skip the onboarding profile
    _wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json

    # Mark the task list as complete
    _wp option update woocommerce_task_list_complete yes
    e_end
fi

if [[ ${MULTISITE_ENABLED:-0} -eq 1 ]]; then
    e_start "Set up MultiSite"

    # https://developer.wordpress.org/advanced-administration/server/web-server/httpd/#multisite
    cat "$ASSET_DIR/.htaccess.multisite" > "$INSTALL_DIR/.htaccess"
    echo 'Update .htaccess.'

    _wp core multisite-convert

    if [[ -n "$installed_plugins" ]]; then
        _wp plugin activate $installed_plugins --network
    fi
    e_end
fi

if [[ -n "${SITE_THEMES:-}" ]]; then
    e_start 'Set up default themes'
    themes=""

    for theme in ${SITE_THEMES//,/ }; do
        if _wp theme is-installed "$theme"; then
            echo " - $theme is already installed."
            continue
        fi

        themes="$themes $theme"
    done

    if [[ -n "$themes" ]]; then
        _wp theme install $themes
    fi

    SITE_DEFAULT_THEME=${SITE_DEFAULT_THEME:-}

    if [[ -n "$SITE_DEFAULT_THEME" ]] && _wp theme is-installed "$SITE_DEFAULT_THEME"; then
        _wp theme activate $SITE_DEFAULT_THEME
    fi
    e_end
fi

e_start 'Cleanup'
if _wp plugin is-installed hello; then
    _wp plugin uninstall hello
fi
e_end

e_start 'Verify Installation'
_wp core version --extra
echo "Site URL: ${SITE_URL}"
e_end
