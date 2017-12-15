#!/bin/sh
# This script processes the output of the oai download and uploads the XMl to exist

echo Starting process_oai

echo Processing XML for collections
# Remove existing local xml files
cd output
rm *.xml
cd ..
python ./parseOAItoXML.py
echo Finished processing XML

echo Uploading processed files to eXistDB
python ./uploadXML.py
echo Finished uploading

echo Ending process_oai
