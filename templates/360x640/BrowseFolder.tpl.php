<div id="header">
	<strong><?=$_['folder']?></strong>
</div> <!-- header -->

<table cellspacing="0">
<tr class="">
<? if ($_['folder'] == '/'): ?>
	<td></td>
<? else: ?>
	<td><a href="<?=$_['backUrl']?>"><img src="<?=$_['imagePath']?>/go-previous.png" alt="back"></a><img src="<?=$_['imagePath']?>/spacer.png" alt="spacer"><a href="<?=$_['homeUrl']?>"><img src="<?=$_['imagePath']?>/go-home.png" alt="home"></a></td>
<? endif; ?>
	<td class="right1"><a href="<?=$_['addAllUrl']?>"><img src="<?=$_['imagePath']?>/list-add.png" alt="add all"></a></td>
</tr>
<? foreach ($_['directories'] as $directory): ?>
<tr class="<?=$directory['cssClass']?>">
	<td><a href="<?=$directory['openUrl']?>"><?=$directory['name']?></a></td>
	<td class="right1"><a href="<?=$directory['openUrl']?>"><img src="<?=$_['imagePath']?>/go-next<?=$directory['imageClass']?>.png" alt="go"></a></td>
</tr>
<? endforeach; ?>
<? foreach ($_['files'] as $file): ?>
<tr class="<?=$file['cssClass']?>">
	<td><span class="small"><strong><a href="<?=$file['addUrl']?>"><?=$file['name']?></a></strong><br><?=$file['time']?></span></td>
	<td class="right1"><a href="<?=$file['addUrl']?>"><img src="<?=$_['imagePath']?>/list-add<?=$file['imageClass']?>.png" alt="add"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right1"><a href="#navigation"><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
