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

class ThumbnailCache {
	const MIN_WIDTH = 16;
	const MAX_WIDTH = 2048;
	const MIN_HEIGHT = 16;
	const MAX_HEIGHT = 1536;
	const JPEG_QUALITY = 90;
	const PNG_COMPRESSION = 8;

	private $FILE_EXTENSIONS = array('jpeg', 'jpg', 'png');
	private $cachePath = '';
	private $width = 320;
	private $height = 240;

	public function __construct($cachePath, $width = 320, $height = 240) {
		$this->setCachePath($cachePath);
		$this->setWidth($width);
		$this->setHeight($height);
	}

	public function getCachePath() {
		return $this->cache_path;
	}

	public function setCachePath($path) {
		// Check cache path exists and is writable.
		if (file_exists($path) && is_writable($path)) {
			$this->cache_path = rtrim($path, '/');
		} else {
			throw new \Exception('Path does not exist or is not writable: ' . $path);
		}
	}

	public function getWidth() {
		return $this->width;
	}

	public function setWidth($width) {
		if ($width < self::MIN_WIDTH) {
			$width = self::MIN_WIDTH;
		}

		if ($width > self::MAX_WIDTH) {
			$width = self::MAX_WIDTH;
		}

		$this->width = $width;
	}

	public function getHeight() {
		return $this->height;
	}

	public function setHeight($height) {
		if ($height < self::MIN_HEIGHT) {
			$height = self::MIN_HEIGHT;
		}

		if ($height > self::MAX_HEIGHT) {
			$height = self::MAX_HEIGHT;
		}

		$this->height = $height;
	}

	public function clear() {
		// Get all files in the cache directory.
		$files = array();
		$files = scandir($this->cachePath);

		// Delete all image files with a supported extension.
		foreach ($files as $file) {
			$path = $this->cache_path . '/' . $file;
			$info = pathinfo($path);

			if (isset($info['extension'])) {
				$extension = strtolower($info['extension']);

				if (in_array($extension, $this->FILE_EXTENSIONS)) {
					unlink($path);
				}
			}
		}
	}

	public function getThumbnail($path) {
		// Check original image file exists, is readable and has a supported format.
		if (!file_exists($path) || !is_readable($path)) {
			throw new \Exception('File does not exist or is not readable: ' . $path);
		}

		$info = pathinfo($path);
		$extension = strtolower($info['extension']);

		if (!in_array($extension, $this->FILE_EXTENSIONS)) {
			throw new \Exception('Unsupported image format: ' . $extension);
		}

		// Build path to cached thumbnail image. Basename of file is MD5 of full
		// path to original image plus the resolution attached. This way, we can
		// have multiple thumbnails with different sizes for one image.
		$thumbnailPath = $this->cache_path . '/' . md5($path) . '_'
		               . $this->width . 'x' . $this->height . '.' . $extension;

		// If cached thumbnail does not exist or is outdated: create a new one.
		if ((!file_exists($thumbnailPath)) ||
				(filemtime($thumbnailPath) < filemtime($path))) {
			switch ($extension) {
				case 'jpeg':
					$this->cacheJpegThumbnail($path, $thumbnailPath);
					break;
				case 'jpg':
					$this->cacheJpegThumbnail($path, $thumbnailPath);
					break;
				case 'png':
					$this->cachePngThumbnail($path, $thumbnailPath);
					break;
			}
		}

		// Return path to the cached image.
		return $thumbnailPath;
	}

	private function cacheJpegThumbnail($srcPath, $destPath) {
		// Get original image dimensions and calculate dimensions of thumbnail.
		list($width, $height) = getimagesize($srcPath);
		list($newWidth, $newHeight) = $this->calcDimensions($width, $height);

		// Load original image.
		$image = imagecreatefromjpeg($srcPath);

		// Create thumbnail image.
		$thumbnail = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($thumbnail, $image, 0, 0, 0, 0,
			$newWidth, $newHeight, $width, $height);

		// Save thumbnail to cache.
		imagejpeg($thumbnail, $destPath, self::JPEG_QUALITY);

		// Free memory.
		imagedestroy($image);
		imagedestroy($thumbnail);
	}

	private function cachePngThumbnail($srcPath, $destPath) {
		// Get original image dimensions and calculate dimensions of thumbnail.
		list($width, $height) = getimagesize($srcPath);
		list($newWidth, $newHeight) = $this->calcDimensions($width, $height);

		// Load original image.
		$image = imagecreatefrompng($srcPath);

		// Create thumbnail image.
		$thumbnail = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($thumbnail, $image, 0, 0, 0, 0,
			$newWidth, $newHeight, $width, $height);

		// Save thumbnail to cache.
		imagepng($thumbnail, $destPath, self::PNG_COMPRESSION);

		// Free memory.
		imagedestroy($image);
		imagedestroy($thumbnail);
	}

	private function calcDimensions($origWidth, $origHeight) {
		// Calculate thumbnail image dimensions keeping the aspect ratio.
		$ratio = min(($this->width / $origWidth), ($this->height / $origHeight));
		$newWidth = intval($ratio * $origWidth);
		$newHeight = intval($ratio * $origHeight);
		return array($newWidth, $newHeight);
	}
}
?>
