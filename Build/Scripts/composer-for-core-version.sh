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
    composer install

    echo -e "🌊 Restore composer.json"
    git restore composer.json
}

update_v11() {
    echo -e "💪 Enforce TYPO3 v11"
    composer require --no-update \
        "typo3/cms-core":"^11.5"

    echo -e "💪 Enforce PHPUnit 9.x"
    composer req --dev --no-update \
        "phpunit/phpunit":"^9.6.8"
}

case "$1" in
11)
    composer_cleanup
    update_v11
    composer_update
    ;;
*)
    echo -e "🌀 Usage: ddev update-to {11}" >&2
    exit 0
    ;;
esac
