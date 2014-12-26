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

class Config {
	const VERSION = '0.4a9';

	const HOSTNAME = 'localhost';
	const PORT = 6600;
	const PASSWORD = NULL;

	const COVER_NAME = 'folder.jpg';
	const MUSIC_ROOT = '/home/marcus/music';
	const COMPILATIONS_VALUE = 'Various Artists';
	const SHOW_COMPILATION_ONLY_ARTISTS = FALSE;
	const SHOW_VOLUME_CONTROLS = FALSE;
	const USE_ARTISTSORT_TAG = FALSE; // Check README file before enabling this!
	const RELOAD_TIMEOUT = 2;

	private function __construct() {
	}

	public static $layouts = array(
		'240x320' => array(
			'path' => './templates/240x320',
			'maxCoverWidth' => 200,
			'maxCoverHeight' => 130
		),
		'360x640' => array(
			'path' => './templates/360x640',
			'maxCoverWidth' => 320,
			'maxCoverHeight' => 280
		)
	);
}
?>
