#!/usr/bin/env bash

set -e


# Set variables.
PREFIX="refs/tags/"
VERSION=${1#"$PREFIX"}


# Build files
composer install --no-dev --prefer-dist --no-suggest --no-progress
npm install
npm run package

# Make Readme
echo 'Generate readme.'
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php

# Change version string.
sed -i.bak "s/Version: .*/Version: ${VERSION}/g" ./style.css
sed -i.bak "s/^Stable Tag: .*/Stable Tag: ${VERSION}/g" ./readme.txt
