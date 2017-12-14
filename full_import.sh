#!/bin/sh
# This file runs all of the scripts required to pull from ContentDM and update the NYH exist database

echo Starting job
echo Harvesting OAI from ContentDM
# Remove the existing output.xml. This aids in failure checking
rm output.xml
python ./oaiharvest.py
echo Finished OAI harvest

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

echo Ending job
