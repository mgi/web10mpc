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

/**
 * Exception thrown if MPD database is in an unexpected state.
 *
 * This can be used by clients if certain requirements on file tags are not met,
 * for example inconsistent mapping of "artist" to "artistsort" tags.
 */
class MpdDatabaseException extends MpdException {
}
?>
