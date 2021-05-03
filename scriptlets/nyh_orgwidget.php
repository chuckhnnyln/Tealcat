<?php
if (isset($_GET['alias'])) {
  $alias = $_GET['alias'];
};
?>
<script type="text/javascript">
    function goToPage() {
        var page = document.getElementById('SearchTerm').value;
window.location = "https://cdm16694.contentdm.oclc.org/digital/search/collection/<?php echo $alias; ?>/searchterm/" + SearchTerm.value + "/order/relevancy";
    }
</script>
<?php
echo "<input id='SearchTerm' type='text' placeholder='Search the Collections' size='50'><input type=Submit value=Go onclick='goToPage();'>";
?>
