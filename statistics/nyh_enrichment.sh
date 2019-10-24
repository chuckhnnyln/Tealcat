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
  #rm stats-enrich-usable.csv
}

cleanup

#wget -O stats-enrich.csv https://nyheritage.org/stats-enrich

echo "Creating dependent files."
#Setting the environment
if [ ! -f stats-enrich.csv ]; then exit 1; fi
if [ -f stats-enrich-usable.csv ]; then rm stats-enrich-usable.csv; fi
unset $FILELIST

# Parse stats-enrich into usable format
cat stats-enrich.csv | tail -n +2 > stage1.csv

#Create AliasID master list
IFS=$'\n'
while read FILE; do
    FILELIST+=($FILE)
  done < stage1.csv

for ITEM in "${FILELIST[@]}"
	do
    ALIASID=`echo $ITEM | cut -d "^" -f1`
    PATHID=`echo $ITEM | cut -d "^" -f2`
    PATHID=${PATHID##*/}
    echo $ALIASID >> stage2.csv
    echo '"'$PATHID >> stage2.csv
	done
sort stage2.csv > stage3.csv
uniq stage3.csv > alias_master.csv
rm stage2.csv stage3.csv

##Make the final usable enrichment csv
exit 0
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
            if [[ "$EXTENTVAL" == "" ]]
              then
                EXTENTVAL=0
              else
                EXTENTVAL=${EXTENT% *}
                EXTENTVAL=${EXTENTVAL#\"}
            fi
            TOTALEXT=$(expr $TOTALEXT + $EXTENTVAL)
          else
            TEMPARRAY+=($LINE)
        fi
      done
    STATSENRICH=("${TEMPARRAY[@]}")
    unset TEMPARRAY
    echo $REALORGID"^"$REALORGNAME"^"$REALCOUNCIL"^"$TOTALEXT >> stats-enrich-usable.csv
  done

cleanup

exit 0
