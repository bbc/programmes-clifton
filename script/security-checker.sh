#!/usr/bin/env bash

SCRIPTPATH=$( cd $(dirname $0) ; pwd -P )
FILE="${SCRIPTPATH}/../composer.lock"

curl -H "Accept: text/plain" https://security.symfony.com/check_lock -F lock=@$FILE
echo "" # new line
