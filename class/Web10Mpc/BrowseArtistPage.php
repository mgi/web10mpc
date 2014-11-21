<?php
/* Web1.0MPC - a web based remote control for MPD (Music Player Daemon)
 * Copyright (C) 2011-2014  Marcus Geuecke (web10mpc [at] geuecke [dot] org)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace Web10Mpc;
use Web10Mpc\Mpd;

class BrowseArtistPage extends AbstractPage {
	protected $artist = '';
	protected $mpd = NULL;
	protected $mpdHelper = NULL;
	protected $sortMode = 'date';

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
		$this->mpdHelper = new MpdHelper($this->mpd);

		if (isset($_SESSION['artist'])) {
			$this->artist = $_SESSION['artist'];
		}

		if (isset($_COOKIE['sortMode'])) {
			if ($_COOKIE['sortMode'] == 'name') {
				$this->sortMode = 'name';
			} else {
				$this->sortMode = 'date';
			}
		}
	}

	protected function handleRequestEx(array $request) {
		$_SESSION['lastBrowsePage'] = 'artist';

		if (isset($request['artist'])) {
			$this->artist = rawurldecode($request['artist']);
			$_SESSION['artist'] = $this->artist;
		}

		if ((isset($request['uid'])) && ($request['uid'] == $_SESSION['uid'])) {
			if (isset($request['action'])) {
				switch ($request['action']) {
					case 'addAll':
						if (isset($request['artist'])) {
							$albums = $this->mpdHelper->getAlbumsByArtist(
								rawurldecode($request['artist']), $this->sortMode);
							$this->mpd->beginCommandList();

							foreach ($albums as $album) {
								$songs = $this->mpdHelper->getSongsByArtistAndAlbum(
									rawurldecode($request['artist']), $album['Album']);

								foreach ($songs as $song) {
									$this->mpd->enqueueCommand('add', $song['file']);
								}
							}

							$this->mpd->endCommandList();
						}

						break;
				}
			}

			$_SESSION['uid'] = uniqid();
		}
	}

	protected function renderEx($tplPath) {
		$_['cssPath'] = $tplPath . '/css';
		$_['cat'] = 'browse';
		$status = $this->mpd->executeCommand('status');

		if (isset($status['songid'])) {
			$_['playlistAnchor'] = '#' . $status['songid'];
		} else {
			$_['playlistAnchor'] = '';
		}

		$_['imagePath'] = $tplPath . '/images';
		$_['artist'] = htmlspecialchars($this->artist);
		$_['backUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
		              . '&amp;page=artistRange';
		$_['homeUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=root';
		$_['addAllUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=artist'
		                . '&amp;uid=' . $_SESSION['uid']
		                . '&amp;action=addAll'
		                . '&amp;artist='
		                . htmlspecialchars(rawurlencode($this->artist));

		// per album: name, date, browseUrl, cssClass, imageClass
		$albums = $this->mpdHelper->getAlbumsByArtist($this->artist,
			$this->sortMode);
		$count = 0;
		$_['albums'] = array();

		foreach ($albums as $item) {
			$count++;
			$album = array();
			$album['name'] = htmlspecialchars($item['Album']);
			$album['date'] = htmlspecialchars($item['Date']);
			$album['browseUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			                    . '&amp;page=album'
			                    . '&amp;artist='
			                    . htmlspecialchars(rawurlencode($this->artist))
			                    . '&amp;album='
			                    . htmlspecialchars(rawurlencode($item['Album']));

			if ($count % 2 == 1) {
				$album['cssClass'] = 'dark';
				$album['imageClass'] = '-dark';
			} else {
				$album['cssClass'] = '';
				$album['imageClass'] = '';
			}

			$_['albums'][] = $album;
		}

		include($tplPath . '/Header.tpl.php');
		include($tplPath . '/Navigation.tpl.php');
		include($tplPath . '/BrowseArtist.tpl.php');
		include($tplPath . '/Footer.tpl.php');
	}
}
?>
