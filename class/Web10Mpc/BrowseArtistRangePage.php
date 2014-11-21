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

class BrowseArtistRangePage extends AbstractPage {
	protected $mpd = NULL;
	protected $mpdHelper = NULL;
	protected $artistRange = 0;

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
		$this->mpdHelper = new MpdHelper($this->mpd);

		if (isset($_SESSION['artistRange'])) {
			$this->artistRange = $_SESSION['artistRange'];
		}
	}

	protected function handleRequestEx(array $request) {
		$_SESSION['lastBrowsePage'] = 'artistRange';

		if (isset($request['artistRange'])) {
			$this->artistRange = $request['artistRange'];
			$_SESSION['artistRange'] = $this->artistRange;
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

		switch ($this->artistRange) {
			case 1:
				$pattern = '/^[D-Fd-f]+/';
				$_['artistRange'] = 'D - F';
				break;
			case 2:
				$pattern = '/^[G-Ig-i]+/';
				$_['artistRange'] = 'G - I';
				break;
			case 3:
				$pattern = '/^[J-Lj-l]+/';
				$_['artistRange'] = 'J - L';
				break;
			case 4:
				$pattern = '/^[M-Om-o]+/';
				$_['artistRange'] = 'M - O';
				break;
			case 5:
				$pattern = '/^[P-Rp-r]+/';
				$_['artistRange'] = 'P - R';
				break;
			case 6:
				$pattern = '/^[S-Us-u]+/';
				$_['artistRange'] = 'S - U';
				break;
			case 7:
				$pattern = '/^[V-Xv-x]+/';
				$_['artistRange'] = 'V - X';
				break;
			case 8:
				$pattern = '/^[Y-Zy-z]+/';
				$_['artistRange'] = 'Y - Z';
				break;
			case 9:
				$pattern = '/^[^A-Za-z]+/';
				$_['artistRange'] = '0 - 9 and others';
				break;
			default:
				$pattern = '/^[A-Ca-c]+/';
				$_['artistRange'] = 'A - C';
				break;
		}

		$_['backUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=root';
		$_['homeUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=root';

		// per artist: name, browseUrl, cssClass, imageClass
		$artists = $this->mpdHelper->getArtists(Config::USE_ARTISTSORT_TAG);
		$coa = array();

		if (!Config::SHOW_COMPILATION_ONLY_ARTISTS) {
			$coa = $this->mpdHelper->getCompilationOnlyArtists(
				Config::COMPILATIONS_VALUE);
		}

		$count = 0;
		$_['artists'] = array();

		foreach ($artists as $item) {
			if (preg_match($pattern, $item['ArtistSort'])) {
				if (!in_array($item['Artist'], $coa)) {
					$count++;
					$artist = array();
					$artist['name'] = htmlspecialchars($item['Artist']);
					$artist['browseUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
					                     . '&amp;page=artist'
					                     . '&amp;artist='
					                     . htmlspecialchars(rawurlencode($item['Artist']));

					if ($count % 2 == 1) {
						$artist['cssClass'] = 'dark';
						$artist['imageClass'] = '-dark';
					} else {
						$artist['cssClass'] = '';
						$artist['imageClass'] = '';
					}

					$_['artists'][] = $artist;
				}
			}
		}

		include($tplPath . '/Header.tpl.php');
		include($tplPath . '/Navigation.tpl.php');
		include($tplPath . '/BrowseArtistRange.tpl.php');
		include($tplPath . '/Footer.tpl.php');
	}
}
?>
