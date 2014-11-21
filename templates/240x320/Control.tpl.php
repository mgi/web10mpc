<div id="controls">
	<a href="<?=$_['skipBwUrl']?>"><img src="<?=$_['imagePath']?>/media-skip-backward-big.png" alt="skip bw"></a>
	<a href="<?=$_['seekBwUrl']?>"><img src="<?=$_['imagePath']?>/media-seek-backward-big.png" alt="seek bw"></a>
<? if ($_['playing']): ?>
	<a href="<?=$_['pauseUrl']?>"><img src="<?=$_['imagePath']?>/media-playback-pause-big.png" alt="pause"></a>
<? else: ?>
	<a href="<?=$_['playUrl']?>"><img src="<?=$_['imagePath']?>/media-playback-start-big.png" alt="play"></a>
<? endif; ?>
	<a href="<?=$_['stopUrl']?>"><img src="<?=$_['imagePath']?>/media-playback-stop-big.png" alt="stop"></a>
	<a href="<?=$_['seekFwUrl']?>"><img src="<?=$_['imagePath']?>/media-seek-forward-big.png" alt="seek fw"></a>
	<a href="<?=$_['skipFwUrl']?>"><img src="<?=$_['imagePath']?>/media-skip-forward-big.png" alt="skip fw"></a>
</div> <!-- controls -->

<? if ($_['showVolumeControls']): ?>
<div id="volume">
	<a href="<?=$_['volDownUrl']?>"><img class="middle" src="<?=$_['imagePath']?>/list-remove.png" alt="vol -"></a>
	vol.: <?=$_['volume']?>%
	<a href="<?=$_['volUpUrl']?>"><img class="middle" src="<?=$_['imagePath']?>/list-add.png" alt="vol +"></a>
</div> <!-- volume -->
<? endif; ?>

<div id="info">
	<p><span class="small"><?=$_['state']?></span></p>
<? if (!$_['stopped']): ?>
	<p><strong><?=$_['title']?></strong><br>
		<strong><span class="small"><?=$_['progress']?></span></strong><br>
		<span class="small"><?=$_['info']?></span></p>
<? endif; ?>
</div> <!-- info -->

<? if (!$_['stopped']): ?>
<div id="cover">
	<img class="cover" src="<?=$_['coverPath']?>" alt="cover">
</div> <!-- cover -->
<? endif; ?>
