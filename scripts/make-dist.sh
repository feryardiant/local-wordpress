#!/bin/bash

set -euo pipefail
shopt -s nullglob

for pkg_dir in packages/*/; do
    pkg_dir="${pkg_dir%/}"
    pkg="${pkg_dir##*/}"

    if [ ! -f "$pkg_dir/.distignore" ]; then
        echo -e "\e[1;33mNotice:\e[0m No .distignore found for $pkg, skipping"
        continue
    fi

    composer -d "$pkg_dir" install

    rm public/dist/$pkg.*.zip

    ./vendor/bin/wp dist-archive "$pkg_dir" public/dist --force --create-target-dir
done
