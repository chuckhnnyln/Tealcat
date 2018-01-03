#!/bin/sh
# This script processes the output of the oai download and uploads the XMl to exist
LOCALPATH="/home/ubuntu/nyh_scripts/Tealcat"

echo Starting process_oai

echo Processing XML for collections
# Remove existing local xml files
rm $LOCALPATH/output/*.xml
python $LOCALPATH/parseOAItoXML.py
echo Finished processing XML

echo Uploading processed files to eXistDB
python $LOCALPATH/uploadXML.py
echo Finished uploading

echo Ending process_oai

exit 0
