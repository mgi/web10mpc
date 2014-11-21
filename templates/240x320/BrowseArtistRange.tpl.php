<div id="header">
	<strong><?=$_['artistRange']?></strong>
</div> <!-- header -->

<table cellspacing="0">
<tr class="">
	<td><a href="<?=$_['backUrl']?>"><img src="<?=$_['imagePath']?>/go-previous.png" alt="back"></a><img src="<?=$_['imagePath']?>/spacer.png" alt="spacer"><a href="<?=$_['homeUrl']?>"><img src="<?=$_['imagePath']?>/go-home.png" alt="home"></a></td>
	<td class="right1"></td>
</tr>
<? foreach ($_['artists'] as $artist): ?>
<tr class="<?=$artist['cssClass']?>">
	<td><a href="<?=$artist['browseUrl']?>"><?=$artist['name']?></a></td>
	<td class="right1"><a href="<?=$artist['browseUrl']?>"><img src="<?=$_['imagePath']?>/go-next<?=$artist['imageClass']?>.png" alt="go"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right1"><a href="#navigation"><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
