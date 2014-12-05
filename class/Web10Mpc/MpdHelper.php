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

class MpdHelper {
	private $mpd = NULL;

	public function __construct(Mpd\Mpd $mpd) {
		$this->mpd = $mpd;
	}

	public function getArtists($useArtistSortTag = FALSE) {
		if ($useArtistSortTag) {
			// Use "ArtistSort" tag, redirect function call.
			$result = $this->getArtistsEx();
		} else {
			// Do not use "ArtistSort" tag, map artist name to itself.
			$artists = $this->mpd->executeCommand('list', 'Artist');
			$result = array();

			foreach ($artists as $artist) {
				$result[] = array('Artist' => $artist['Artist'],
				                  'ArtistSort' => $artist['Artist']);
			}

			usort($result, array($this, 'sortArtistsByName'));
		}

		return $result;
	}

	public function getCompilationOnlyArtists($albumArtist) {
		// Return cached result if available.
		$cachePath = './cache/__coa_' . md5($albumArtist) . '.cache';

		if (file_exists($cachePath)) {
			$result = unserialize(file_get_contents($cachePath));
			return $result;
		}

		// Get all artists and all compilations.
		$artists = $this->mpd->executeCommand('list', 'Artist');
		$compilations = $this->getCompilations($albumArtist);
		$result = array();

		// Get all albums for each artist and check if some of the albums are not in
		// the compilations list.
		foreach ($artists as $artist) {
			$albums = $this->getAlbumsByArtist($artist['Artist']);
			$compilationOnly = TRUE;

			foreach ($albums as $album) {
				if (!in_array($album, $compilations)) {
					// This album is not a compilation. So this artist is not a
					// compilation only artist and we can stop scanning further albums of
					// this artist.
					$compilationOnly = FALSE;
					break;
				}
			}

			if ($compilationOnly) {
				// This artist only appears on compilations. Add the artist to the
				// result array.
				$result[] = $artist['Artist'];
			}
		}

		natcasesort($result);

		// Cache the result before returning it.
		// TODO: check cache directory exists and is writable.
		file_put_contents($cachePath, serialize($result));
		return $result;
	}

	public function getAlbumsByArtist($artist, $sortBy = 'date') {
		return $this->getAlbums('Artist', $artist, $sortBy);
	}

	public function getCompilations($albumArtist = 'Various Artists',
		$sortBy = 'date') {
		// Return cached result if available.
		$cachePath = './cache/__compilations_' . md5($albumArtist . '_' . $sortBy)
		           . '.cache';

		if (file_exists($cachePath)) {
			$result = unserialize(file_get_contents($cachePath));
			return $result;
		}

		$result = $this->getAlbums('AlbumArtist', $albumArtist, $sortBy);

		// Cache the result before returning it.
		// TODO: check cache directory exists and is writable.
		file_put_contents($cachePath, serialize($result));
		return $result;
	}

	public function getSongsByArtistAndAlbum($artist, $album) {
		if ($album == 'Unknown Album') {
			// Virtual album created by addMissingKeys function, search for empty tag
			// in MPD. (Ugly temporary workaround.)
			$album = '';
		}

		$args = array('Artist', $artist, 'Album', $album);
		$songs = $this->mpd->executeCommandEx('find', $args);
		$songs = $this->addMissingKeys($songs);
		usort($songs, array($this, 'sortSongsByTrackNumber'));
		return $songs;
	}

	public function getSongsByCompilation($album) {
		// Should really only be called for compilations. It would work with other
		// albums, but it could cause problems when there are multiple albums with
		// the same name ("The Greatest Hits") by different artists. Compilation
		// album names are likely to be unique.
		if ($album == 'Unknown Album') {
			// Virtual album created by addMissingKeys function, search for empty tag
			// in MPD. (Ugly temporary workaround.)
			$album = '';
		}

		$songs = $this->mpd->executeCommand('find', 'Album', $album);
		$songs = $this->addMissingKeys($songs);
		usort($songs, array($this, 'sortSongsByTrackNumber'));
		return $songs;
	}

	public function getPlaylists() {
		$playlists = $this->mpd->executeCommand('listplaylists');
		usort($playlists, array($this, 'sortPlaylistsByName'));
		return $playlists;
	}

	public function destroyCaches() {
		$files = scandir('./cache');
		$pattern = '/^__((artists)|(coa)|(compilations))(_[0-9a-f]{32})?\.cache$/';

		foreach ($files as $file) {
			if (preg_match($pattern, $file)) {
				unlink('./cache/' . $file);
			}
		}
	}

