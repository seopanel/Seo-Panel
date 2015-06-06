<script>
	updateInnerHtml('checked_count', '<?php echo $checkedCount?>');
	updateInnerHtml('active_count', '<?php echo $activeCount?>');
	updateInnerHtml('inactive_count', '<?php echo $inActiveCount?>');
</script>
<?php
if (count($proxyList)) {
	$proxyInfo = $proxyList[0];
	$argumentVar = isset($status) ? "&status=$status" : "";
	$argumentVar .= empty($proxyMaxId) ? "" : "&proxy_max_id=$proxyMaxId";
    ?>
	<script>
		scriptDoLoad('proxy.php?sec=runcheckstatus&id=<?php echo $proxyInfo['id']?><?php echo $argumentVar?>', 'subcontmed');
    </script>
	<?php
} else {
	showSuccessMsg($spTextSA['Completed project execution'], false);
}
?>
