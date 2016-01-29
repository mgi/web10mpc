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
use Web10Mpc\Utils;

class BrowseAlbumPage extends AbstractPage {
	protected $album = '';
	protected $artist = '';
	protected $mpd = NULL;
	protected $mpdHelper = NULL;

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
		$this->mpdHelper = new MpdHelper($this->mpd);

		if (isset($_SESSION['album'])) {
			$this->album = $_SESSION['album'];
		}

		if (isset($_SESSION['artist'])) {
			$this->artist = $_SESSION['artist'];
		}
	}

	protected function handleRequestEx(array $request) {
		$_SESSION['lastBrowsePage'] = 'album';

		if (isset($request['album'])) {
			$this->album = rawurldecode($request['album']);
			$_SESSION['album'] = $this->album;
		}

		if (isset($request['artist'])) {
			$this->artist = rawurldecode($request['artist']);
			$_SESSION['artist'] = $this->artist;
		}

		if ((isset($request['uid'])) && ($request['uid'] == $_SESSION['uid'])) {
			if (isset($request['action'])) {
				switch ($request['action']) {
					case 'add':
						if (isset($request['file'])) {
							$this->mpd->executeCommand('add', rawurldecode($request['file']));
						}

						break;
					case 'addAll':
						if (isset($request['artist']) && isset($request['album'])) {
							$songs = $this->mpdHelper->getSongsByArtistAndAlbum(
								rawurldecode($request['artist']),
								rawurldecode($request['album']));
							$this->mpd->beginCommandList();

							foreach ($songs as $song) {
								$this->mpd->enqueueCommand('add', $song['file']);
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
		$_['album'] = htmlspecialchars($this->album);
		$_['artist'] = htmlspecialchars($this->artist);
		$_['backUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=artist';
		$_['homeUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=root';
		$_['addAllUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=album'
		                . '&amp;uid=' . $_SESSION['uid']
		                . '&amp;action=addAll'
		                . '&amp;artist='
		                . htmlspecialchars(rawurlencode($this->artist))
		                . '&amp;album='
		                . htmlspecialchars(rawurlencode($this->album));

		// per song: track, title, time, addUrl, cssClass, imageClass
		$songs = $this->mpdHelper->getSongsByArtistAndAlbum($this->artist,
			$this->album);
		$count = 0;
		$_['songs'] = array();

		foreach ($songs as $item) {
			$count++;
			$song = array();
			$song['track'] = htmlspecialchars($item['Track']);
			$song['title'] = htmlspecialchars($item['Title']);
			$song['time'] = Utils\Utils::formatTimeShort($item['Time']);
			$song['addUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			                . '&amp;page=album'
			                . '&amp;uid=' . $_SESSION['uid']
			                . '&amp;action=add'
			                . '&amp;file='
			                . htmlspecialchars(rawurlencode($item['file']));

			if ($count % 2 == 1) {
				$song['cssClass'] = 'dark';
				$song['imageClass'] = '-dark';
			} else {
				$song['cssClass'] = '';
				$song['imageClass'] = '';
			}

			$_['songs'][] = $song;
		}

		include('./templates/common/Header.tpl.php');
		include('./templates/common/Navigation.tpl.php');
		include('./templates/common/BrowseAlbum.tpl.php');
		include('./templates/common/Footer.tpl.php');
	}
}
?>