	protected function addMissingKeys(array $songs) {
		// Ensure that all songs have a minimum set of tags. Fill missing tags using
		// default values.
		$defaults = array(
			'Artist' => 'Unknown Artist',
			'Album' => 'Unknown Album',
			'Date' => '?',
			'Title' => 'Unknown Title',
			'Track' => '?'
		);
		$result = array();

		foreach ($songs as $song) {
			$result[] = array_merge($defaults, $song);
		}

		return $result;
	}

	protected function getArtistsEx() {
		// Return cached result if available.
		$cachePath = './cache/__artists.cache';

		if (file_exists($cachePath)) {
			$result = unserialize(file_get_contents($cachePath));
			return $result;
		}

		// Get all artists.
		//$start = microtime(true);
		$artists = $this->mpd->executeCommand('list', 'Artist');
		$result = array();

		//Get all songs for each artist.
		foreach ($artists as $artist) {
			$songs = $this->mpd->executeCommand('find', 'Artist', $artist['Artist']);

			// Use "ArtistSort" tag of the first song in the list. Default to "Artist"
			// tag if "ArtistSort" tag is missing.
			if (isset($songs[0]['ArtistSort'])) {
				$mapping = array(
					'Artist' => $artist['Artist'],
					'ArtistSort' => $songs[0]['ArtistSort']
				);
			} else {
				$mapping = array('Artist' => $artist['Artist'],
				                 'ArtistSort' => $artist['Artist']);
			}

			// Check for consistency.
			foreach ($songs as $song) {
				if (!isset($song['ArtistSort'])) {
					continue;
				}

				if ($song['ArtistSort'] != $mapping['ArtistSort']) {
					$msg = 'Artist "' . $artist['Artist'] . '" has songs with different '
					     . 'values for the "ArtistSort" tag.';
					throw new Mpd\MpdDatabaseException($msg);
				}
			}

			$result[] = $mapping;
		}

		usort($result, array($this, 'sortArtistsBySortKey'));
		//$end = microtime(true);
		//$diff = $end - $start;
		//echo 'getArtistsEx: ' . $diff . 's' . "\n";

		// Cache the result before returning it.
		// TODO: check cache directory exists and is writable.
		file_put_contents($cachePath, serialize($result));
		return $result;
	}

	protected function getAlbums($tag, $value, $sortBy = 'date') {
		// A simple "list Album $value" would only work when $tag is 'Artist', and
		// it would return only the album names. But we also want it to work for
		// compilations ($tag = 'AlbumArtist'), and we also want the dates. So we
		// have to scan all the songs.
		$albums = array();
		$songs = $this->mpd->executeCommand('find', $tag, $value);
		$songs = $this->AddMissingKeys($songs);

		foreach ($songs as $song) {
			if ($song[$tag] == $value) {
				$album = array('Album' => $song['Album'], 'Date' => $song['Date']);

				if (!in_array($album, $albums)) {
					$albums[] = $album;
				}
			}
		}

		if ($sortBy == 'name') {
			usort($albums, array($this, 'sortAlbumsByName'));
		} else {
			usort($albums, array($this, 'sortAlbumsByDate'));
		}

		return $albums;
	}

	protected function sortArtistsByName($a, $b) {
		return strnatcasecmp($a['Artist'], $b['Artist']);
	}

	protected function sortArtistsBySortKey($a, $b) {
		return strnatcasecmp($a['ArtistSort'], $b['ArtistSort']);
	}

	protected function sortAlbumsByDate($a, $b) {
		if ($a['Date'] != $b['Date']) {
			return strnatcasecmp($a['Date'], $b['Date']);
		}

		// If dates are equal, sort by album name.
		return strnatcasecmp($a['Album'], $b['Album']);
	}

	protected function sortAlbumsByName($a, $b) {
		if ($a['Album'] != $b['Album']) {
			return strnatcasecmp($a['Album'], $b['Album']);
		}

		// If album names are equal, sort by date.
		return strnatcasecmp($a['Date'], $b['Date']);
	}

	protected function sortPlaylistsByName($a, $b) {
		// 'playlist' can never be equal, because it corresponds to the name in the
		// file system.
		return strnatcasecmp($a['playlist'], $b['playlist']);
	}

	protected function sortSongsByTrackNumber($a, $b) {
		if ($a['Track'] != $b['Track']) {
			return strnatcasecmp($a['Track'], $b['Track']);
		}

		// Should not get here, because this is only called for the content of a
		// single album. But in case we do, sort songs with equal track numbers by
		// filename.
		return strnatcasecmp($a['file'], $b['file']);
	}
}
?>
