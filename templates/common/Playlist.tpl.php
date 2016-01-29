<table cellspacing="0">
<tr>
	<td><a href="<?=$_SERVER['PHP_SELF']?>?cat=playlist&amp;page=load"><img src="<?=$_['imagePath']?>/document-open.png" alt="open"></a>&nbsp;<a href="<?=$_SERVER['PHP_SELF']?>?cat=playlist&amp;page=save"><img src="<?=$_['imagePath']?>/document-save.png" alt="save"></a></td>
	<td class="right2"><a href="<?=$_['clearUrl']?>"><img src="<?=$_['imagePath']?>/edit-clear.png" alt="clear"></a></td>
</tr>
<? foreach ($_['songs'] as $song): ?>
<tr class="<?=$song['cssClass']?>" id="<?=$song['id']?>">
	<td><span class="small"><strong><?=$song['pos']?>. <?=$song['title']?></strong><br><?=$song['info']?></span></td>
	<td class="right2"><a href="<?=$song['skipToUrl']?>"><img src="<?=$_['imagePath']?>/media-playback-start<?=$song['imageClass']?>.png" alt="play"></a><a href="<?=$song['removeUrl']?>"><img src="<?=$_['imagePath']?>/list-remove<?=$song['imageClass']?>.png" alt="remove"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right2"><a href="#navigation"><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
