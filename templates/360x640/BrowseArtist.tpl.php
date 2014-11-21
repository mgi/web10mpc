<div id="header">
	<strong><?=$_['artist']?></strong>
</div> <!-- header -->

<table cellspacing="0">
<tr class="">
	<td><a href="<?=$_['backUrl']?>"><img src="<?=$_['imagePath']?>/go-previous.png" alt="back"></a><img src="<?=$_['imagePath']?>/spacer.png" alt="spacer"><a href="<?=$_['homeUrl']?>"><img src="<?=$_['imagePath']?>/go-home.png" alt="home"></a></td>
	<td class="right1"><a href="<?=$_['addAllUrl']?>"><img src='<?=$_['imagePath']?>/list-add.png' alt='add all'></a></td>
</tr>
<? foreach ($_['albums'] as $album): ?>
<tr class="<?=$album['cssClass']?>">
	<td><span class='small'><strong><a href="<?=$album['browseUrl']?>"><?=$album['name']?></a></strong><br><span>(</span><?=$album['date']?><span>)</span></span></td>
	<td class="right1"><a href="<?=$album['browseUrl']?>"><img src="<?=$_['imagePath']?>/go-next<?=$album['imageClass']?>.png" alt="go"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right1"><a href="#navigation"><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
