#!/usr/bin/env bash

subcommand="$1"
shift

case $subcommand in
    update)
        COMPOSER=composer-dev.json composer update
        ;;
    install)
        COMPOSER=composer-dev.json composer install
        ;;
    *)
        echo "ABORT!! NO command"
        ;;
esac

