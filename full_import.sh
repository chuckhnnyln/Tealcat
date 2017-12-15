#!/bin/sh
# This file runs all of the scripts required to pull from ContentDM and update the NYH exist database

echo Starting job
echo Harvesting OAI from ContentDM
# Remove the existing output.xml. This aids in failure checking
rm output.xml
python ./oaiharvest.py
echo Finished OAI harvest

echo Processing XML for collections
./process_oai.sh

echo Ending job
