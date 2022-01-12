#/bin/bash

# $1 = YYYYMM
# $2 = Stats file from Google

showme () {
  echo "Council: "$council
  echo "InstitutionID: "$orgid
  echo "InstitutionName: "$orgname
  echo "Path: "$path
  echo "Outcomes: "$outcome
  echo "Pageviews: "$pageviews
  echo ""
}

mv $2 $1_DRUI_raw.csv
wget -O $1_DRUI_enrich_raw.csv https://nyheritage.org/stats-enrich-DRUI

cat $1_DRUI_enrich_raw.csv | tail -n +1 > $1_DRUI_enrich.csv
rm $1_DRUI_enrich_raw.csv

#Flotsam are lines we don't need...
FLOTSAM='^collection_type|^field_alpha_sort|^nyh_topic|^time_period|^terms|^\('

#Create a copy to mangle
cat $1_DRUI_raw.csv | tail -n +8 > stats.csv

#Strip lines of data we don't want / need
sed 's/\"//g' stats.csv | sort -f > stats.csv.1
grep -E -v "${FLOTSAM}" stats.csv.1 | sort -f > stats.csv.2

IFS=$'\n'
while read FILE; do
    FILELIST+=($FILE)
  done < stats.csv.2
unset IFS

rm stats.csv stats.csv.1 stats.csv.2
if [[ -e $1_DRUI_stats.csv ]]; then rm $1_DRUI_stats.csv; fi

#Cleans each line
for index in "${!FILELIST[@]}"
	do
    #Break each entry out
    COUNT=${FILELIST[index]#*,}
    COUNT=${COUNT//\"} #Strips quotes
    COUNT=${COUNT//,} #Strips thousands comma
    #Remove FBIDs & random quotes & convert spaces to dashes
    COLLECTID=${FILELIST[index]%%,*}
    COLLECTID=${COLLECTID%%\?*}
    COLLECTID=${COLLECTID%%\&*}
    COLLECTID=${COLLECTID// /-}
    UFILELIST+=($COLLECTID","$COUNT)
  done
#Sorts the list
IFS=$'\n'
SFILELIST=($(sort <<<"${UFILELIST[*]}"))
unset IFS

#Add together matching orgs
COLTOTAL=0
for index in "${!SFILELIST[@]}"
  do
    COLLECTID=${SFILELIST[index]%%,*}
    COUNT=${SFILELIST[index]#*,}
    NEXTID=${SFILELIST[index+1]%%,*}
    COLTOTAL=$((COLTOTAL + COUNT))
    if [[ $NEXTID != $COLLECTID ]]
      then
        echo $COLLECTID","$COLTOTAL >> $1_DRUI_stats.csv
        COLTOTAL=0
    fi
done

IFS=$'\n'
while read LINE; do
    ENRICH+=($LINE)
  done < $1_DRUI_enrich.csv
unset IFS

#Produces Stats file by going through all orgs and adding data from stats file.
echo "\"Council\",\"InstitutionID\",\"InstitutionName\",\"Path\",\"Pageviews\"" > $1_DRUI_combined.csv
for org in "${ENRICH[@]}"
  do
    council=`echo $org | cut -d "~" -f 1`
    orgid=`echo $org | cut -d "~" -f 2`
    orgname=`echo $org | cut -d "~" -f 3`
    path=`echo $org | cut -d "~" -f 4`
    path=`echo $path | tr -d '\r'`
    path=${path##*/}
    path=${path#\"}
    path=${path%\"*}
    outcome=`cat $1_DRUI_stats.csv | grep ^$path[,]`
    echo -n "."
    if [[ $outcome != "" ]]
    then
      pageviews=`echo $outcome | cut -d "," -f 2`
      pageviews=${pageviews%,*}
    else
      pageviews=0
    fi
    #showme
    echo $council","$orgid","$orgname",\""$path"\","$pageviews >> $1_DRUI_combined.csv
done

exit 0
