<?php
/**
 * Web1.0MPC\Mpd - a PHP interface to MPD (Music Player Daemon).
 * Copyright (C) 2011-2014  Marcus Geuecke (web10mpc [at] geuecke [dot] org)
 *
 * LICENSE:
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
 *
 * @copyright Copyright (C) 2011-2014  Marcus Geuecke (web10mpc [at] geuecke [dot] org)
 * @license http://www.gnu.org/licenses/gpl-2.0.html   GPLv2 or later
 */
namespace Web10Mpc\Mpd;

/** Definition of MPD command result types. */
final class MpdCommandResultType {
	/** Unsupported command, will throw an exception. */
	const Unsupported = 0;
	/** Internal use only, use class methods instead. */
	const Internal = 1;
	/** Just send command, do not parse response ("close", "kill"). */
	const None = 2;
	/** No music data, just returns "OK" or "ACK ..." on error. */
	const Ack = 3;
	/** A single value. */
	const Value = 4;
	/** A list of values. */
	const ValueList = 5;
	/** A list of (key,value) pairs. */
	const Assoc = 6;
	/** Lists of (key,value) pairs, separated by the 1st element of the MPD
	 * response.
	 */
	const AssocList = 7;
	/** A list of files (songs) with attributes. */
	const Files = 8;
	/** Lists of directories, files and playlists. */
	const Multi = 9;
	/**
	 * A list of stickers ((key,value) pairs, requires different parsing, i.e.
	 * cannot use type Assoc).
	 */
	const Stickers = 10;
	/**
	 * Special handling for "plchangesposid", "listplaylists", "listfiles",
	 * "listmounts", "listneighbors", "sticker find", "outputs", "decoders".
	 */
	const Special = 11;

	/**
	 * Enumeration class, prevent instance creation by making constructor
	 * private.
	 */
	private function __construct() {
	}
}
?>
