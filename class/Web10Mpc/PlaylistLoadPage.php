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

class PlaylistLoadPage extends AbstractPage {
	protected $mpd = NULL;
	protected $mpdHelper = NULL;

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
		$this->mpdHelper = new MpdHelper($this->mpd);
	}

	protected function handleRequestEx(array $request) {
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

		// per playlist:
		// pos, name, lastModified, loadUrl, cssClass, imageClass
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
			$playlist['loadUrl'] = $_SERVER['PHP_SELF'] . '?cat=playlist'
			                     . '&amp;uid=' . $_SESSION['uid']
			                     . '&amp;action=load'
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
		include('./templates/common/PlaylistLoad.tpl.php');
		include('./templates/common/Footer.tpl.php');
	}
}
?>
