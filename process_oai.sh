#!/bin/sh
# This script processes the output of the oai download and uploads the XMl to exist

echo Starting process_oai

echo Processing XML for collections
# Remove existing local xml files
rm /home/ubuntu/output/*.xml
python /home/ubuntu/parseOAItoXML.py
echo Finished processing XML

echo Uploading processed files to eXistDB
python /home/ubuntu/output/uploadXML.py
echo Finished uploading

echo Ending process_oai
