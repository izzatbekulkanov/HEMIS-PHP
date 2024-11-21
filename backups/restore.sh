#!/bin/sh
# you should switch to postgres user to run this script successfully
SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")

cd $SCRIPTPATH || exit

if [ -z "$2" ]
  then
    OUTPUT="$(ls -t *.tar | head -1)"
  else
    OUTPUT=$1
fi

if [ -f $OUTPUT ]; then
    echo "Restoring db from file ${OUTPUT}"
    pg_restore -d "$1" "${OUTPUT}" -c
fi
