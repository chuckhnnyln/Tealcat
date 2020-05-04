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
#wget -O $1_DRUI_enrich.csv https://nyheritage.org/stats-enrich-DRUI

#Flotsam are lines we don't need...
FLOTSAM='^collection_type
^field_alpha_sort
^nyh_topic
^time_period
^terms
^\('

#Create a copy to mangle
cat $1_DRUI_raw.csv | tail -n +8 > stats.csv

#Strip lines of data we don't want / need
grep -E -v "${FLOTSAM}" stats.csv | sort -f > stats.csv.1
sed 's/\"//g' stats.csv.1 | sort -f > stats.csv.2

IFS=$'\n'
while read FILE; do
    FILELIST+=($FILE)
  done < stats.csv.2

rm stats.csv stats.csv.1 stats.csv.2
if [[ -e $1_DRUI_stats.csv ]]; then rm $1_DRUI_stats.csv; fi

#Produces "clean" stats file.
COLTOTAL=0
for index in "${!FILELIST[@]}"
	do
    #Break each entry out
    COLLECTID=${FILELIST[index]%%,*}
    COUNT=${FILELIST[index]#*,}
    #Remove FBIDs & random quotes
    COLLECTID=${COLLECTID%%\?*}
    COLLECTID=${COLLECTID%%\&*}
    #COUNT=${COUNT%\"*}
    COUNT=${COUNT//\"} #Strips quotes
    COUNT=${COUNT//,} #Strips thousands comma
    NEXTID=${FILELIST[index+1]%%,*}
    NEXTID=${NEXTID%%\?*}
    NEXTID=${NEXTID%%\&*}
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
    echo -n .
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
