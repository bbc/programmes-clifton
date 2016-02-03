#!/bin/sh

while php app/console alfred:pips -vv; do
   echo "."
done
