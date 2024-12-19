#!/usr/bin/env bash

# @todo This script is crap. It must fall and properly implemented in Build/Scripts/runTests.sh along with
#       adding two ddev core instances decoupled from the root composer.json.

composer_cleanup() {
    echo -e "💥 Cleanup folders"
    rm -Rf \
        .cache/phpstan/* \
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
        "typo3/minimal":"^12.4"
    echo -e "🔥 Update to selected dependencies"
    php -dxdebug.mode=off $(which composer) install
    php -dxdebug.mode=off $(which composer) remove --no-update \
        "typo3/minimal"
}

update_v13() {
    echo -e "💪 Enforce TYPO3 v13"
    php -dxdebug.mode=off $(which composer) require --dev --no-update \
        "phpunit/phpunit":"^10.5"
    php -dxdebug.mode=off $(which composer) require --no-update \
        "typo3/minimal":"^13.4"
    echo -e "🔥 Update to selected dependencies"
    php -dxdebug.mode=off $(which composer) install
    php -dxdebug.mode=off $(which composer) remove --no-update \
        "typo3/minimal"
}

case "$1" in
12)
    composer_cleanup
    update_v12
    ;;
13)
    composer_cleanup
    update_v13
    ;;
*)
    echo -e "🌀 Usage: ddev update-to (12)" >&2
    exit 0
    ;;
esac
