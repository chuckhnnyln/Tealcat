<?php

$SavePath="/home/ubuntu/metadata-archiver/";
$CollectionFeed = "http://54.174.162.83:8080/exist/apps/nyheritage/views/collection_list.xq";
$OrganizationFeed = "http://54.174.162.83:8080/exist/apps/nyheritage/views/institution_list.xq";
$CouncilFeed = "http://54.174.162.83:8080/exist/apps/nyheritage/views/council_list.xq";
date_default_timezone_set('UTC');
$CurrentDate = date("Ymd-His");
$Types = array("coll","inst","coun");
$RetainThreshold = 3;

function grabFile ($Feed, $Type) {
  global $CurrentDate, $SavePath;
  $FullXml = shell_exec("curl -s $Feed");
  $Filename = $Type . "-" . $CurrentDate . ".xml";
  $FullPath = $SavePath . $Filename;
  file_put_contents($FullPath,$FullXml);
}

function killOldest ($TargetList) {
  array_multisort(
    array_map('filectime',$TargetList),
    SORT_NUMERIC,
    SORT_ASC,
    $TargetList
  );
  unlink ($TargetList[0]);
}

foreach ($Types as $Item) {
  grabFile($CollectionFeed,$Item);
  $FileList = glob ("$SavePath$Item*.xml");
  $FileCount = count ($FileList);
  while ( $FileCount > $RetainThreshold ) {
    killOldest ($FileList);
    $FileList = glob ("$Item*.xml");
    $FileCount = count ($FileList);
  }
}

?>
