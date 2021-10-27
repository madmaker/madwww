#!/usr/bin/env bash

docker run --rm --interactive --tty \
  --volume /www/madwww/src/:/app \
  composer update
