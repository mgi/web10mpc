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

class SetupPage extends AbstractPage {
	protected $browseMode = 'tags';
	protected $layout = '240x320';
	protected $mpd = NULL;
	protected $mpdHelper = NULL;
	protected $repeat = 'off';
	protected $seekInterval = 15;
	protected $sortMode = 'date';

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
		$this->mpdHelper = new MpdHelper($this->mpd);
		$this->readSetup();
	}

	protected function handleRequestEx(array $request) {
		if ((isset($request['uid'])) && ($request['uid'] == $_SESSION['uid'])) {
			if (isset($request['action'])) {
				switch ($request['action']) {
					case 'set':
						$this->writeSetup($request);
						break;
					case 'updateDatabase':
						$this->mpd->executeCommand('update');
						// Destroy compilation related caches.
						$this->mpdHelper->destroyCaches();
						break;
				}

				// Unset browsing related session variables.
				unset($_SESSION['lastBrowsePage']);
				unset($_SESSION['artistRange']);
				unset($_SESSION['artist']);
				unset($_SESSION['album']);
				unset($_SESSION['compilation']);
				unset($_SESSION['folder']);
			}

			$_SESSION['uid'] = uniqid();
		}
	}

	protected function writeSetup(array $request) {
		if (isset($request['browseMode'])) {
			if ($request['browseMode'] == 'folders') {
				setcookie('browseMode', 'folders', time() + 3600 * 24 * 365);
				$this->browseMode = 'folders';
			} else {
				setcookie('browseMode', 'tags', time() + 3600 * 24 * 365);
				$this->browseMode = 'tags';
			}
		}

		if (isset($request['repeat'])) {
			if ($request['repeat'] == 'on') {
				$this->mpd->executeCommand('repeat', 1);
				$this->repeat = 'on';
			} else {
				$this->mpd->executeCommand('repeat', 0);
				$this->repeat = 'off';
			}
		}

		if (isset($request['seekInterval'])) {
			$allowed = array(5, 10, 15, 30, 60);

			if (in_array($request['seekInterval'], $allowed)) {
				setcookie('seekInterval', $request['seekInterval'],
				          time() + 3600 * 24 * 365);
				$this->seekInterval = $request['seekInterval'];
			} else {
				setcookie('seekInterval', 15, time() + 3600 * 24 * 365);
				$this->seekInterval = 15;
			}
		}

		if (isset($request['sortMode'])) {
			if ($request['sortMode'] == 'name') {
				setcookie('sortMode', 'name', time() + 3600 * 24 * 365);
				$this->sortMode = 'name';
			} else {
				setcookie('sortMode', 'date', time() + 3600 * 24 * 365);
				$this->sortMode = 'date';
			}
		}

		if (isset($request['layout'])) {
			if (in_array($request['layout'], array_keys(Config::$layouts))) {
				setcookie('layout', $request['layout'], time() + 3600 * 24 * 365);
				$this->layout = $request['layout'];
			} else {
				setcookie('layout', '240x320', time() + 3600 * 24 * 365);
				$this->layout = '240x320';
			}
		}
	}

	protected function readSetup() {
		if (isset($_COOKIE['browseMode'])) {
			if ($_COOKIE['browseMode'] == 'folders') {
				$this->browseMode = 'folders';
			} else {
				$this->browseMode = 'tags';
			}
		}

		$status = $this->mpd->executeCommand('status');
		$this->repeat = $status['repeat'] == 1 ? 'on' : 'off';

		if (isset($_COOKIE['seekInterval'])) {
			$allowed = array(5, 10, 15, 30, 60);

			if (in_array($_COOKIE['seekInterval'], $allowed)) {
				$this->seekInterval = $_COOKIE['seekInterval'];
			} else {
				$this->seekInterval = 15;
			}
		}

		if (isset($_COOKIE['sortMode'])) {
			if ($_COOKIE['sortMode'] == 'name') {
				$this->sortMode = 'name';
			} else {
				$this->sortMode = 'date';
			}
		}

		if (isset($_COOKIE['layout'])) {
			if (in_array($_COOKIE['layout'], array_keys(Config::$layouts))) {
				$this->layout = $_COOKIE['layout'];
			} else {
				$this->layout = '240x320';
			}
		}
	}

	protected function renderEx($tplPath) {
		$_['cssPath'] = $tplPath . '/css';
		$_['cat'] = 'setup';
		$status = $this->mpd->executeCommand('status');

		if (isset($status['songid'])) {
			$_['playlistAnchor'] = '#' . $status['songid'];
		} else {
			$_['playlistAnchor'] = '';
		}

		$_['imagePath'] = $tplPath . '/images';
		$_['uid'] = $_SESSION['uid'];
		$_['browseOptions'] = array();
		$_['repeatOptions'] = array();
		$_['seekOptions'] = array();
		$_['sortOptions'] = array();
		$_['layoutOptions'] = array();

		foreach (array('folders', 'tags') as $value) {
			if ($value == $this->browseMode) {
				$_['browseOptions'][] = array('value' => $value, 'selected' => TRUE);
			} else {
				$_['browseOptions'][] = array('value' => $value, 'selected' => FALSE);
			}
		}

		foreach (array('off', 'on') as $value) {
			if ($value == $this->repeat) {
				$_['repeatOptions'][] = array('value' => $value, 'selected' => TRUE);
			} else {
				$_['repeatOptions'][] = array('value' => $value, 'selected' => FALSE);
			}
		}

		foreach (array(5, 10, 15, 30, 60) as $value) {
			if ($value == $this->seekInterval) {
				$_['seekOptions'][] = array('value' => $value, 'selected' => TRUE);
			} else {
				$_['seekOptions'][] = array('value' => $value, 'selected' => FALSE);
			}
		}

		foreach (array('date', 'name') as $value) {
			if ($value == $this->sortMode) {
				$_['sortOptions'][] = array('value' => $value, 'selected' => TRUE);
			} else {
				$_['sortOptions'][] = array('value' => $value, 'selected' => FALSE);
			}
		}

		foreach (array_keys(Config::$layouts) as $value) {
			if ($value == $this->layout) {
				$_['layoutOptions'][] = array('value' => $value, 'selected' => TRUE);
			} else {
				$_['layoutOptions'][] = array('value' => $value, 'selected' => FALSE);
			}
		}

		$_['updateUrl'] = $_SERVER['PHP_SELF'] . '?cat=setup'
		                . '&amp;uid=' . $_SESSION['uid']
		                . '&amp;action=updateDatabase';
		$stats = $this->mpd->executeCommand('stats');
		$_['uptime'] = Utils\Utils::formatTimeLong($stats['uptime']);
		$_['playtime'] = Utils\Utils::formatTimeLong($stats['playtime']);
		$_['artists'] = $stats['artists'];
		$_['albums'] = $stats['albums'];
		$_['songs'] = $stats['songs'];
		$_['databasePlaytime'] = Utils\Utils::formatTimeLong($stats['db_playtime']);
		$_['databaseUpdated'] =
			Utils\Utils::formatDateUnix($stats['db_update'], TRUE);
		$_['version'] = Config::VERSION;
		$_['mpdProtocolVersion'] = $this->mpd->getProtocolVersion();

		include($tplPath . '/Header.tpl.php');
		include($tplPath . '/Navigation.tpl.php');
		include($tplPath . '/Setup.tpl.php');
		include($tplPath . '/Footer.tpl.php');
	}
}
?>
