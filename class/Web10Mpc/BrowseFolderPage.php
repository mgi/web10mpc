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

class BrowseFolderPage extends AbstractPage {
	protected $folder = '';
	protected $mpd = NULL;
	protected $parentFolder = '';

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;

		if (isset($_SESSION['folder'])) {
			$this->folder = $_SESSION['folder'];
			$this->parentFolder = $this->getParentFolder($this->folder);
		}
	}

	protected function getParentFolder($folder) {
		$pos = strrpos($folder, '/');

		if ($pos == FALSE) {
			return '';
		}

		return substr($folder, 0, $pos);
	}

	protected function handleRequestEx(array $request) {
		$_SESSION['lastBrowsePage'] = 'folder';

		if (isset($request['folder'])) {
			$this->folder = trim(rawurldecode($request['folder']), '/');
			$this->parentFolder = $this->getParentFolder($this->folder);
			$_SESSION['folder'] = $this->folder;
		}

		if ((isset($request['uid'])) && ($request['uid'] == $_SESSION['uid'])) {
			if (isset($request['action'])) {
				switch ($request['action']) {
					case 'add':
						if (isset($request['uri'])) {
							$this->mpd->executeCommand('add',
								trim(rawurldecode($request['uri']), '/'));
						}
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
		$_['folder'] = '/' . htmlspecialchars($this->folder);
		$_['backUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			            . '&amp;page=folder'
			            . '&amp;uid=' . $_SESSION['uid']
			            . '&amp;action=cd'
			            . '&amp;folder='
			            . htmlspecialchars(rawurlencode($this->parentFolder));
		$_['homeUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			            . '&amp;page=folder'
			            . '&amp;uid=' . $_SESSION['uid']
			            . '&amp;action=cd'
			            . '&amp;folder=';
		$_['addAllUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			              . '&amp;page=folder'
			              . '&amp;uid=' . $_SESSION['uid']
			              . '&amp;action=add'
			              . '&amp;uri='
			              . htmlspecialchars(rawurlencode($this->folder));

		// per directory: name, openUrl, cssClass, imageClass
		$dirContent = $this->mpd->executeCommand('lsinfo', $this->folder);
		$count = 0;
		$_['directories'] = array();
		$_['files'] = array();

		foreach ($dirContent['directories'] as $item) {
			$count ++;
			$directory = array();
			$pos = strrpos($item['directory'], '/');

			if ($pos == FALSE) {
				$directoryName = Utils\Utils::shortenStringUTF8($item['directory']);
			} else {
				$directoryName =
					Utils\Utils::shortenStringUTF8(substr($item['directory'], $pos + 1));
			}

			$directory['name'] = htmlspecialchars('[ ' . $directoryName . ' ]');
			$directory['openUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			                      . '&amp;page=folder'
			                      . '&amp;uid=' . $_SESSION['uid']
			                      . '&amp;folder='
			                      . htmlspecialchars(rawurlencode($item['directory']));

			if ($count % 2 == 1) {
				$directory['cssClass'] = 'dark';
				$directory['imageClass'] = '-dark';
			} else {
				$directory['cssClass'] = '';
				$directory['imageClass'] = '';
			}

			$_['directories'][] = $directory;
		}

		// per file: name, time, addUrl, cssClass, imageClass
		foreach ($dirContent['files'] as $item) {
			$count++;
			$file = array();
			$pathInfo = pathinfo($item['file']);
			$file['name'] =
				htmlspecialchars(Utils\Utils::shortenStringUTF8($pathInfo['basename'], 35));
			$file['time'] = Utils\Utils::formatTimeShort($item['Time']);
			$file['addUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			                . '&amp;page=folder'
			                . '&amp;uid=' . $_SESSION['uid']
			                . '&amp;action=add'
			                . '&amp;uri='
			                . htmlspecialchars(rawurlencode($item['file']));

			if ($count % 2 == 1) {
				$file['cssClass'] = 'dark';
				$file['imageClass'] = '-dark';
			} else {
				$file['cssClass'] = '';
				$file['imageClass'] = '';
			}

			$_['files'][] = $file;
		}

		include('./templates/common/Header.tpl.php');
		include('./templates/common/Navigation.tpl.php');
		include('./templates/common/BrowseFolder.tpl.php');
		include('./templates/common/Footer.tpl.php');
	}
}
?>
