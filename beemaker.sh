#!/usr/bin/env sh

# gets the current file folder
folder=$(dirname "$(readlink "$0")")

# run the php script
# shellcheck disable=SC2068
php "$folder"/beemaker.php $@