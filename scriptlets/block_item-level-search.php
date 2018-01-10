<script type="text/javascript">
  function goToPage() {
    var page = document.getElementById('SearchTerm').value
    window.location = "http://cdm16694.contentdm.oclc.org/cdm/search/searchterm/" + SearchTerm.value + "/field/all/mode/all/conn/and/cosuppress/";
  }
function searchKeyPress(e) {
  // look for window.event in case event isn't passed in
  e = e || window.event;
  if (e.keyCode == 13)
  {
    document.getElementById('btnSearch').click();
    return false;
  }
  return true;
}
</script>
<?php
#This code is used on the frontpage in a block. It displays a item-level search box that passes the request to ContentDM.
echo "<div class=search_box>";
echo "<input id='SearchTerm' type='text' placeholder='Search Digital Objects' size='50' onkeypress='return searchKeyPress(event);'>";
echo "<input type=Submit id='btnSearch' value='Go!' onclick='goToPage();'>";
echo "<div style='clear: both;'></div>";
echo "<a href='https://cdm16694.contentdm.oclc.org/digital/search/advanced/'>Advanced Search</a>";
echo "</div>";
?>
