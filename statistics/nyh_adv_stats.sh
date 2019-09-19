#/bin/bash
# NY Heritage Enrichment production
# Chuck Henry September 2019
# Requires fresh stats-enrich.csv and GA csv

testout () {
  echo $ORGID
  echo $ORGNAME
  echo $COUNCIL
  echo $EXTENTVAL
}

cleanup() {
  echo "Cleaning up last run."
  rm stage*.csv
  rm alias_master.csv
  rm ga_ready.csv
  #rm stats-enrich-usable.csv
}

cleanup

echo "Creating dependent files."
#Setting the environment
if [ ! -f stats-enrich.csv ]; then exit 1; fi
if [ ! -f "Analytics All Web Site Data New Statistics with Chuck"* ]; then exit 1; fi
if [ -f stats-enrich-usable.csv ]; then rm stats-enrich-usable.csv; fi
unset $FILELIST

# Parse stats-enrich into usable format
cat stats-enrich.csv | tail -n +2 > stage1.csv

#Remove unneeded crap from GA stats file
cat "Analytics All Web Site Data New Statistics with Chuck"* | tail -n +8 > ga_ready.csv

#Create AliasID master list
IFS=$'\n'
while read FILE; do
    FILELIST+=($FILE)
  done < stage1.csv

for ITEM in "${FILELIST[@]}"
	do
    ALIASID=`echo $ITEM | cut -d "^" -f1`
    echo $ALIASID >> stage2.csv
	done
sort stage2.csv > stage3.csv
uniq stage3.csv > alias_master.csv

##Make the final usable enrichment csv

echo "Creating usable enrichment file."
#Read AliasID master list
IFS=$'\n'
while read FILE; do
    ALIASLIST+=($FILE)
  done < alias_master.csv

#Read stats-enrich.csv
IFS=$'\n'
while read FILE; do
    STATSENRICH+=($FILE)
  done < stage1.csv

#Create the usable format file
for ENTRY in "${ALIASLIST[@]}"
  do
    TOTALEXT=0
    TEMPARRAY=()
    for LINE in "${STATSENRICH[@]}"
      do
        ORGID=`echo $LINE | cut -d "^" -f1`
        if [[ "$ENTRY" == "$ORGID" ]]
          then
            REALORGID=$ORGID
            REALORGNAME=`echo $LINE | cut -d "^" -f2`
            REALCOUNCIL=`echo $LINE | cut -d "^" -f3`
            EXTENT=`echo $LINE | cut -d "^" -f4`
            EXTENTVAL=${EXTENT% *}
            EXTENTVAL=${EXTENTVAL#\"}
            if [[ "$EXTENTVAL" == "" ]]; then $EXTENTVAL=0
            TOTALEXT=$(expr $TOTALEXT + $EXTENTVAL)
          else
            TEMPARRAY+=($LINE)
        fi
      done
    STATSENRICH=("${TEMPARRAY[@]}")
    unset TEMPARRAY
    echo $REALORGID"^"$REALORGNAME"^"$REALCOUNCIL"^"$TOTALEXT >> stats-enrich-usable.csv
  done

exit 0
