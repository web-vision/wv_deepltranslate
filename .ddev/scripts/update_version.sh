#!/usr/bin/env bash

composer_cleanup() {
    echo "Reset dev requirements"
    composer remove --dev \
        typo3/cms-extensionmanager \
        typo3/cms-frontend \
        b13/container \
        helhum/typo3-console \
        nimut/testing-framework \
        saschaegerer/phpstan-typo3 \
        typo3/cms-belog \
        typo3/cms-fluid-styled-content \
        typo3/cms-filelist \
        typo3/cms-info \
        typo3/cms-lowlevel \
        typo3/cms-tstemplate \
        typo3/cms-workspaces \
        typo3fluid/fluid --no-update

    echo "Reset Requires from vX"
    composer remove \
        typo3/cms-backend \
        typo3/cms-core \
        typo3/cms-fluid \
        typo3/cms-install \
        typo3/cms-extbase \
        typo3/cms-fluid -W

    rm composer.lock
    rm -rf .Build/vendor/*
    rm -rf .Build/var/*
    rm -rf .Build/bin/*
    rm -rf .Build/Web/typo3conf/ext/*
    rm -rf .Build/Web/typo3/*
    rm -rf .Build/Web/typo3temp/*
}

composer_update() {
    echo "Update Instance"
    composer install

    echo "restore composer.json"
    git restore composer.json
}

composer_require_default_dev() {
    echo "################################"
    echo "Require default dev-dependencies"
    echo "################################"
    echo ""

    composer req \
        b13/container:^1.6 \
        saschaegerer/phpstan-typo3:^1.0 \
        nimut/testing-framework:^6.0 --no-update
}

update_v9() {
    echo "###############################"
    echo "Update TYPO3 dependency to v9"
    echo "###############################"
    echo ""

    echo "Add Requires for v9"
    composer req \
        typo3/cms-core:^9.5 \
        typo3/cms-backend:^9.5 \
        typo3/cms-install:^9.5 \
        typo3/cms-extbase:^9.5 \
        typo3/cms-fluid:^9.5 --no-update

    composer_require_default_dev

    echo "Add Dev-Requires for v9"
    composer req \
        helhum/typo3-console:^5.8 \
        typo3/cms-belog:^9.5 \
        typo3/cms-fluid-styled-content:^9.5 \
        typo3/cms-filelist:^9.5 \
        typo3/cms-info:^9.5 \
        typo3/cms-lowlevel:^9.5 \
        typo3/cms-tstemplate:^9.5 \
        typo3/cms-workspaces:^9.5 \
        typo3/cms-frontend:^9.5 \
        typo3/cms-extensionmanager:^9.5 --dev -W --no-scripts
}

update_v10() {
    echo "Add Requires for v10"
    composer req \
        typo3/cms-core:^10.4 \
        typo3/cms-backend:^10.4 \
        typo3/cms-install:^10.4 \
        typo3/cms-extbase:^10.4 \
        typo3/cms-fluid:^10.4 --no-update

    composer_require_default_dev

    echo "Add Dev-Requires for v10"
    composer req \
        helhum/typo3-console:^6.7 \
        typo3/cms-belog:^10.4 \
        typo3/cms-fluid-styled-content:^10.4 \
        typo3/cms-filelist:^10.4 \
        typo3/cms-info:^10.4 \
        typo3/cms-lowlevel:^10.4 \
        typo3/cms-tstemplate:^10.4 \
        typo3/cms-workspaces:^10.4 \
        typo3/cms-frontend:^10.4 \
        typo3/cms-extensionmanager:^10.4 --dev -W --no-scripts
}

update_v11() {
    echo "Add Dev-Requires for v11"
    composer req \
        typo3/cms-core:^11.5 \
        typo3/cms-backend:^11.5 \
        typo3/cms-install:^11.5 \
        typo3/cms-extbase:^11.5 \
        typo3/cms-fluid:^11.5 --no-update

    composer_require_default_dev

    echo "Add Dev-Requires for v11"
    composer req \
        helhum/typo3-console:^7.1 \
        typo3/cms-belog:^11.5 \
        typo3/cms-fluid-styled-content:^11.5 \
        typo3/cms-filelist:^11.5 \
        typo3/cms-info:^11.5 \
        typo3/cms-lowlevel:^11.5 \
        typo3/cms-tstemplate:^11.5 \
        typo3/cms-workspaces:^11.5 \
        typo3/cms-frontend:^11.5 \
        typo3/cms-extensionmanager:^11.5 --dev -W --no-scripts
}

case "$1" in
9)
    composer_cleanup
    update_v9
    composer_update
    ;;
10)
    composer_cleanup
    update_v10
    composer_update
    ;;
11)
    composer_cleanup
    update_v11
    composer_update
    ;;
*)
    echo "Usage: ddev update-to {9|10|11}" >&2
    exit 0
    ;;
esac
