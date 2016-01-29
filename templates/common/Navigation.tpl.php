<div id="navigation">
	<a class="navigation<?=$_['cat']=='control'?'_active':''?>" href="<?=$_SERVER['PHP_SELF']?>?cat=control">Control</a>
	<a class="navigation<?=$_['cat']=='playlist'?'_active':''?>" href="<?=$_SERVER['PHP_SELF']?>?cat=playlist&amp;page=show<?=$_['playlistAnchor']?>">Playlist</a>
	<a class="navigation<?=$_['cat']=='browse'?'_active':''?>" href="<?=$_SERVER['PHP_SELF']?>?cat=browse&amp;page=lastBrowsePage">Browse</a>
	<a class="navigation<?=$_['cat']=='setup'?'_active':''?>" href="<?=$_SERVER['PHP_SELF']?>?cat=setup">Setup</a>
</div> <!-- navigation -->
