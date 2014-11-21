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

class BrowseCompilationPage extends AbstractPage {
	protected $compilation = '';
	protected $mpd = NULL;
	protected $mpdHelper = NULL;

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
		$this->mpdHelper = new MpdHelper($this->mpd);

		if (isset($_SESSION['compilation'])) {
			$this->compilation = $_SESSION['compilation'];
		}
	}

	protected function handleRequestEx(array $request) {
		$_SESSION['lastBrowsePage'] = 'compilation';

		if (isset($request['compilation'])) {
			$this->compilation = rawurldecode($request['compilation']);
			$_SESSION['compilation'] = $this->compilation;
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
						if (isset($request['compilation'])) {
							$songs = $this->mpdHelper->getSongsByCompilation(
								rawurldecode($request['compilation']));
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
		$_['compilation'] = htmlspecialchars($this->compilation);
		$_['backUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
		              . '&amp;page=compilations';
		$_['homeUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=root';
		$_['addAllUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
		                . '&amp;page=compilation'
		                . '&amp;uid=' . $_SESSION['uid']
		                . '&amp;action=addAll'
		                . '&amp;compilation='
		                . htmlspecialchars(rawurlencode($this->compilation));

		// per song: track, artist, title, length, addUrl, cssClass, imageClass
		$songs = $this->mpdHelper->getSongsByCompilation($this->compilation);
		$count = 0;
		$_['songs'] = array();

		foreach ($songs as $item) {
			$count++;
			$song = array();
			$song['track'] = htmlspecialchars($item['Track']);
			$song['artist'] = htmlspecialchars($item['Artist']);
			$song['title'] = htmlspecialchars($item['Title']);
			$song['time'] = Utils\Utils::formatTimeShort($item['Time']);
			$song['addUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			                . '&amp;page=compilation'
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

		include($tplPath . '/Header.tpl.php');
		include($tplPath . '/Navigation.tpl.php');
		include($tplPath . '/BrowseCompilation.tpl.php');
		include($tplPath . '/Footer.tpl.php');
	}
}
?>
