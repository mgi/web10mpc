<div id="header">
	<strong>Various artists: <?=$_['compilation']?></strong>
</div> <!-- header -->

<table cellspacing="0">
<tr class="">
	<td><a href="<?=$_['backUrl']?>"><img src="<?=$_['imagePath']?>/go-previous.png" alt="back"></a><img src="<?=$_['imagePath']?>/spacer.png" alt="spacer"><a href="<?=$_['homeUrl']?>"><img src="<?=$_['imagePath']?>/go-home.png" alt="home"></a></td>
	<td class="right1"><a href="<?=$_['addAllUrl']?>"><img src='<?=$_['imagePath']?>/list-add.png' alt='add all'></a></td>
</tr>
<? foreach ($_['songs'] as $song): ?>
<tr class="<?=$song['cssClass']?>">
	<td><span class='small'><strong><?=$song['track']?>. <?=$song['title']?></strong><br>by <?=$song['artist']?> <span>(</span><?=$song['time']?><span>)</span></span></td>
	<td class="right1"><a href="<?=$song['addUrl']?>"><img src="<?=$_['imagePath']?>/list-add<?=$song['imageClass']?>.png" alt="go"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right1"><a href="#navigation"><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
