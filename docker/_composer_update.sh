#!/usr/bin/env bash

docker run --rm --interactive --tty \
  --volume /Users/madmaker/Projects/madwww/src/:/app \
  composer update
