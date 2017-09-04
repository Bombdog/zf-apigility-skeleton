#!/usr/bin/env bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd ../ && pwd )
cd $DIR

echo
echo " - Clearing out cache files in $DIR/data/cache/*.php"

rm -rf data/cache/*.php

echo " - Cache files cleared"
echo
