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

class Application {
	public function run(array $request) {
		if (!isset($_SESSION['uid'])) {
			if (isset($_GET['uid'])) {
				// request after session timeout
				$_SESSION['uid'] = $_GET['uid'];
			} else {
				$_SESSION['uid'] = uniqid();
			}
		}

		$cat = isset($_GET['cat']) ? $_GET['cat'] : 'control';
		$page = isset($_GET['page']) ? $_GET['page'] : '';
		$browseMode = isset($_COOKIE['browseMode']) ? $_COOKIE['browseMode'] : 'tags';

		if (($cat == 'browse') && ($page == 'lastBrowsePage')) {
			if (isset($_SESSION['lastBrowsePage'])) {
				$page = $_SESSION['lastBrowsePage'];
			} else {
				if ($browseMode == 'folders') {
					$page = 'folder';
				} else {
					$page = 'root';
				}
			}
		}

		if (isset($_COOKIE['layout'])) {
			$tpl = Config::$layouts[$_COOKIE['layout']];
		} else {
			$_COOKIE['layout'] = '240x320';
			$tpl = Config::$layouts['240x320'];
		}

		$mpd = new Mpd\Mpd(Config::HOSTNAME, Config::PORT, Config::PASSWORD);
		$mpd->connect();

		switch ($cat) {
			// playlist pages
			case 'playlist':
				switch ($page) {
					case 'load':
						$page = new PlaylistLoadPage($mpd);
						break;
					case 'save':
						$page = new PlaylistSavePage($mpd);
						break;
					default: // 'show', no page or unknown page
						$page = new PlaylistPage($mpd);
				}
				break;

			// browse pages
			case 'browse':
				switch ($page) {
					case 'artistRange':
						$page = new BrowseArtistRangePage($mpd);
						break;
					case 'artist':
						$page = new BrowseArtistPage($mpd);
						break;
					case 'album':
						$page = new BrowseAlbumPage($mpd);
						break;
					case 'compilations':
						$page = new BrowseCompilationsPage($mpd);
						break;
					case 'compilation':
						$page = new BrowseCompilationPage($mpd);
						break;
					case 'folder':
						$page = new BrowseFolderPage($mpd);
						break;
					default: // 'root', no page or unknown page
						$page = new BrowseRootPage($mpd);
				}
				break;

			// setup page(s)
			case 'setup':
				$page = new SetupPage($mpd);
				break;

			// 'control', no category or unknown category
			default:
				$thumbsCache = new Utils\ThumbnailCache('./cache',
					$tpl['maxCoverWidth'], $tpl[ 'maxCoverHeight']);
				$page = new ControlPage($mpd, $thumbsCache);
		}

		$page->handleRequest($_GET);
		echo $page->render($tpl['path']);
	}
}
?>
