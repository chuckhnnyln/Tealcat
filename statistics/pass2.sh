#/bin/bash

#Create a copy to mangle
cat $1 | tail -n +8 > stats.csv
sort stats.csv > stats.csv.2

#Strip lines of data we don't want / need
grep -v '^collection_type' stats.csv.2 > stats.csv.3
grep -v '^field_alpha_sort' stats.csv.3 > stats.csv.4
grep -v '^nyh_topic' stats.csv.4 > stats.csv.5
grep -v '^page\/' stats.csv.5 > stats.csv.6
grep -v '^time_period' stats.csv.6 > stats.csv.7
grep -v '^terms' stats.csv.7 > stats.csv.8
grep -v '^\"' stats.csv.8 > stats.csv.9
grep -v '^(not set)' stats.csv.9 > stats.csv.10
mv stats.csv{.10,}
rm stats.csv.*

IFS=$'\n'
while read FILE; do
    FILELIST+=($FILE)
  done < stats.csv

rm stats.csv

#Produces "clean" stats file.
for ITEM in "${FILELIST[@]}"
	do
    #Break each entry out
    COLLECTID=${ITEM%%,*}
    COUNT=${ITEM##*,}
    #Remove FBIDs
    COLLECTID=${COLLECTID%%\?*}
    COLLECTID=${COLLECTID%%\&*}
    #Remove random quotes
    COUNT=${COUNT%\"*}
    echo $COLLECTID","$COUNT >> stats.csv
  done



exit 0





#Read the org & collections replacement list in.
if [ ! -f stats-first.csv ]; then exit 1; fi
IFS=$'\n'
while read FILE; do
    FILELIST+=($FILE)
  done < stats-first.csv


for ITEM in "${FILELIST[@]}"
	do
    ALIAS=${ITEM##*/}
    WRONG=${ALIAS%^*}
    RIGHT=${ALIAS#*^}
    sed -i "" "s|$WRONG|$RIGHT|g" "stats.csv"
	done

exit 0
