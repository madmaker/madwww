#!/usr/bin/env bash

docker run --rm --interactive --tty \
  --volume /www/src/:/app \
  composer install
