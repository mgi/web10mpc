<div id="header">
	<strong>/</strong>
</div> <!-- header -->

<table cellspacing="0">
<? foreach ($_['ranges'] as $range): ?>
<tr class="<?=$range['cssClass']?>">
	<td><a href="<?=$range['browseUrl']?>"><?=$range['name']?></a></td>
	<td class="right1"><a href="<?=$range['browseUrl']?>"><img src="<?=$_['imagePath']?>/go-next<?=$range['imageClass']?>.png" alt="go"></a></td>
</tr>
<? endforeach; ?>
<tr>
	<td></td>
	<td class="right1"><a href="#navigation"><img src="<?=$_['imagePath']?>/go-top.png" alt="top"></a></td>
</tr>
</table>
