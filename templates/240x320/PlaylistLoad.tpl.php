<div id="header">
	<strong>Load playlist (append to current)</strong>
</div> <!-- header -->

<table cellspacing="0">
<tr>
	<td><a href="<?=$_SERVER['PHP_SELF']?>?cat=playlist"><img src="<?=$_['imagePath']?>/go-previous.png" alt="back"></a></td>
	<td class="right1"></td>
</tr>
<? foreach ($_['playlists'] as $playlist): ?>
<tr class="<?=$playlist['cssClass']?>">
	<td><span class="small"><strong><?=$playlist['pos']?>. <?=$playlist['name']?></strong><br>modified: <?=$playlist['lastModified']?></span></td>
	<td class="right1"><a href="<?=$playlist['loadUrl']?>"><img src="<?=$_['imagePath']?>/document-open<?=$playlist['imageClass']?>.png" alt="open"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right1"><a href="#navigation"><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
