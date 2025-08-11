#!/usr/bin/env bash

set -e


# Set variables.
PREFIX="refs/tags/"
VERSION=${1#"$PREFIX"}

# Change version string.
sed -i.bak "s/Version: .*/Version: ${VERSION}/g" ./style.css
sed -i.bak "s/^Stable Tag: .*/Stable Tag: ${VERSION}/g" ./readme.txt
