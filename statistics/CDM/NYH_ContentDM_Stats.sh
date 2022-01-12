#/bin/bash

# $1 = YYYYMM
# $2 = Stats file from Google

hunt () {
  local target=$1
  outcome=0
  for search in "${STATS[@]}"
    do
      local collectionid=`echo $search | cut -d "," -f 1`
      local pageviews=`echo $search | cut -d "," -f 2`
      target="${target#\"}"
      target="${target%\"*}"
      if [ "$target" = "$collectionid" ]
        then
          outcome=$pageviews
          break
      fi
  done
}

showme () {
  echo "Council: "$council
  echo "OrgID: "$orgid
  echo "Org Name: "$orgname
  echo "CollectID: "$collectionid
}

mv $2 $1_CDM_raw.csv
wget -O $1_CDM_enrich_raw.csv https://nyheritage.org/stats-enrich-CDM

cat $1_CDM_enrich_raw.csv | tail -n +1 > $1_CDM_enrich.csv
rm $1_CDM_enrich_raw.csv

#Flotsam are lines we don't need...
FLOTSAM='^collection_type
^field_alpha_sort
^nyh_topic
^page\/
^time_period
^terms
^\(
^\"
^&'

#Create a copy to mangle
cat $1_CDM_raw.csv | tail -n +8 > stats.csv

#Strip lines of data we don't want / need
grep -E -v "${FLOTSAM}" stats.csv | sort -f > stats.csv.1

IFS=$'\n'
while read FILE; do
    FILELIST+=($FILE)
  done < stats.csv.1

rm stats.csv stats.csv.1

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
        echo $COLLECTID","$COLTOTAL >> $1_CDM_stats.csv
        COLTOTAL=0
    fi
  done

IFS=$'\n'
while read LINE; do
    ENRICH+=($LINE)
  done < $1_CDM_enrich.csv

IFS=$'\n'
while read LINE; do
    STATS+=($LINE)
  done < $1_CDM_stats.csv

#Produces Stats file by going through all orgs and adding data from stats file.
echo "\"Council\",\"InstitutionID\",\"InstitutionName\",\"CollectionAlias\",\"Pageviews\"" > $1_CDM_combined.csv
for org in "${ENRICH[@]}"
  do
    council=`echo $org | cut -d "~" -f 1`
    orgid=`echo $org | cut -d "~" -f 2`
    orgname=`echo $org | cut -d "~" -f 3`
    collectionid=`echo $org | cut -d "~" -f 4`
    collectionid=`echo $collectionid | tr -d '\r'`
    #Detect Orgs with multiple collections.
    char="!"
    collcount=`echo ${collectionid} | awk -F"${char}" '{print NF}'`
    #showme
    for ((l=0;l<$collcount;l+=1))
      do
        prey=${collectionid%%!*}
        collectionid=${collectionid#*!}
        hunt $prey
        prey="${prey#\"}"
        prey="${prey%\"*}"
        echo -n "."
        echo $council","$orgid","$orgname",\""$prey"\","$outcome >> $1_CDM_combined.csv
    done
done

exit 0
