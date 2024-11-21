#!/bin/sh
# you should switch to postgre user to run this script successfully
# postg_hemis_06-05-2020_23_01.bak.tar
SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")

cd $SCRIPTPATH || exit

# Keep rotate backed up files for each 5 days
OUTPUT="$(ls -t pos*_*0\-*\-*_*.bak.tar | head -1)"
if [ -f $OUTPUT ]; then
     FILE_BACK="rtt_$(echo $OUTPUT | cut -c7-33)tar"
     mv "${OUTPUT}"  "${FILE_BACK}"
fi

OUTPUT="$(ls -t pos*_*5\-*\-*_*.bak.tar | head -1)"
if [ -f $OUTPUT ]; then
     FILE_BACK="rtt_$(echo $OUTPUT | cut -c7-33)tar"
     mv "${OUTPUT}"  "${FILE_BACK}"
fi

THE_DATE=$(date +"%d-%m-%Y_%H_%M")
pg_dump -F t "$1" > postg_hemis_${THE_DATE}.bak.tar
find ./*.bak.* -mtime +60 -exec rm {} \;

