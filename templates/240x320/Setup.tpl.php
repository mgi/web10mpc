<form action="<?=$_SERVER['PHP_SELF']?>" method="get">
<input type="hidden" name="cat" value="setup">
<input type="hidden" name="uid" value="<?=$_['uid']?>">
<input type="hidden" name="action" value="set">
<table cellspacing="0">
<tr>
	<td class="settings_left">Browse mode:</td>
	<td class="settings_right">
		<select name="browseMode">
<? foreach ($_['browseOptions'] as $option): ?>
<? if ($option['selected']): ?>
			<option selected="selected"><?=$option['value']?></option>
<? else: ?>
			<option><?=$option['value']?></option>
<? endif; ?>
<? endforeach; ?>
		</select>
	</td>
</tr>
<tr>
	<td class="settings_left">Repeat playlist:</td>
	<td class="settings_right">
		<select name="repeat">
<? foreach ($_['repeatOptions'] as $option): ?>
<? if ($option['selected']): ?>
			<option selected="selected"><?=$option['value']?></option>
<? else: ?>
			<option><?=$option['value']?></option>
<? endif; ?>
<? endforeach; ?>
		</select>
	</td>
</tr>
<tr>
	<td class="settings_left">Seek interval (seconds):</td>
	<td class="settings_right">
		<select name="seekInterval">
<? foreach ($_['seekOptions'] as $option): ?>
<? if ($option['selected']): ?>
			<option selected="selected"><?=$option['value']?></option>
<? else: ?>
			<option><?=$option['value']?></option>
<? endif; ?>
<? endforeach; ?>
		</select>
	</td>
</tr>
<tr>
	<td class="settings_left">Sort albums by:</td>
	<td class="settings_right">
		<select name="sortMode">
<? foreach ($_['sortOptions'] as $option): ?>
<? if ($option['selected']): ?>
			<option selected="selected"><?=$option['value']?></option>
<? else: ?>
			<option><?=$option['value']?></option>
<? endif; ?>
<? endforeach; ?>
		</select>
	</td>
</tr>
<tr>
	<td class="settings_left">Web1.0MPC layout:</td>
	<td class="settings_right">
		<select name="layout">
<? foreach ($_['layoutOptions'] as $option): ?>
<? if ($option['selected']): ?>
			<option selected="selected"><?=$option['value']?></option>
<? else: ?>
			<option><?=$option['value']?></option>
<? endif; ?>
<? endforeach; ?>
		</select>
	</td>
</tr>
<tr>
	<td class="spacer"></td>
	<td class="spacer"></td>
</tr>
<tr>
	<td class="settings_left"></td>
	<td class="settings_right"><input class="button" type="submit" value="Save"></td>
</tr>
<tr>
	<td class="spacer"></td>
	<td class="spacer"></td>
</tr>
<tr>
	<td class="settings_left">Update database</td>
	<td class="settings_right"><a href="<?=$_['updateUrl']?>"><img src="<?=$_['imagePath']?>/go-next.png" alt="go"></a></td>
</tr>
</table>
</form>

<p class="small_centered">.....</p>

<p class="small"><strong>Statistics:</strong></p>

<table cellspacing="0">
<tr>
	<td class="stats_left">Uptime:</td>
	<td class="stats_right"><?=$_['uptime']?></td>
</tr>
<tr>
	<td class="stats_left">Playtime:</td>
	<td class="stats_right"><?=$_['playtime']?></td>
</tr>
<tr>
	<td class="spacer"></td>
	<td class="spacer"></td>
</tr>
<tr>
	<td class="stats_left">Artists:</td>
	<td class="stats_right"><?=$_['artists']?></td>
</tr>
<tr>
	<td class="stats_left">Albums:</td>
	<td class="stats_right"><?=$_['albums']?></td>
</tr>
<tr>
	<td class="stats_left">Songs:</td>
	<td class="stats_right"><?=$_['songs']?></td>
</tr>
<tr>
	<td class="spacer"></td>
	<td class="spacer"></td>
</tr>
<tr>
	<td class="stats_left">DB playtime:</td>
	<td class="stats_right"><?=$_['databasePlaytime']?></td>
</tr>
<tr>
	<td class="stats_left">DB updated:</td>
	<td class="stats_right"><?=$_['databaseUpdated']?></td>
</tr>
</table>

<p class="small_centered">.....</p>

<p class="small"><strong>Version information:</strong></p>

<table cellspacing="0">
<tr>
	<td class="stats_left">MPD Protocol:</td>
	<td class="stats_right"><?=$_['mpdProtocolVersion']?></td>
</tr>
<tr>
	<td class="stats_left">Web1.0MPC:</td>
	<td class="stats_right"><?=$_['version']?></td>
</tr>
</table>
