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
namespace Web10Mpc\Utils;

class Utils {
	public static function formatDateISO8601($iso8601Time, $addSpans = false) {
		// Format the given ISO8601 timestamp as "31.12.2011 23:59:59". Optionally
		// add <span> tags to prevent Opera Mobile from converting the date string
		// to a phone number link.
		$str = '';

		if ($addSpans) {
			$str  = '<span>' . date('d', strtotime($iso8601Time)) . '.</span>'
			      . '<span>' . date('m', strtotime($iso8601Time)) . '.</span>'
			      . '<span>' . date('Y', strtotime($iso8601Time)) . ' </span>'
			      . date('H:i:s', strtotime($iso8601Time));
		} else {
			$str = date('d.m.Y H:i:s', $iso8601Time);
		}

		return $str;
	}

	public static function formatDateUnix($unixTime, $addSpans = false) {
		// Format the given unix timestamp as "31.12.2011 23:59:59". Optionally add
		// <span> tags to prevent Opera Mobile from converting the date string to a
		// phone number link.
		$str = '';

		if ($addSpans) {
			$str = '<span>' . date('d', $unixTime) . '.</span>'
			     . '<span>' . date('m', $unixTime) . '.</span>'
			     . '<span>' . date('Y', $unixTime) . ' </span>'
			     . date('H:i:s', $unixTime);
		} else {
			$str = date('d.m.Y H:i:s', $unixTime);
		}

		return $str;
	}

	public static function formatTimeLong($seconds) {
		// Format the given time in seconds as "d days, hh:mm:ss".
		$days = floor($seconds / 86400);
		$seconds = $seconds % 86400;
		$hours = floor($seconds / 3600);
		$seconds = $seconds % 3600;
		$minutes = floor($seconds / 60);
		$seconds = $seconds % 60;
		$str = $days . ' days, ' . sprintf('%02d', $hours) . ':'
		     . sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
		return $str;
	}

	public static function formatTimeShort($seconds) {
		// Format the given time in seconds as "mm:ss" (197 --> 3:17).
		return floor($seconds / 60) . ':' . sprintf('%02d', $seconds % 60);
	}

	public static function shortenStringUTF8($string, $maxLength = 25) {
		// Shorten a UTF8 string: keep x characters of the beginning and the end and
		// add "..." in the middle. Do not shorten strings with a length < 5. Use
		// mb_* functions to prevent destroying strings with multi-byte characters.
		$length = mb_strlen($string, 'UTF-8');

		if ($maxLength < 5) {
			$maxLength = 5;
		}

		if ($length <= $maxLength) {
			return $string;
		}

		$keep = intval(($maxLength - 3) / 2);
		$left = mb_substr($string, 0, $keep, 'UTF-8');
		$right = mb_substr($string, $length - $keep, $keep, 'UTF-8');
		return $left . '...' . $right;
	}
}
?>
