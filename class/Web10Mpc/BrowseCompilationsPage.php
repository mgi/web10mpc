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

class BrowseCompilationsPage extends AbstractPage {
	protected $mpd = NULL;
	protected $mpdHelper = NULL;
	protected $sortMode = 'date';

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
		$this->mpdHelper = new MpdHelper($this->mpd);

		if (isset($_COOKIE['sortMode'])) {
			if ($_COOKIE['sortMode'] == 'name') {
				$this->sortMode = 'name';
			} else {
				$this->sortMode = 'date';
			}
		}
	}

	protected function handleRequestEx(array $request) {
		$_SESSION['lastBrowsePage'] = 'compilations';
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
		$_['range'] = 'Various artists';
		$_['backUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=root';
		$_['homeUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse' . '&amp;page=root';

		// per compilation: name, date, browseUrl, cssClass, imageClass
		$compilations = $this->mpdHelper->getCompilations(
			Config::COMPILATIONS_VALUE, $this->sortMode);
		$count = 0;
		$_['compilations'] = array();

		foreach ($compilations as $item) {
			$count++;
			$compilation = array();
			$compilation['name'] = htmlspecialchars($item['Album']);
			$compilation['date'] = $item['Date'];
			$compilation['browseUrl'] = $_SERVER['PHP_SELF'] . '?cat=browse'
			                          . '&amp;page=compilation'
			                          . '&amp;compilation='
			                          . htmlspecialchars(rawurlencode($item['Album']));

			if ($count % 2 == 1) {
				$compilation['cssClass'] = 'dark';
				$compilation['imageClass'] = '-dark';
			} else {
				$compilation['cssClass'] = '';
				$compilation['imageClass'] = '';
			}

			$_['compilations'][] = $compilation;
		}

		include('./templates/common/Header.tpl.php');
		include('./templates/common/Navigation.tpl.php');
		include('./templates/common/BrowseCompilations.tpl.php');
		include('./templates/common/Footer.tpl.php');
	}
}
?>
