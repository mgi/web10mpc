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

class PlaylistPage extends AbstractPage {
	protected $mpd = NULL;

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
	}

	protected function handleRequestEx(array $request) {
		if ((isset($request['uid'])) && ($request['uid'] == $_SESSION['uid'])) {
			if (isset($request['action'])) {
				switch ($request['action']) {
					case 'clear':
						$this->mpd->executeCommand('clear');
						break;
					case 'remove':
						if (isset($request['id'])) {
							$this->mpd->executeCommand('deleteid', $request['id']);
						}

						break;
					case 'load':
						if (isset($request['name'])) {
							$this->mpd->executeCommand('load',
								rawurldecode($request['name']));
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
		$_['clearUrl'] = $_SERVER['PHP_SELF'] . '?cat=playlist'
			             . '&amp;uid=' . $_SESSION['uid']
			             . '&amp;action=clear';

		// per song:
		// pos, id, title, info, skipToUrl, removeUrl, cssClass, imageClass
		$playlist = $this->mpd->executeCommand('playlistinfo');
		$count = 0;
		$_['songs'] = array();

		foreach ($playlist as $item) {
			$count++;
			$song = array();
			$song['pos'] = $item['Pos'] + 1;
			$song['id'] = $item['Id'];

			if (isset($item['Title'])) {
				$song['title'] = htmlspecialchars($item['Title']);
			} else {
				$pathInfo = pathinfo($item['file']);
				$song['title'] = htmlspecialchars($pathInfo['filename']);
			}

			$song['info'] = 'by ';

			if (isset($item['Artist'])) {
				$song['info'] .= htmlspecialchars($item['Artist']);
			} else {
				$song['info'] .= 'Unknown Artist';
			}

			$song['info'] .= ' from ';

			if (isset($item['Album'])) {
				$song['info'] .= htmlspecialchars($item['Album']);
			} else {
				$song['info'] .= 'Unknown Album';
			}

			$song['skipToUrl'] = $_SERVER['PHP_SELF'] . '?cat=control'
			                   . '&amp;uid=' . $_SESSION['uid']
			                   . '&amp;action=skipTo&amp;id=' . $item['Id'];
			$song['removeUrl'] = $_SERVER['PHP_SELF'] . '?cat=playlist'
			                   . '&amp;uid=' . $_SESSION['uid']
			                   . '&amp;action=remove&amp;id=' . $item['Id'];

			if (($status['state'] != 'stop') && ($status['songid'] == $item['Id'])){
				$song['cssClass'] = 'playing';
				$song['imageClass'] = '-active';
			} elseif ($count % 2 == 1) {
				$song['cssClass'] = 'dark';
				$song['imageClass'] = '-dark';
			} else {
				$song['cssClass'] = '';
				$song['imageClass'] = '';
			}

			$_['songs'][] = $song;
		}

		$_['reloadUrl'] = $_SERVER['PHP_SELF'] . '?cat=playlist';
		$_['reloadTimeout'] = Config::RELOAD_TIMEOUT;

		if ($status['state'] == 'play') {
			$_['playing'] = TRUE;
			list($_['position'], $_['length']) = explode(':', $status['time']);

			if (isset($status['nextsongid'])) {
				$_['reloadUrl'] = $_SERVER['PHP_SELF']
				                . '?cat=playlist#' . $status['nextsongid'];
			}
		} else {
			$_['playing'] = FALSE;
			$_['position'] = 0;
			$_['length'] = 0;
		}

		include($tplPath . '/Header.tpl.php');
		include($tplPath . '/Navigation.tpl.php');
		include($tplPath . '/Playlist.tpl.php');
		include($tplPath . '/PlaylistFooter.tpl.php');
	}
}
?>
