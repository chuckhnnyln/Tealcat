<?php
#This allows the adding, updating, and deletion of collections and Institution from tealcat.
#Chuck Henry August 2017

require '/var/www/meta/tealcat_db.inc';

#Misc variables
$NYHTeditforms=3; #Defines how many edit blanks NYHTopic get
$Subjeditforms=10; #Defines how many edit blanks Subject get
$Orgseditforms=5; #Defines how many edit blanks InstitutionID gets
$NYHTopicList = array("","Agriculture","Architecture","Arts & Entertainment","Business & Industry","Community & Events","Daily Life","Education","Environment & Nature","Ethnic Groups","Geography & Maps","Government, Law & Politics","Medicine, Science & Technology","Military & War","People","Philosophy & Religion","Recreation & Sports","Transportation","Work & Labor");
$NYCounties = array("","Albany","Allegany","Bronx","Broome","Cattaraugus","Cayuga","Chautauqua","Chemung","Chenango","Clinton","Columbia","Cortland","Delaware","Dutchess","Erie","Essex","Franklin","Fulton","Genesee","Greene","Hamilton","Herkimer","Hidalgo","Jefferson","Kings","Lewis","Livingston","Madison","Monroe","Montgomery","Nassau","New York","Niagara","Oneida","Onondaga","Ontario","Orange","Orleans","Oswego","Otsego","Putnam","Queens","Rensselaer","Richmond","Rockland","St. Lawrence","Saratoga","Schenectady","Schoharie","Schuyler","Seneca","Steuben","Suffolk","Sullivan","Tioga","Tompkins","Ulster","Warren","Washington","Wayne","Westchester","Wyoming","Yates");
$CouncilList = array("","CDLC","CLRC","LILRC","NNYLN","RRLC","SCRLC","WNYLRC","METRO","SENYLRC");

#Configuration settings
$protocol = 'http';
$serverurl = '127.0.0.1:8080';
$serverfolder = 'exist/rest/db/apps/nyheritage/data';
$XSDloc = 'http://54.174.162.83:8080/exist/apps/nyheritage/NYHeritage.xsd';
$OAIProcessCMD = '/home/ubuntu/nyh_scripts/Tealcat/process_oai.sh';

#Functions
function NYHTopicDropdown ($prettyi, $i, $NYHTopicList, $Selected) {
  $NYHTopicCount = count($NYHTopicList);
  echo "NYHTopic $prettyi: <select name='NYHTopic-$i'>";
  for ($c = 0; $c < $NYHTopicCount; $c++) {
    if ( $NYHTopicList[$c] == $Selected ) {
      echo "<option value='$NYHTopicList[$c]' selected>$NYHTopicList[$c]</option>";
    } else {
      echo "<option value='$NYHTopicList[$c]'>$NYHTopicList[$c]</option>";
    }
  }
  echo "</select>";
}


function CountyOptions ($NYCounties, $Selected) {
  $CountyCount = count($NYCounties);
  echo "County:<font color='red'>*</font> <select name='County' required>";
  for ($c=0; $c < $CountyCount; $c++) {
    if ( $NYCounties[$c] == $Selected ){
      echo "<option value='$NYCounties[$c]' selected>$NYCounties[$c]</option>";
    } else {
      echo "<option value='$NYCounties[$c]'>$NYCounties[$c]</option>";
    }
  }
  echo "</select>";
}

function CouncilOptions ($CouncilList, $Selected) {
  $CouncilCount = count($CouncilList);
  echo "CouncilID:<font color='red'>*</font> <select name='CouncilID' required>";
  for ($c=0; $c < $CouncilCount; $c++) {
    if ( $CouncilList[$c] == $Selected) {
      echo "<option value='$CouncilList[$c]' selected>$CouncilList[$c]</option>";
    } else {
      echo "<option value='$CouncilList[$c]'>$CouncilList[$c]</option>";
    }
  }
  echo "</select>";
}

function CreateDisplayName ($InstitutionName, $ParentOrganization) {
  if ( $ParentOrganization == "" ){
    return $InstitutionName;
  } else {
    $DisplayName = $ParentOrganization . " - " . $InstitutionName;
    return $DisplayName;
  }
}

#Determine interface mode and action
if ( isset($_REQUEST['task']) ) {
  $task = $_REQUEST['task'];
  $action = "server-action";
} elseif ( isset($_REQUEST['action']) ) {
  $action = $_REQUEST['action'];
  $task = "user-action";
} else {
  $task = "stop";
  $action = "stop";
}

