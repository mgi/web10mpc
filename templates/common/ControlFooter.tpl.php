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
	}

	function updateTrackPosition() {
		if (trackPosition < trackLength) {
			trackPosition = trackPosition + 1;

			if (document.getElementById) {
				document.getElementById("elapsed").innerHTML = formatTime(trackPosition);
			}
		}
	}

	function formatTime(seconds) {
		// Format the given time in seconds as mm:ss (197 --> 3:17).
		return Math.floor(seconds / 60) + ":" + ((seconds % 60) < 10 ? "0" : "") + (seconds % 60);
	}

	if (playing) {
		if (trackLength < trackPosition) {
			trackLength = trackPosition;
		}

		// Schedule page reload after remaining track time plus x seconds.
		setTimeout("reloadPage()", (trackLength - trackPosition + reloadTimeout) * 1000);
		// Update elapsed track time every second.
		setInterval("updateTrackPosition()", 1000);
	}
</script>

</body>

</html>
