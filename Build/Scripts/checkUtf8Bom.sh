#!/usr/bin/env bash

#########################
#
# Check all UTF-8 files do not contain BOM.
#
# It expects to be run from the core root.
#
##########################

FILES=`find . -type f \
    ! -path "./.Build/*" \
    ! -path "./.git/*" \
    ! -path "./.php_cs.cache" \
    ! -path "./.php-cs-fixer.cache" \
    ! -path "./Documentation-GENERATED-temp/*" \
    -print0 | xargs -0 -n1 -P8 file {} | grep 'UTF-8 Unicode (with BOM)'`

if [ -n "${FILES}" ]; then
    echo "Found UTF-8 files with BOM:";
    echo ${FILES};
    exit 1;
fi

exit 0
