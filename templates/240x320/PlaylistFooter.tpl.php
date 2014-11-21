</div> <!-- main -->

<script type="text/javascript">
	// Variables set via PHP.
	var reloadTimeout = <?=$_['reloadTimeout']?>;
	var trackLength = <?=$_['length']?>;
	var trackPosition = <?=$_['position']?>;
<? if ($_['playing']): ?>
	var playing = true;
<? else: ?>
	var playing = false;
<? endif; ?>

	function reloadPage() {
		window.location.href = "<?=$_['reloadUrl']?>";
		window.location.reload(true);
	}

	if (playing) {
		// Schedule page reload after remaining track time plus x seconds.
		setTimeout("reloadPage()", (trackLength - trackPosition + reloadTimeout) * 1000);
	}
</script>

</body>

</html>
