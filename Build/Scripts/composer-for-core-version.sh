#!/usr/bin/env bash

composer_cleanup() {
    echo -e "💥 Cleanup folders"
    rm -Rf \
        .Build/vendor/* \
        .Build/var/* \
        .Build/bin/* \
        .Build/Web/typo3conf/ext/* \
        .Build/Web/typo3/* \
        .Build/Web/typo3temp/* \
        composer.lock
}

composer_update() {
    echo -e "🔥 Update to selected dependencies"
    php -dxdebug.mode=off $(which composer) install

    echo -e "🌊 Restore composer.json"
    git restore composer.json
}

update_v12() {
    echo -e "💪 Enforce TYPO3 v12"
    php -dxdebug.mode=off $(which composer) require --dev --no-update \
        "phpunit/phpunit":"^10.5"
    php -dxdebug.mode=off $(which composer) require --no-update \
        "typo3/cms-core":"^12.4.2"
}

case "$1" in
12)
    composer_cleanup
    update_v12
    composer_update
    ;;
*)
    echo -e "🌀 Usage: ddev update-to (12)" >&2
    exit 0
    ;;
esac
