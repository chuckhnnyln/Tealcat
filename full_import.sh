#!/bin/sh
# This file runs all of the scripts required to pull from ContentDM and update the NYH exist database
LOCALPATH="/home/ubuntu/nyh_scripts/Tealcat"

echo Starting job
echo Harvesting OAI from ContentDM
# Remove the existing output.xml. This aids in failure checking
rm $LOCALPATH/output.xml
echo Deleted previous output
python $LOCALPATH/oaiharvest.py
echo Finished OAI harvest

echo Processing XML for collections
$LOCALPATH/process_oai.sh

echo Ending job

exit 0
