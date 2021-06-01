#!/usr/bin/env bash

set -e

# Build files
composer install --no-dev --prefer-dist --no-suggest --no-progress
npm install
npm run package
# Make Readme
echo 'Generate readme.'
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php
# Remove files
rm -rf .git
rm -rf .github
rm -rf .gitignore
rm -rf .browserslistrc
rm -rf .editorconfig
rm -rf .eslintrc
rm -rf .gitignore
rm -rf .wp-env.json
rm -rf node_modules
rm -rf tests
rm -rf bin
rm -rf phpcs.ruleset.xml
rm -rf phpunit.xml.dist
