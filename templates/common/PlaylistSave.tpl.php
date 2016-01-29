<div id="header">
	<strong>Save or delete playlist</strong>
</div> <!-- header -->

<table cellspacing="0">
<tr>
	<td class="settings_left"><a href="<?=$_SERVER['PHP_SELF']?>?cat=playlist"><img src="<?=$_['imagePath']?>/go-previous.png" alt="back"></a></td>
	<td class="settings_right"></td>
</tr>
</table>

<? if ($_['result'] != ''): ?>
<p style="color: <?=$_['resultColor']?>;"><?=$_['result']?></p>
<? endif; ?>

<form action="<?=$_SERVER['PHP_SELF']?>" method="get">
<input type="hidden" name="cat" value="playlist">
<input type="hidden" name="page" value="save">
<input type="hidden" name="uid" value="<?=$_['uid']?>">
<input type="hidden" name="action" value="save">
<table cellspacing="0">
<tr>
	<td class="settings_left">Enter new playlist name:</td>
	<td class="settings_right"></td>
</tr>
<tr>
	<td class="settings_left"><input class="textfield" type="text" name="name" maxlength="40" value="<?=$_['name']?>"></td>
	<td class="settings_right"><input class="button" type="submit" value="Save"></td>
</tr>
</table>
</form>

<table cellspacing="0">
<tr>
	<td>Overwrite or delete playlist:</td>
	<td class="right2"></td>
</tr>
<? foreach ($_['playlists'] as $playlist): ?>
<tr class="<?=$playlist['cssClass']?>">
	<td><span class="small"><strong><?=$playlist['pos']?>. <?=$playlist['name']?></strong><br>modified: <?=$playlist['lastModified']?></span></td>
	<td class="right2"><a href="<?=$playlist['overwriteUrl']?>"><img src="<?=$_['imagePath']?>/document-save<?=$playlist['imageClass']?>.png" alt="save"></a><a href="<?=$playlist['deleteUrl']?>"><img src="<?=$_['imagePath']?>/edit-delete<?=$playlist['imageClass']?>.png" alt="delete"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right2"><a href='#navigation'><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