if ( ( $action == "add" ) && ( isset($_REQUEST['id']) ) ) {
    $addtype = substr($_REQUEST['id'], 0, 4);
    $object = substr ($addtype, 0, 4);
    if ( $addtype == "coll" ) {
      #build the editing form
      echo "<form method='post'>";
      echo "<input type='hidden' name='object' value='coll'>";
      echo "<input type='hidden' name='task' value='addnew-coll'>";
      echo "CollectionID:<font color='red'>*</font> <input type='text' size='35' maxlength='255' name='CollectionID' required>";
      echo "Title:<font color='red'>*</font> <input type='text' size='35' maxlength='255' name='Title' required>";
      echo "CollectionAlias:<font color='red'>*</font> <input type='text' size='35' maxlength='35' name='CollectionAlias' required>";
      for ($i = 0; $i < $Orgseditforms; $i++) {
        $prettyi = $i + 1;
        echo "InstitutionID $prettyi: <input type='text' size='35' maxlength='35' name='InstitutionID-$i'>";
      }
      #echo "InstitutionID:<font color='red'>*</font> <input type='text' size='35' maxlength='35' name='InstitutionID' required>";
      echo "Abstract:<font color='red'>*</font> <textarea size='35' name='Abstract' cols='35' rows='10' required></textarea>";
      echo "DatesOfOriginal: <input type='text' size='35' maxlength='35' name='DatesOfOriginal'>";
      for ($i = 0; $i < $NYHTeditforms; $i++) {
        $prettyi = $i + 1;
        NYHTopicDropdown ($prettyi,$i,$NYHTopicList,'');
      }
      #for ($i = 0; $i < $Subjeditforms; $i++) {
      #  $prettyi = $i + 1;
      #  echo "Subject $prettyi: <input type='text' size='255' maxlength='255' name='Subject-$i'>";
      #}
      echo "BiogHistory: <textarea name='BiogHistory' cols='35' rows='10'></textarea>";
      echo "ScopeAndContent:<font color='red'>*</font> <textarea size='35' name='ScopeAndContent' cols='35' rows='10' required></textarea>";
      echo "PublisherOfDigital: <textarea cols='35' rows='10' name='PublisherOfDigital'></textarea>";
      echo "LocationOfOriginals: <textarea name='LocationOfOriginals' cols='35' rows='10'></textarea>";
      echo "ScopeAndContentSource: <textarea name='ScopeAndContentSource' cols='35' rows='10'></textarea>";
      echo "FindingAidURL: <input type='text' size='35' maxlength='255' name='FindingAidURL'>";
      echo "CollectionType: <input type='text' size='35' maxlength='35' name='CollectionType'>";
      echo "YearbookTitle: <input type='text' size='35' maxlength='100' name='YearbookTitle'>";
      echo "SchoolName: <input type='text' size='35' maxlength='100' name='SchoolName'>";
      echo "SchoolCity: <input type='text' size='35' maxlength='100' name='SchoolCity'>";
      echo "Author: <input type='text' size='35' maxlength='100' name='Author'>";
      echo "<input type='submit' value='Submit'>";
      echo "</form>";
    } elseif ( $addtype == "inst") {
      #build the new Institution form
      echo "<form method='post'>";
      echo "<input type='hidden' name='object' value='inst'>";
      echo "<input type='hidden' name='task' value='addnew-inst'>";
      echo "InstitutionID:<font color='red'>*</font> <input type='text' size='35' maxlength='255' name='InstitutionID' required>";
      echo "InstitutionName:<font color='red'>*</font> <input type='text' size='35' maxlength='255' name='InstitutionName' required>";
      echo "CollectionAlias:<font color='red'>*</font> <input type='text' size='35' maxlength='35' name='CollectionAlias' required>";
      echo "ParentOrganization: <input type='text' size='35' maxlength='50' name='ParentOrganization'>";
      echo "Department: <input type='text' size='35' maxlength='50' name='Department'>";
      echo "ContactPerson: <input type='text' size='255' maxlength='255' name='ContactPerson'>";
      echo "ContactPhone: <input type='text' size='35' maxlength='35' name='ContactPhone'>";
      echo "ContactEmail: <input type='text' size='35' maxlength='255' name='ContactEmail'>";
      echo "Address1: <input type='text' size='35' maxlength='50' name='Address1'>";
      echo "Address2: <input type='text' size='35' maxlength='50' name='Address2'>";
      echo "City: <input type='text' size='35' maxlength='35' name='City'>";
      echo "State: <input type='text' size='35' maxlength='35' name='State'>";
      echo "Zip: <input type='text' size='35' maxlength='35' name='Zip'>";
      CountyOptions ($NYCounties,''); #required
      echo "Phone: <input type='text' size='35' maxlength='35' name='Phone'>";
      echo "Fax: <input type='text' size='35' maxlength='35' name='Fax'>";
      echo "Website: <input type='text' size='35' maxlength='100' name='Website'>";
      echo "About:<font color='red'>*</font> <textarea size='35' name='About' cols='35' rows='10' required></textarea>";
      CouncilOptions ($CouncilList,''); #required
      #echo "LogoURL: <input type='text' size='35' maxlength='35' name='LogoURL'>";
      echo "ProxyMember: <input type='text' size='35' maxlength='35' name='ProxyMember'>";
      #echo "DisplayName: <input type='text' size='35' maxlength='35' name='DisplayName'>";
      echo "<input type='submit' value='Submit'>";
      echo "</form>";
    } else {
      echo "Unable to add metadata... no type specified.";
    }
} elseif ( $action == "update" ) {
  #User interface for updating Collections
  if (isset($_REQUEST['id'])) {
    $objectid = $_REQUEST['id'];
    $object = substr ($objectid, 0, 4);
  } else {
    $object = '';
    $objectid = '';
  }
  $url="$protocol://$serverurl/$serverfolder/$objectid.xml";
  $cmd="curl $protocol://$serverurl/$serverfolder/$objectid.xml";
  if ($object == "coll") {
    $CollectionID = substr ($objectid, 5);
    #Editing a collection
    $collxml = shell_exec($cmd);
    $xmlDoc = new DOMDocument();
    $xmlDoc->loadXML($collxml);
    #Harvest the data from the xml
    $Collections = $xmlDoc->documentElement;
    $Title = $Collections->getElementsByTagName( "Title" )->item(0)->nodeValue;
    $CollectionAlias = $Collections->getElementsByTagName( "CollectionAlias" )->item(0)->nodeValue;
    $Orgscount = $Collections->getElementsByTagName( "InstitutionID" );
    for ($i = 0; $i < $Orgscount->length; $i++) {
      $InstitutionID[$i] = $Collections->getElementsByTagName( "InstitutionID" )->item($i)->nodeValue;
    }
    #$InstitutionID = $Collections->getElementsByTagName( "InstitutionID" )->item(0)->nodeValue;
    $AbstractSearch = $Collections->getElementsByTagName( "Abstract" )->item(0);
    $Abstract = $AbstractSearch->getElementsByTagName( "div" )->item(0)->nodeValue;
    $DatesOfOriginal = $Collections->getElementsByTagName( "DatesOfOriginal" )->item(0)->nodeValue;
    $NYHtopiccount = $Collections->getElementsByTagName( "NYHTopic" );
    $NYHTopic = array();
    for ($i = 0; $i < $NYHtopiccount->length; $i++) {
      $NYHTopic[$i] = $Collections->getElementsByTagName( "NYHTopic" )->item($i)->nodeValue;
    }
    $subjectcount = $Collections->getElementsByTagName( "Subject" );
    $Subject = array();
    for ($i = 0; $i < $subjectcount->length; $i++) {
      $Subject[$i] = $Collections->getElementsByTagName( "Subject" )->item($i)->nodeValue;
    }
    $BiogHistorySearch = $Collections->getElementsByTagName( "BiogHistory" )->item(0);
    $BiogHistory = $BiogHistorySearch->getElementsByTagName( "div" )->item(0)->nodeValue;
    $ScopeAndContentSearch = $Collections->getElementsByTagName( "ScopeAndContent" )->item(0);
    $ScopeAndContent = $ScopeAndContentSearch->getElementsByTagName( "div" )->item(0)->nodeValue;
    $PublisherOfDigital = $Collections->getElementsByTagName( "PublisherOfDigital" )->item(0)->nodeValue;
    $LocationOfOriginals = $Collections->getElementsByTagName( "LocationOfOriginals" )->item(0)->nodeValue;
    $ScopeAndContentSourceSearch = $Collections->getElementsByTagName( "ScopeAndContentSource" )->item(0);
    $ScopeAndContentSource = $ScopeAndContentSourceSearch->getElementsByTagName( "div" )->item(0)->nodeValue;
    $FindingAidURL = $Collections->getElementsByTagName( "FindingAidURL" )->item(0)->nodeValue;
    $CollectionType = $Collections->getElementsByTagName( "CollectionType" )->item(0)->nodeValue;
    #$SampleImageURL = $Collections->getElementsByTagName( "SampleImageURL" )->item(0)->nodeValue;
    $NewElementSearch = "false";
    $AllElements = $Collections->getElementsByTagName('*');
    foreach ($AllElements as $EachElement) {
      #echo $EachElement->tagName . " - ". $NewElementSearch;
      if ( ($EachElement->tagName) == "YearbookTitle" ) {
        $NewElementSearch = "true";
      }
    }
    if ( $NewElementSearch == "true" ) {
      $YearbookTitle = $Collections->getElementsByTagName( "YearbookTitle" )->item(0)->nodeValue;
      $SchoolName = $Collections->getElementsByTagName( "SchoolName" )->item(0)->nodeValue;
      $SchoolCity = $Collections->getElementsByTagName( "SchoolCity" )->item(0)->nodeValue;
      $Author = $Collections->getElementsByTagName( "Author" )->item(0)->nodeValue;
    } else {
      $YearbookTitle = '';
      $SchoolName = '';
      $SchoolCity = '';
      $Author = '';
    }
    #build the editing collection form
    echo "<form method='post'>";
    echo "<input type='hidden' name='object' value='$object'>";
    echo "<input type='hidden' name='task' value='upcoll'>";
    echo "<input type='hidden' name='OriginID' value='$CollectionID'>";
    echo "CollectionID:<font color='red'>*</font> <input type='text' size='35' maxlength='255' name='CollectionID' value=\"$CollectionID\" required>";
    echo "Title:<font color='red'>*</font> <input type='text' size='35' maxlength='255' name='Title' value=\"$Title\" required>";
    echo "CollectionAlias:<font color='red'>*</font> <input type='text' size='35' maxlength='35' name='CollectionAlias' value='$CollectionAlias' required>";
    #echo "InstitutionID:<font color='red'>*</font> <input type='text' size='35' maxlength='35' name='InstitutionID' value='$InstitutionID' required>";
    for ($i = 0; $i < $Orgseditforms; $i++) {
      if ( $i < $Orgscount->length ) {
        $prettyi = $i + 1;
        echo "InstitutionID $prettyi: <input type='text' size='35' maxlength='35' name='InstitutionID-$i' value='$InstitutionID[$i]'>";
      } else {
        $prettyi = $i + 1;
        echo "InstitutionID $prettyi: <input type='text' size='35' maxlength='35' name='InstitutionID-$i'>";
      }
    }
    echo "Abstract:<font color='red'>*</font> <textarea size='35' name='Abstract' cols='35' rows='10' required>$Abstract</textarea>";
    echo "DatesOfOriginal: <input type='text' size='35' maxlength='35' name='DatesOfOriginal' value='$DatesOfOriginal'>";
    for ($i = 0; $i < $NYHTeditforms; $i++) {
      if ( $i < $NYHtopiccount->length ) {
        $prettyi = $i + 1;
        #echo "NYHTopic $prettyi: <input type='text' size='255' maxlength='255' name='NYHTopic-$i' value='$NYHTopic[$i]'>";
        NYHTopicDropdown ($prettyi,$i,$NYHTopicList,$NYHTopic[$i]);
      } else {
        $prettyi = $i + 1;
        #echo "NYHTopic $prettyi: <input type='text' size='255' maxlength='255' name='NYHTopic-$i'>";
        NYHTopicDropdown ($prettyi,$i,$NYHTopicList,'');
      }
    }
    #for ($i = 0; $i < $Subjeditforms; $i++) {
    #  if ( $i < $subjectcount->length ) {
    #    $prettyi = $i + 1;
    #    echo "Subject $prettyi: <input type='text' size='255' maxlength='255' name='Subject-$i' value='$Subject[$i]'>";
    #  } else {
    #    $prettyi = $i + 1;
    #    echo "Subject $prettyi: <input type='text' size='255' maxlength='255' name='Subject-$i'>";
    #  }
    #}
    echo "BiogHistory: <textarea name='BiogHistory' cols='35' rows='10'>$BiogHistory</textarea>";
    echo "ScopeAndContent:<font color='red'>*</font> <textarea size='35' name='ScopeAndContent' cols='35' rows='10' required>$ScopeAndContent</textarea>";
    echo "PublisherOfDigital: <textarea name='PublisherOfDigital' cols='35' rows='10'>$PublisherOfDigital</textarea>";
    echo "LocationOfOriginals: <textarea name='LocationOfOriginals' size='35' maxlength='35'>$LocationOfOriginals</textarea>";
    echo "ScopeAndContentSource: <textarea name='ScopeAndContentSource' cols='35' rows='10'>$ScopeAndContentSource</textarea>";
    echo "FindingAidURL: <input type='text' size='35' maxlength='255' name='FindingAidURL' value='$FindingAidURL'>";
    echo "CollectionType: <input type='text' size='35' maxlength='35' name='CollectionType' value='$CollectionType'>";
    echo "YearbookTitle: <input type='text' size='35' maxlength='100' name='YearbookTitle' value='$YearbookTitle'>";
    echo "SchoolName: <input type='text' size='35' maxlength='100' name='SchoolName' value='$SchoolName'>";
    echo "SchoolCity: <input type='text' size='35' maxlength='100' name='SchoolCity' value='$SchoolCity'>";
    echo "Author: <input type='text' size='35' maxlength='100' name='Author' value='$Author'>";
    echo "<input type='submit' value='Submit'>";
    echo "</form>";

  } else if ($object == "inst") {
    #Editing an organization
    $InstitutionID = substr ($objectid, 5);
    #Editing a collection
    $instxml = shell_exec($cmd);
    $xmlDoc = new DOMDocument();
    $xmlDoc->loadXML($instxml);

    #Harvest the data from the xml
    $Institution = $xmlDoc->documentElement;
    $InstitutionName = $Institution->getElementsByTagName( "InstitutionName" )->item(0)->nodeValue;
    $CollectionAlias = $Institution->getElementsByTagName( "CollectionAlias" )->item(0)->nodeValue;
    $ParentOrganization = $Institution->getElementsByTagName( "ParentOrganization" )->item(0)->nodeValue;
    $Department = $Institution->getElementsByTagName( "Department" )->item(0)->nodeValue;
    $ContactInfo = $Institution->getElementsByTagName( "ContactInfo" )->item(0);
      $ContactPerson = $ContactInfo->getElementsByTagName( "ContactPerson" )->item(0)->nodeValue;
      $ContactPhone = $ContactInfo->getElementsByTagName( "ContactPhone" )->item(0)->nodeValue;
      $ContactEmail = $ContactInfo->getElementsByTagName( "ContactEmail" )->item(0)->nodeValue;
      $Address1 = $ContactInfo->getElementsByTagName( "Address1" )->item(0)->nodeValue;
      $Address2 = $ContactInfo->getElementsByTagName( "Address2" )->item(0)->nodeValue;
      $City = $ContactInfo->getElementsByTagName( "City" )->item(0)->nodeValue;
      $State = $ContactInfo->getElementsByTagName( "State" )->item(0)->nodeValue;
      $Zip = $ContactInfo->getElementsByTagName( "Zip" )->item(0)->nodeValue;
      $County = $ContactInfo->getElementsByTagName( "County" )->item(0)->nodeValue;
      $Phone = $ContactInfo->getElementsByTagName( "Phone" )->item(0)->nodeValue;
      $Fax = $ContactInfo->getElementsByTagName( "Fax" )->item(0)->nodeValue;
      $Website = $ContactInfo->getElementsByTagName( "Website" )->item(0)->nodeValue;
    $AboutSearch = $Institution->getElementsByTagName( "About" )->item(0);
    $About = $AboutSearch->getElementsByTagName( "div" )->item(0)->nodeValue;
    $CouncilID = $Institution->getElementsByTagName( "CouncilID" )->item(0)->nodeValue;
    #$LogoURL = $Institution->getElementsByTagName( "LogoURL" )->item(0)->nodeValue;
    $ProxyMember = $Institution->getElementsByTagName( "ProxyMember" )->item(0)->nodeValue;
    $DisplayName = $Institution->getElementsByTagName( "DisplayName" )->item(0)->nodeValue;

    #build the editing form
    echo "<form method='post'>";
    echo "<input type='hidden' name='object' value='$object'>";
    echo "<input type='hidden' name='task' value='upinst'>";
    echo "<input type='hidden' name='InstitutionID' value='$InstitutionID'>";
    echo "InstitutionID: <b>$InstitutionID</b></br></br>";
    echo "InstitutionName:<font color='red'>*</font> <input type='text' size='35' maxlength='255' name='InstitutionName' value=\"$InstitutionName\" required>";
    echo "CollectionAlias:<font color='red'>*</font> <input type='text' size='35' maxlength='255' name='CollectionAlias' value=\"$CollectionAlias\" required>";
    echo "ParentOrganization: <input type='text' size='35' maxlength='50' name='ParentOrganization' value=\"$ParentOrganization\">";
    echo "Department: <input type='text' size='35' maxlength='50' name='Department' value='$Department'>";
    echo "ContactPerson: <input type='text' size='255' maxlength='255' name='ContactPerson' value='$ContactPerson'>";
    echo "ContactPhone: <input type='text' size='35' maxlength='35' name='ContactPhone' value='$ContactPhone'>";
    echo "ContactEmail: <input type='text' size='35' maxlength='255' name='ContactEmail' value='$ContactEmail'>";
    echo "Address1: <input type='text' size='35' maxlength='50' name='Address1' value='$Address1'>";
    echo "Address2: <input type='text' size='35' maxlength='50' name='Address2' value='$Address2'>";
    echo "City: <input type='text' size='35' maxlength='35' name='City' value='$City'>";
    echo "State: <input type='text' size='35' maxlength='35' name='State' value='$State'>";
    echo "Zip: <input type='text' size='35' maxlength='35' name='Zip' value='$Zip'>";
    CountyOptions ($NYCounties, $County); #Required
    echo "Phone: <input type='text' size='35' maxlength='35' name='Phone' value='$Phone'>";
    echo "Fax: <input type='text' size='35' maxlength='35' name='Fax' value='$Fax'>";
    echo "Website: <input type='text' size='35' maxlength='100' name='Website' value='$Website'>";
    echo "About: <textarea size='35' name='About' cols='35' rows='10'>$About</textarea>";
    CouncilOptions ($CouncilList, $CouncilID); #Required
    #echo "LogoURL: <input type='text' size='35' maxlength='35' name='LogoURL' value='$LogoURL'>";
    echo "ProxyMember: <input type='text' size='35' maxlength='35' name='ProxyMember' value='$ProxyMember'>";
    #echo "DisplayName: <input type='text' size='35' maxlength='35' name='DisplayName' value='$DisplayName'>";
    echo "<input type='submit' value='Submit'>";
    echo "</form>";
  } else {
    #Displaying nothing at all
    echo "Error! ID not set correctly.";
  }
} elseif ( $action == "delete" ) {
  #User interface for deleting Collections
  if (isset($_REQUEST['id'])) {
    $objectid = $_REQUEST['id'];
    $object = substr ($objectid, 0, 4);
    $ItemID = substr ($objectid, 5);

    #Get the original xml from the server
    $url="$protocol://$serverurl/$serverfolder/$objectid.xml";
    $cmd="curl $protocol://$serverurl/$serverfolder/$objectid.xml";
    $collxml = shell_exec($cmd);
    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->formatOutput = true;
    $xmlDoc->loadXML($collxml);
    $ItemInfo = $xmlDoc->documentElement;

    if ( $object == 'coll' ) {
      $PrettyItemName = $ItemInfo->getElementsByTagName( "Title" )->item(0)->nodeValue;
      $PrettyItemType = 'collection';
    } else {
      $PrettyItemName = $ItemInfo->getElementsByTagName( "InstitutionName" )->item(0)->nodeValue;
      $PrettyItemType = 'organization';
    }
    echo "<br><br>";
    echo "Are you sure you want to delete the metadata associated with the <b>" . $PrettyItemType . "</b> called <b>" . $PrettyItemName . "</b>?";
    echo "<form method='post'>";
    echo "<input type='hidden' name='object' value='$object'>";
    echo "<input type='hidden' name='task' value='delitem'>";
    echo "<input type='hidden' name='ItemID' value='$ItemID'>";
    echo "<input type='hidden' name='objectid' value='$objectid'>";
    echo "<input type='hidden' name='PrettyItemName' value='$PrettyItemName'>";
    echo "<input type='hidden' name='PrettyItemType' value='$PrettyItemType'>";
    echo "<br><br><a href='/'>Cancel!</a> <input type='submit' value='Delete'>";
    echo "</form>";
    echo "<br><br><br>";
  } else {
    echo "Not deleting anything. No ID set.";
  }
} elseif ( $action == "server-action" ) {
  #The Server doing stuff!
  if ( $task == "upcoll" ) {
    ### Updating the collection!
    #Get the data from the form
    $CollectionID = $_REQUEST['CollectionID'];
    $OriginID = $_REQUEST['OriginID'];
    $CollectionID = trim($CollectionID);
    $OriginID = trim($OriginID);
    $Title = $_REQUEST['Title'];
    $CollectionAlias = $_REQUEST['CollectionAlias'];
    #$InstitutionID = $_REQUEST['InstitutionID'];
    $InstitutionID = array();
    for ($i = 0; $i < $Orgseditforms; $i++) {
      if ( isset($_REQUEST['InstitutionID-' . $i]) ) $InstitutionID[$i] = $_REQUEST['InstitutionID-' . $i];
    }
    $Abstract = $_REQUEST['Abstract'];
    $DatesOfOriginal = $_REQUEST['DatesOfOriginal'];
    $BiogHistory = $_REQUEST['BiogHistory'];
    $ScopeAndContent = $_REQUEST['ScopeAndContent'];
    $PublisherOfDigital = $_REQUEST['PublisherOfDigital'];
    $LocationOfOriginals = $_REQUEST['LocationOfOriginals'];
    $ScopeAndContentSource = $_REQUEST['ScopeAndContentSource'];
    $FindingAidURL = $_REQUEST['FindingAidURL'];
    $CollectionType = $_REQUEST['CollectionType'];
    $YearbookTitle = $_REQUEST['YearbookTitle'];
    $SchoolName = $_REQUEST['SchoolName'];
    $SchoolCity = $_REQUEST['SchoolCity'];
    $Author = $_REQUEST['Author'];
    $object = $_REQUEST['object'];
    $OldObjectID = $object . "_" . $OriginID;
    $NewObjectID = $object . "_" . $CollectionID;
    $NYHTopic = array();
    for ($i = 0; $i < $NYHTeditforms; $i++) {
      if ( isset($_REQUEST['NYHTopic-' . $i]) ) $NYHTopic[$i] = $_REQUEST['NYHTopic-' . $i];
    }
    $Subject = array();
    for ($i = 0; $i < $Subjeditforms; $i++) {
      if ( isset($_REQUEST['Subject-' . $i]) ) $Subject[$i] = $_REQUEST['Subject-' . $i];
    }
    #Get the original xml from the server
    $url="$protocol://$serverurl/$serverfolder/$NewObjectID.xml";
    $cmd="curl $protocol://$serverurl/$serverfolder/$OldObjectID.xml";
    $collxml = shell_exec($cmd);
    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->formatOutput = true;
    $xmlDoc->loadXML($collxml);
    $Collections = $xmlDoc->documentElement;
    #Update XML fields from form data
    $Collections->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
    $Collections->setAttribute('CollectionID',$CollectionID);
    $Collections->setAttribute('xsi:noNamespaceSchemaLocation',$XSDloc);
    $Collections->getElementsByTagName( "Title" )->item(0)->nodeValue = "$Title";
    $Collections->getElementsByTagName( "CollectionAlias" )->item(0)->nodeValue = "$CollectionAlias";
    #$Collections->getElementsByTagName( "InstitutionID" )->item(0)->nodeValue = "$InstitutionID";
    $AbstractSearch = $Collections->getElementsByTagName( "Abstract" )->item(0);
    $Abstract = htmlspecialchars($Abstract);
    $AbstractSearch = $AbstractSearch->getElementsByTagName( "div" )->item(0)->nodeValue = "$Abstract";
    $Collections->getElementsByTagName( "DatesOfOriginal" )->item(0)->nodeValue = "$DatesOfOriginal";
    $BiogHistorySearch = $Collections->getElementsByTagName( "BiogHistory" )->item(0);
    $BiogHistory = htmlspecialchars($BiogHistory);
    $BiogHistorySearch->getElementsByTagName( "div" )->item(0)->nodeValue = "$BiogHistory";
    $ScopeAndContentSearch = $Collections->getElementsByTagName( "ScopeAndContent" )->item(0);
    $ScopeAndContent = htmlspecialchars($ScopeAndContent);
    $ScopeAndContentSearch->getElementsByTagName( "div" )->item(0)->nodeValue = "$ScopeAndContent";
    $PublisherOfDigital = htmlspecialchars($PublisherOfDigital);
    $Collections->getElementsByTagName( "PublisherOfDigital" )->item(0)->nodeValue = "$PublisherOfDigital";
    $ScopeAndContentSourceSearch = $Collections->getElementsByTagName( "ScopeAndContentSource" )->item(0);
    $ScopeAndContentSource = htmlspecialchars($ScopeAndContentSource);
    $ScopeAndContentSourceSearch->getElementsByTagName( "div" )->item(0)->nodeValue = "$ScopeAndContentSource";
    $LocationOfOriginals = htmlspecialchars($LocationOfOriginals);
    $Collections->getElementsByTagName( "LocationOfOriginals" )->item(0)->nodeValue = "$LocationOfOriginals";
    $FindingAidURL = htmlspecialchars($FindingAidURL,ENT_XML1);
    $Collections->getElementsByTagName( "FindingAidURL" )->item(0)->nodeValue = "$FindingAidURL";
    $Collections->getElementsByTagName( "CollectionType" )->item(0)->nodeValue = "$CollectionType";
    $NewElementSearch = "false";
    $AllElements = $Collections->getElementsByTagName('*');
    foreach ($AllElements as $EachElement) {
      #echo $EachElement->tagName . " - ". $NewElementSearch;
      if ( ($EachElement->tagName) == "YearbookTitle" ) {
        $NewElementSearch = "true";
      }
    }
    if ( $NewElementSearch == "true" ) {
      $Collections->getElementsByTagName( "YearbookTitle" )->item(0)->nodeValue = "$YearbookTitle";
      $Collections->getElementsByTagName( "SchoolName" )->item(0)->nodeValue = "$SchoolName";
      $Collections->getElementsByTagName( "SchoolCity" )->item(0)->nodeValue = "$SchoolCity";
      $Collections->getElementsByTagName( "Author" )->item(0)->nodeValue = "$Author";

    } else {
      $Collections->appendChild($xmlDoc->createElement('YearbookTitle',"$YearbookTitle"));
      $Collections->appendChild($xmlDoc->createElement('SchoolName',"$SchoolName"));
      $Collections->appendChild($xmlDoc->createElement('SchoolCity',"$SchoolCity"));
      $Collections->appendChild($xmlDoc->createElement('Author',"$Author"));
    }
    #Remove existing NYHTopics
    $RemoveList = $Collections->getElementsByTagName( "NYHTopic" );
    $TopicRemoveList = array ();
    foreach ( $RemoveList as $x ) $TopicRemoveList[] = $x;
    if ( count($TopicRemoveList) > 0) foreach ( $TopicRemoveList as $y ) $y->parentNode->removeChild($y);
    #Add new NYHTopics
    for ($i = 0; $i < $NYHTeditforms; $i++) {
      if ( $NYHTopic[$i] !== "" ) {
        $Added = $xmlDoc->createElement("NYHTopic");
        $AddedText = $xmlDoc->createTextNode($NYHTopic[$i]);
        $Added->appendChild($AddedText);
        $Collections->appendChild($Added);
      }
    }
    #Remove existing Subjects
    $RemoveList = $Collections->getElementsByTagName( "Subject" );
    $TopicRemoveList = array ();
    foreach ( $RemoveList as $x ) $TopicRemoveList[] = $x;
    if ( count($TopicRemoveList) > 0) foreach ( $TopicRemoveList as $y ) $y->parentNode->removeChild($y);

    #Remove existing InstitutionIDs
    $RemoveList = $Collections->getElementsByTagName( "InstitutionID" );
    $TopicRemoveList = array ();
    foreach ( $RemoveList as $x ) $TopicRemoveList[] = $x;
    if ( count($TopicRemoveList) > 0) foreach ( $TopicRemoveList as $y ) $y->parentNode->removeChild($y);
    #Add new InstitutionIDs
    for ($i = 0; $i < $Orgseditforms; $i++) {
      if ( $InstitutionID[$i] !== "" ) {
        $Added = $xmlDoc->createElement("InstitutionID");
        $AddedText = $xmlDoc->createTextNode($InstitutionID[$i]);
        $Added->appendChild($AddedText);
        $Collections->appendChild($Added);
      }
    }

    #Place XML on server
    $xml_data = $xmlDoc->saveXML($xmlDoc->documentElement);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
    $output = curl_exec($ch);
    curl_close($ch);

    #Remove old collectionID XML if needed
    if ( $CollectionID !== $OriginID ) {
      #Delete the item!
      $url="$protocol://$serverurl/$serverfolder/$OldObjectID.xml";
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      echo curl_error($ch) . "<br><br>";
      curl_close($ch);
    }

    echo "Updated " . $CollectionAlias . " : <b>" . $Title . "</b><br><br>";
    echo "Changes to the metadata <b>will not</b> appear immediately for the public. Metadata changes will update within 15 minutes.<br><br>";
    ### End Updating the Collection
  } elseif ( $task == "upinst" ) {
    $InstitutionID = $_REQUEST['InstitutionID'];
    $InstitutionName = $_REQUEST['InstitutionName'];
    $ParentOrganization = $_REQUEST['ParentOrganization'];
    $CollectionAlias = $_REQUEST['CollectionAlias'];
    $Department = $_REQUEST['Department'];
      $ContactPerson = $_REQUEST['ContactPerson'];
      $ContactPhone = $_REQUEST['ContactPhone'];
      $ContactEmail = $_REQUEST['ContactEmail'];
      $Address1 = $_REQUEST['Address1'];
      $Address2 = $_REQUEST['Address2'];
      $City = $_REQUEST['City'];
      $State = $_REQUEST['State'];
      $Zip = $_REQUEST['Zip'];
      $County = $_REQUEST['County'];
      $Phone = $_REQUEST['Phone'];
      $Fax = $_REQUEST['Fax'];
      $Website = $_REQUEST['Website'];
    $About = $_REQUEST['About'];
    $CouncilID = $_REQUEST['CouncilID'];
    #$LogoURL = $_REQUEST['LogoURL'];
    $ProxyMember = $_REQUEST['ProxyMember'];
    #$DisplayName = $_REQUEST['DisplayName'];
    $object = $_REQUEST['object'];
    $objectid = $object . "_" . $InstitutionID;

    #Get the original xml from the server
    $url="$protocol://$serverurl/$serverfolder/$objectid.xml";
    $cmd="curl $protocol://$serverurl/$serverfolder/$objectid.xml";
    $instxml = shell_exec($cmd);
    $xmlDoc = new DOMDocument();
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->formatOutput = true;
    $xmlDoc->loadXML($instxml);
    $Institution = $xmlDoc->documentElement;
    #Update XML fields from form data
    $Institution->getElementsByTagName( "InstitutionName" )->item(0)->nodeValue = "$InstitutionName";
    $Institution->getElementsByTagName( "CollectionAlias" )->item(0)->nodeValue = "$CollectionAlias";
    $Institution->getElementsByTagName( "ParentOrganization" )->item(0)->nodeValue = "$ParentOrganization";
    $Institution->getElementsByTagName( "Department" )->item(0)->nodeValue = "$Department";
    $ContactInfo = $Institution->getElementsByTagName( "ContactInfo" )->item(0);
      $ContactInfo->getElementsByTagName( "ContactPerson" )->item(0)->nodeValue = "$ContactPerson";
      $ContactInfo->getElementsByTagName( "ContactEmail" )->item(0)->nodeValue = "$ContactEmail";
      $ContactInfo->getElementsByTagName( "ContactPhone" )->item(0)->nodeValue = "$ContactPhone";
      $ContactInfo->getElementsByTagName( "Address1" )->item(0)->nodeValue = "$Address1";
      $ContactInfo->getElementsByTagName( "Address2" )->item(0)->nodeValue = "$Address2";
      $ContactInfo->getElementsByTagName( "City" )->item(0)->nodeValue = "$City";
      $ContactInfo->getElementsByTagName( "State" )->item(0)->nodeValue = "$State";
      $ContactInfo->getElementsByTagName( "Zip" )->item(0)->nodeValue = "$Zip";
      $ContactInfo->getElementsByTagName( "County" )->item(0)->nodeValue = "$County";
      $ContactInfo->getElementsByTagName( "Phone" )->item(0)->nodeValue = "$Phone";
      $ContactInfo->getElementsByTagName( "Fax" )->item(0)->nodeValue = "$Fax";
      $ContactInfo->getElementsByTagName( "Website" )->item(0)->nodeValue = "$Website";
    $AboutSearch = $Institution->getElementsByTagName( "About" )->item(0);
      $About = htmlspecialchars ($About);
      $AboutSearch->getElementsByTagName( "div" )->item(0)->nodeValue = "$About";
    $Institution->getElementsByTagName( "CouncilID" )->item(0)->nodeValue = "$CouncilID";
    #$Institution->getElementsByTagName( "LogoURL" )->item(0)->nodeValue = "$LogoURL";
    $Institution->getElementsByTagName( "ProxyMember" )->item(0)->nodeValue = "$ProxyMember";
    $DisplayName = CreateDisplayName ($InstitutionName,$ParentOrganization);
    $Institution->getElementsByTagName( "DisplayName" )->item(0)->nodeValue = "$DisplayName";

    #Place XML on server
    $xml_data = $xmlDoc->saveXML($xmlDoc->documentElement);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
    $output = curl_exec($ch);
    curl_close($ch);
    echo "Updated " . $InstitutionID . " : <b>" . $DisplayName . "</b><br><br>";
    echo "Changes to the metadata <b>will not</b> appear immediately for the public. Metadata changes will update within 15 minutes.<br><br>";
    ### END update organization!
  } elseif ( $task == "delitem" ) {
    if (isset($_REQUEST['ItemID']) ) {
      $objectid = $_REQUEST['objectid'];
      $object = $_REQUEST['object'];
      $ItemID = $_REQUEST['ItemID'];
      $PrettyItemType = $_REQUEST['PrettyItemType'];
      $PrettyItemName = $_REQUEST['PrettyItemName'];
      #Delete the item!
      $url="$protocol://$serverurl/$serverfolder/$objectid.xml";
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      echo curl_error($ch) . "<br><br>";
      curl_close($ch);

      echo "<br><br>";
      echo "The " . $PrettyItemType . " <b>" . $PrettyItemName . "</b> has been deleted.";
      echo "<br><br>";
    } else {
      echo "Not deleting anything. No Item ID set.";
    }
  } elseif ( $task == "addnew-coll" ) {
    ### Adding new collection!
    #Get the data from the form
    $CollectionID = $_REQUEST['CollectionID'];
    $CollectionID = strtoupper($CollectionID);
    $Title = $_REQUEST['Title'];
    $CollectionAlias = $_REQUEST['CollectionAlias'];
    #$InstitutionID = $_REQUEST['InstitutionID'];
    $InstitutionID = array();
    for ($i = 0; $i < $Orgseditforms; $i++) {
      if ( isset($_REQUEST['InstitutionID-' . $i]) ) $InstitutionID[$i] = $_REQUEST['InstitutionID-' . $i];
    }
    $Abstract = $_REQUEST['Abstract'];
    $DatesOfOriginal = $_REQUEST['DatesOfOriginal'];
    $BiogHistory = $_REQUEST['BiogHistory'];
    $ScopeAndContent = $_REQUEST['ScopeAndContent'];
    $PublisherOfDigital = $_REQUEST['PublisherOfDigital'];
    $LocationOfOriginals = $_REQUEST['LocationOfOriginals'];
    $ScopeAndContentSource = $_REQUEST['ScopeAndContentSource'];
    $FindingAidURL = $_REQUEST['FindingAidURL'];
    $CollectionType = $_REQUEST['CollectionType'];
    $YearbookTitle = $_REQUEST['YearbookTitle'];
    $SchoolName = $_REQUEST['SchoolName'];
    $SchoolCity = $_REQUEST['SchoolCity'];
    $Author = $_REQUEST['Author'];
    $object = $_REQUEST['object'];
    $objectid = $object . "_" . $CollectionID;
    $NYHTopic = array();
    for ($i = 0; $i < $NYHTeditforms; $i++) {
      if ( isset($_REQUEST['NYHTopic-' . $i]) ) $NYHTopic[$i] = $_REQUEST['NYHTopic-' . $i];
    }
    #$Subject = array();
    #for ($i = 0; $i < $Subjeditforms; $i++) {
    #  if ( isset($_REQUEST['Subject-' . $i]) ) $Subject[$i] = $_REQUEST['Subject-' . $i];
    #}
    $url="$protocol://$serverurl/$serverfolder/$objectid.xml";

    $xmlDoc = new DOMDocument('1.0','UTF-8');
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->formatOutput = true;

    $Collections = $xmlDoc->createElement('Collection');
    $xmlDoc->appendChild($Collections);

    $Collections->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
    $Collections->setAttribute('CollectionID',$CollectionID);
    $Collections->setAttribute('xsi:noNamespaceSchemaLocation',$XSDloc);

    $Collections->appendChild($xmlDoc->createElement('Title',$Title));
    $Collections->appendChild($xmlDoc->createElement('CollectionAlias',$CollectionAlias));
    #$Collections->appendChild($xmlDoc->createElement('InstitutionID',$InstitutionID));
    $AbstractSearch = $xmlDoc->createElement('Abstract');
    $Collections->appendChild($AbstractSearch);
    $AbstractSearch->appendChild($xmlDoc->createElement('div',htmlspecialchars($Abstract)));
    $Collections->appendChild($xmlDoc->createElement('DatesOfOriginal',$DatesOfOriginal));
    $BiogHistorySearch = $xmlDoc->createElement('BiogHistory');
    $Collections->appendChild($BiogHistorySearch);
    $BiogHistorySearch->appendChild($xmlDoc->createElement('div',htmlspecialchars($BiogHistory)));
    $ScopeAndContentSearch = $xmlDoc->createElement('ScopeAndContent');
    $Collections->appendChild($ScopeAndContentSearch);
    $ScopeAndContentSearch->appendChild($xmlDoc->createElement('div',htmlspecialchars($ScopeAndContent)));
    $Collections->appendChild($xmlDoc->createElement('PublisherOfDigital',htmlspecialchars($PublisherOfDigital)));
    $ScopeAndContentSourceSearch = $xmlDoc->createElement('ScopeAndContentSource');
    $Collections->appendChild($ScopeAndContentSourceSearch);
    $ScopeAndContentSourceSearch->appendChild($xmlDoc->createElement('div',htmlspecialchars($ScopeAndContentSource)));
    $Collections->appendChild($xmlDoc->createElement('LocationOfOriginals',htmlspecialchars($LocationOfOriginals)));
    $Collections->appendChild($xmlDoc->createElement('FindingAidURL',$FindingAidURL));
    $Collections->appendChild($xmlDoc->createElement('CollectionType',$CollectionType));
    $Collections->appendChild($xmlDoc->createElement('YearbookTitle',$YearbookTitle));
    $Collections->appendChild($xmlDoc->createElement('SchoolName',$SchoolName));
    $Collections->appendChild($xmlDoc->createElement('SchoolCity',$SchoolCity));
    $Collections->appendChild($xmlDoc->createElement('Author',$Author));
    #Add new NYHTopics
    for ($i = 0; $i < $NYHTeditforms; $i++) {
      if ( $NYHTopic[$i] !== "" ) {
        $NYHTopic[$i] = htmlspecialchars($NYHTopic[$i]);
        $Collections->appendChild($xmlDoc->createElement('NYHTopic',$NYHTopic[$i]));
      }
    }
    #Add new InstitutionIDs
    for ($i = 0; $i < $Orgseditforms; $i++) {
      if ( $InstitutionID[$i] !== "" ) {
        $Added = $xmlDoc->createElement("InstitutionID");
        $AddedText = $xmlDoc->createTextNode($InstitutionID[$i]);
        $Added->appendChild($AddedText);
        $Collections->appendChild($Added);
      }
    }
    #Add new Subjects
    #for ($i = 0; $i < $Subjeditforms; $i++) {
    #  if ( $Subject[$i] !== "" ) {
    #    $Collections->appendChild($xmlDoc->createElement('Subject',$Subject[$i]));
    #  }
    #}
    #Place XML on server
    $xml_data = $xmlDoc->saveXML($xmlDoc->documentElement);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
    $output = curl_exec($ch);
    curl_close($ch);
    #echo "<textarea>" . $xml_data . "</textarea><br><br>";
    echo "Updated " . $CollectionID . " : <b>" . $Title . "</b><br><br>";
    echo "Changes to the metadata <b>will not</b> appear immediately for the public. Metadata changes will update within 15 minutes.<br><br>";
    exec($OAIProcessCMD);
    ### End adding new collection
  } elseif ( $task == "addnew-inst" ) {
    $InstitutionID = $_REQUEST['InstitutionID'];
    $InstitutionName = $_REQUEST['InstitutionName'];
    $CollectionAlias = $_REQUEST['CollectionAlias'];
    $ParentOrganization = $_REQUEST['ParentOrganization'];
    $Department = $_REQUEST['Department'];
      $ContactPerson = $_REQUEST['ContactPerson'];
      $ContactPhone = $_REQUEST['ContactPhone'];
      $ContactEmail = $_REQUEST['ContactEmail'];
      $Address1 = $_REQUEST['Address1'];
      $Address2 = $_REQUEST['Address2'];
      $City = $_REQUEST['City'];
      $State = $_REQUEST['State'];
      $Zip = $_REQUEST['Zip'];
      $County = $_REQUEST['County'];
      $Phone = $_REQUEST['Phone'];
      $Fax = $_REQUEST['Fax'];
      $Website = $_REQUEST['Website'];
    $About = $_REQUEST['About'];
    $CouncilID = $_REQUEST['CouncilID'];
    #$LogoURL = $_REQUEST['LogoURL'];
    $ProxyMember = $_REQUEST['ProxyMember'];
    #$DisplayName = $_REQUEST['DisplayName'];
    $object = $_REQUEST['object'];
    $objectid = $object . "_" . $InstitutionID;

    $url="$protocol://$serverurl/$serverfolder/$objectid.xml";

    $xmlDoc = new DOMDocument('1.0','UTF-8');
    $xmlDoc->preserveWhiteSpace = false;
    $xmlDoc->formatOutput = true;

    $Institutions = $xmlDoc->createElement('Institution');
    $xmlDoc->appendChild($Institutions);

    $Institutions->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
    $Institutions->setAttribute('InstitutionID',$InstitutionID);
    $Institutions->setAttribute('xsi:noNamespaceSchemaLocation',$XSDloc);

    $Institutions->appendChild($xmlDoc->createElement('InstitutionName',$InstitutionName));
    $Institutions->appendChild($xmlDoc->createElement('ParentOrganization',$ParentOrganization));
    $Institutions->appendChild($xmlDoc->createElement('CollectionAlias',$CollectionAlias));
    $Institutions->appendChild($xmlDoc->createElement('Department',$Department));
    $ContactInfo = $xmlDoc->createElement('ContactInfo');
    $Institutions->appendChild($ContactInfo);
    $ContactInfo->appendChild($xmlDoc->createElement('ContactPerson',$ContactPerson));
    $ContactInfo->appendChild($xmlDoc->createElement('ContactPhone',$ContactPhone));
    $ContactInfo->appendChild($xmlDoc->createElement('ContactEmail',$ContactEmail));
    $ContactInfo->appendChild($xmlDoc->createElement('Address1',$Address1));
    $ContactInfo->appendChild($xmlDoc->createElement('Address2',$Address2));
    $ContactInfo->appendChild($xmlDoc->createElement('City',$City));
    $ContactInfo->appendChild($xmlDoc->createElement('State',$State));
    $ContactInfo->appendChild($xmlDoc->createElement('Zip',$Zip));
    $ContactInfo->appendChild($xmlDoc->createElement('County',$County));
    $ContactInfo->appendChild($xmlDoc->createElement('Phone',$Phone));
    $ContactInfo->appendChild($xmlDoc->createElement('Fax',$Fax));
    $ContactInfo->appendChild($xmlDoc->createElement('Website',$Website));
    $AboutSearch = $xmlDoc->createElement('About');
    $Institutions->appendChild($AboutSearch);
    $AboutSearch->appendChild($xmlDoc->createElement('div',$About));
    $Institutions->appendChild($xmlDoc->createElement('CouncilID',$CouncilID));
    #$Institutions->appendChild($xmlDoc->createElement('LogoURL',$LogoURL));
    $Institutions->appendChild($xmlDoc->createElement('ProxyMember',$ProxyMember));
    $DisplayName = CreateDisplayName ($InstitutionName, $ParentOrganization);
    $Institutions->appendChild($xmlDoc->createElement('DisplayName',$DisplayName));

    #Place XML on server
    $xml_data = $xmlDoc->saveXML($xmlDoc->documentElement);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
    $output = curl_exec($ch);
    curl_close($ch);
    #cho "<textarea>" . $xml_data . "</textarea><br><br>";
    echo "Updated " . $InstitutionID . " : <b>" . $DisplayName . "</b><br><br>";
    echo "Changes to the metadata <b>will not</b> appear immediately for the public. Metadata changes will update within 15 minutes.<br><br>";
    ### End adding new organization

  } else {
    echo "Server doing nothing at all!";
  }
} else {
  echo "Parameters not set. Not doing anything.";
}

 ?>
