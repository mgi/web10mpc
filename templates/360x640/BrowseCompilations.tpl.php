<div id="header">
	<strong><?=$_['range']?></strong>
</div> <!-- header -->

<table cellspacing="0">
<tr class="">
	<td><a href="<?=$_['backUrl']?>"><img src="<?=$_['imagePath']?>/go-previous.png" alt="back"></a><img src="<?=$_['imagePath']?>/spacer.png" alt="spacer"><a href="<?=$_['homeUrl']?>"><img src="<?=$_['imagePath']?>/go-home.png" alt="home"></a></td>
	<td class="right1"></td>
</tr>
<? foreach ($_['compilations'] as $compilation): ?>
<tr class="<?=$compilation['cssClass']?>">
	<td><span class='small'><strong><a href="<?=$compilation['browseUrl']?>"><?=$compilation['name']?></a></strong><br><span>(</span><?=$compilation['date']?><span>)</span></span></td>
	<td class="right1"><a href="<?=$compilation['browseUrl']?>"><img src="<?=$_['imagePath']?>/go-next<?=$compilation['imageClass']?>.png" alt="go"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right1"><a href="#navigation"><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
