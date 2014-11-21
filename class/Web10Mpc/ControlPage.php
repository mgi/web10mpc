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

class ControlPage extends AbstractPage {
	protected $mpd = NULL;
	protected $thumbsCache = NULL;

	public function __construct(Mpd\Mpd $mpd, Utils\ThumbnailCache $thumbsCache) {
		$this->mpd = $mpd;
		$this->thumbsCache = $thumbsCache;
	}

	protected function handleRequestEx(array $request) {
		if ((isset($request['uid'])) && ($request['uid'] == $_SESSION['uid'])) {
			if (isset($request['action'])) {
				switch ($request['action']) {
					case 'skipBw':
						$this->mpd->executeCommand('previous');
						break;
					case 'seekBw':
						$this->Seek($request['action']);
						break;
					case 'play':
						$this->mpd->executeCommand('play');
						break;
					case 'pause':
						$status = $this->mpd->executeCommand('status');

						if ($status['state'] == 'pause') {
							$this->mpd->executeCommand('pause', 0);
						} else {
							$this->mpd->executeCommand('pause', 1);
						}

						break;
					case 'stop':
						$this->mpd->executeCommand('stop');
						break;
					case 'seekFw':
						$this->Seek($request['action']);
						break;
					case 'skipFw':
						$this->mpd->executeCommand('next');
						break;
					case 'volUp':
						$status = $this->mpd->executeCommand('status');
						$vol = $status['volume'] + 10;
						$vol = $vol > 100 ? 100 : $vol;
						$this->mpd->executeCommand('setvol', $vol);
						break;
					case 'volDown':
						$status = $this->mpd->executeCommand('status');
						$vol = $status['volume'] - 10;
						$vol = $vol < 0 ? 0 : $vol;
						$this->mpd->executeCommand('setvol', $vol);
						break;
					case 'skipTo':
						if (isset($request['id'])) {
							$this->mpd->executeCommand('playid', $request['id']);
						}

						break;
				}
			}

			$_SESSION['uid'] = uniqid();
		}
	}

	protected function renderEx($tplPath) {
		$_['cssPath'] = $tplPath . '/css';
		$_['cat'] = 'control';
		$status = $this->mpd->executeCommand('status');

		if (isset($status['songid'])) {
			$_['playlistAnchor'] = '#' . $status['songid'];
		} else {
			$_['playlistAnchor'] = '';
		}

		$_['imagePath'] = $tplPath . '/images';
		$actionUrl = $_SERVER['PHP_SELF']
		           . '?cat=control'
		           . '&amp;uid=' . $_SESSION['uid']
		           . '&amp;action=';
		$_['skipBwUrl'] = $actionUrl . 'skipBw';
		$_['seekBwUrl'] = $actionUrl . 'seekBw';
		$_['playUrl'] = $actionUrl . 'play';
		$_['pauseUrl'] = $actionUrl . 'pause';
		$_['stopUrl'] = $actionUrl . 'stop';
		$_['seekFwUrl'] = $actionUrl . 'seekFw';
		$_['skipFwUrl'] = $actionUrl . 'skipFw';
		$_['volUpUrl'] = $actionUrl . 'volUp';
		$_['volume'] = $status['volume'];
		$_['volDownUrl'] = $actionUrl . 'volDown';

		switch ($status['state']) {
			case 'stop':
				$_['playing'] = FALSE;
				$_['stopped'] = TRUE;
				$_['state'] = '(stopped)';
				break;
			case 'play':
				$_['playing'] = TRUE;
				$_['stopped'] = FALSE;
				$_['state'] = 'now playing:';
				break;
			case 'pause':
				$_['playing'] = FALSE;
				$_['stopped'] = FALSE;
				$_['state'] = 'paused:';
				break;
		}

		$_['showVolumeControls'] = Config::SHOW_VOLUME_CONTROLS;

		if ($status['state'] != 'stop') {
			$song = $this->mpd->executeCommand('currentsong');
			$pathInfo = pathinfo($song['file']);

			if (isset($song['Title'])) {
				$_['title'] = htmlspecialchars($song['Title']);
			} else {
				$_['title'] = htmlspecialchars($pathInfo['filename']);
			}

			list($_['position'], $_['length']) = explode(':', $status['time']);
			$_['progress'] = '(<span id="elapsed">'
			               . Utils\Utils::formatTimeShort($_['position'])
			               . '</span>/'
			               . Utils\Utils::formatTimeShort($_['length'])
			               . ')';
			$_['info'] = 'by ';

			if (isset($song['Artist'])) {
				$_['info'] .= htmlspecialchars($song['Artist']);
			} else {
				$_['info'] .= 'Unknown Artist';
			}

			$_['info'] .= ' from ';

			if (isset($song['Album'])) {
				$_['info'] .= htmlspecialchars($song['Album']);
			} else {
				$_['info'] .= 'Unknown Album';
			}

			if (isset($song['Date'])) {
				$_['info'] .= ' <span>(</span>' . $song['Date'] . '<span>)</span>';
			}

			// Build path to cover image.
			//
			// $pathInfo['dirname'] is the path of the current track without the
			// filename relative to the MPD root (Config::MUSIC_ROOT). Example:
			//
			// Config::MUSIC_ROOT  : /home/user/music
			// $song['file']       : low/2011-c'mon/03-witches.flac
			// $pathInfo['dirname']: low/2011-c'mon
			// Config::COVER_NAME  : folder.jpg
			//
			// --> $tmpPath        : /home/user/music/low/2011-c'mon/folder.jpg
			$tmpPath = Config::MUSIC_ROOT . '/'
			         . $pathInfo['dirname'] . '/'
			         . CONFIG::COVER_NAME;

			// If cover image does not exist or is not readable: use dummy image.
			if (!file_exists($tmpPath) || !is_readable($tmpPath)) {
				$tmpPath = $tplPath . '/images/no-cover.jpg';
			}

			// Create thumbnail and set path to thumbnail.
			$_['coverPath'] = $this->thumbsCache->getThumbnail($tmpPath);
		} else {
			$_['title'] = '';
			$_['position'] = 0;
			$_['length'] = 0;
			$_['progress'] = '';
			$_['info'] = '';
			$_['coverPath'] = '';
		}

		$_['reloadUrl'] = $_SERVER['PHP_SELF'] . '?cat=control';
		$_['reloadTimeout'] = Config::RELOAD_TIMEOUT;

		include($tplPath . '/Header.tpl.php');
		include($tplPath . '/Navigation.tpl.php');
		include($tplPath . '/Control.tpl.php');
		include($tplPath . '/ControlFooter.tpl.php');
	}

	protected function Seek($action) {
		$status = $this->mpd->executeCommand('status');

		if ($status['state'] == 'stop') {
			// Not playing.
			return;
		}

		// Get seek interval from cookie or use default.
		$seekInterval = 15;

		if (isset($_COOKIE['seekInterval'])) {
			$allowed = array(5, 10, 15, 30, 60);

			if (in_array($_COOKIE['seekInterval'], $allowed)) {
				$seekInterval = $_COOKIE['seekInterval'];
			}
		}

		// Seek one interval backward or forward.
		switch ($action) {
			case 'seekBw':
				$this->mpd->executeCommand('seekcur', '-' . $seekInterval);
				break;
			case 'seekFw':
				$this->mpd->executeCommand('seekcur', '+' . $seekInterval);
				break;
		}
	}
}
?>
