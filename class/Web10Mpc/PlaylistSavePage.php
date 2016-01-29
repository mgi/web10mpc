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

class PlaylistSavePage extends AbstractPage {
	protected $mpd = NULL;
	protected $mpdHelper = NULL;
	protected $name = '';
	protected $result = '';
	protected $resultColor = 'black';

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
		$this->mpdHelper = new MpdHelper($this->mpd);
	}

	protected function handleRequestEx(array $request) {
		if ((isset($request['uid'])) && ($request['uid'] == $_SESSION['uid'])) {
			if (isset($request['action'])) {
				switch ($request['action']) {
					case 'save':
						if (isset($_GET['name'])) {
							$this->savePlaylist(rawurldecode($_GET['name']), FALSE);
						}

						break;
					case 'overwrite':
						if (isset($_GET['name'])) {
							$this->savePlaylist(rawurldecode($_GET['name']), TRUE);
						}

						break;
					case 'delete':
						if (isset($_GET['name'])) {
							$this->mpd->executeCommand('rm', rawurldecode($_GET['name']));
						}

						break;
				}
			}

			$_SESSION['uid'] = uniqid();
		}
	}

	protected function renderEx($tplPath) {
		$_['cssPath'] = $tplPath . '/css';
		$_['cat'] = 'playlist';
		$status = $this->mpd->executeCommand('status');

		if (isset($status['songid'])) {
			$_['playlistAnchor'] = '#' . $status['songid'];
		} else {
			$_['playlistAnchor'] = '';
		}

		$_['imagePath'] = $tplPath . '/images';
		$_['name'] = htmlspecialchars($this->name);
		$_['result'] = $this->result;
		$_['resultColor'] = $this->resultColor;
		$_['uid'] = $_SESSION['uid'];

		// per playlist:
		// pos, name, lastModified, overwriteUrl, deleteUrl, cssClass, imageClass
		$playlists = $this->mpdHelper->getPlaylists();
		$count = 0;
		$_['playlists'] = array();

		foreach ($playlists as $item) {
			$count++;
			$playlist = array();
			$playlist['pos'] = $count;
			$playlist['name'] = htmlspecialchars($item['playlist']);
			$playlist['lastModified'] =
				Utils\Utils::formatDateISO8601($item['Last-Modified'], TRUE);
			$playlist['overwriteUrl'] = $_SERVER['PHP_SELF'] . '?cat=playlist'
			                          . '&amp;page=save'
			                          . '&amp;uid=' . $_SESSION['uid']
			                          . '&amp;action=overwrite'
			                          . '&amp;name='
			                          . htmlspecialchars(rawurlencode($item['playlist']));
			$playlist['deleteUrl'] = $_SERVER['PHP_SELF'] . '?cat=playlist'
			                       . '&amp;page=save'
			                       . '&amp;uid=' . $_SESSION['uid']
			                       . '&amp;action=delete'
			                       . '&amp;name='
			                       . htmlspecialchars(rawurlencode($item['playlist']));

			if ($count % 2 == 1) {
				$playlist['cssClass'] = 'dark';
				$playlist['imageClass'] = '-dark';
			} else {
				$playlist['cssClass'] = '';
				$playlist['imageClass'] = '';
			}

			$_['playlists'][] = $playlist;
		}

		include('./templates/common/Header.tpl.php');
		include('./templates/common/Navigation.tpl.php');
		include('./templates/common/PlaylistSave.tpl.php');
		include('./templates/common/Footer.tpl.php');
	}

	protected function savePlaylist($name, $overwrite = FALSE) {
		$playlists = $this->mpd->executeCommand('listplaylists');
		$exists = FALSE;

		foreach ($playlists as $item) {
			if ($item['playlist'] == $name) {
				$exists = TRUE;
				break;
			}
		}

		if (!preg_match("/^[A-Za-z0-9_\-\s]{1,40}$/", $name)) {
			$this->name = $name;
			$this->result = 'Error: illegal filename!';
			$this->resultColor = 'red';
			return;
		}

		if ($exists) {
			if ($overwrite) {
				$this->mpd->executeCommand('rm', $name);
			} else {
				$this->name = $name;
				$this->result = 'Error: file exists!';
				$this->resultColor = 'red';
				return;
			}
		}

		$this->mpd->executeCommand('save', $name);
		$this->name = '';
		$this->result = 'Playlist saved.';
		$this->resultColor = 'green';
	}
}
?>
