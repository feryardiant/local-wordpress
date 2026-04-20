#!/bin/bash

set -euo pipefail
shopt -s nullglob

for pkg_dir in packages/*/; do
    pkg_dir="${pkg_dir%/}"
    pkg="${pkg_dir##*/}"

    ./vendor/bin/wp i18n make-pot "$pkg_dir" "$pkg_dir/languages/$pkg.pot"
done
