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

class BrowseRootPage extends AbstractPage {
	protected $mpd = NULL;

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
	}

	protected function handleRequestEx(array $request) {
		$_SESSION['lastBrowsePage'] = 'root';
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

		// per range: name, browseUrl, cssClass, imageClass
		$_['ranges'] = array();

		for($i = 0; $i <= 10; $i++) {
			$range = array();

			if ($i == 10) {
				// last range is compilations
				$range['browseUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
				                    . '&amp;page=compilations';
			} else {
				$range['browseUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
				                    . '&amp;page=artistRange'
				                    . '&amp;artistRange=' . $i;
			}

			if ($i % 2 == 1) {
				$range['cssClass'] = 'dark';
				$range['imageClass'] = '-dark';
			} else {
				$range['cssClass'] = '';
				$range['imageClass'] = '';
			}

			$_['ranges'][] = $range;
		}

		$_['ranges'][0]['name'] = 'A - C';
		$_['ranges'][1]['name'] = 'D - F';
		$_['ranges'][2]['name'] = 'G - I';
		$_['ranges'][3]['name'] = 'J - L';
		$_['ranges'][4]['name'] = 'M - O';
		$_['ranges'][5]['name'] = 'P - R';
		$_['ranges'][6]['name'] = 'S - U';
		$_['ranges'][7]['name'] = 'V - X';
		$_['ranges'][8]['name'] = 'Y - Z';
		$_['ranges'][9]['name'] = '0 - 9 and others';
		$_['ranges'][10]['name'] = 'Various artists';
		include($tplPath . '/Header.tpl.php');
		include($tplPath . '/Navigation.tpl.php');
		include($tplPath . '/BrowseRoot.tpl.php');
		include($tplPath . '/Footer.tpl.php');
	}
}
?>
