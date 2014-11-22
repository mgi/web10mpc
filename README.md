# Web1.0MPC

a web based MPD (Music Player Daemon) client for small screens

## Credits

Thanks to the MPD creators:

* [Music Player Daemon](http://www.musicpd.org/)
* [Music Player Daemon Community Wiki](http://mpd.wikia.com/)

GNOME Icons from
[gnome-icon-theme-2.22.0.tar.bz2](http://ftp.gnome.org/mirror/gnome.org/desktop/2.22/2.22.0/sources/).

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.html) or later.

## Description

Web1.0MPC is a web based MPD (Music Player Daemon) client for small screens.

As the name implies, Web1.0MPC is lightweight and tries to keep requirements as
low as possible. This way, it should also work on rather ancient devices with
simple mobile phone browsers lacking modern features, e.g. old Windows Mobile
devices.

Old versions of Web1.0MPC were based on an updated version of the original
~~[mpd.class.php](http://mpd.24oz.com/)~~ by B. Carlisle. Plans to extend and
support this class were canceled. Instead a new PHP interface to MPD is being
created along with Web1.0MPC 0.4. It has now been extracted as a
[separate project](https://github.com/web10mpc/web10mpc-mpd) for general use.

This software is intended to be installed on a server in your local private
network. It is work in progress and might contain bugs, do **not** install it on
a public server.

Features:

### Control

* manage playback (play, pause, stop, seek, skip)
* volume control (optional)
* display song info
* resize, cache and display cover art

### Playlist

* list all songs in playlist
* clear playlist
* remove single song from playlist
* jump to any song
* load/save/delete stored playlists

### Browse

* browse the music database by tags or folders
* add all albums by an artist, one album or a single song to the playlist
* compilation albums ("Various Artists") have their own category
* hide "compilation only artists" from the normal artist list (optional)
* support "ArtistSort" tag

### Setup

* select browse mode (tags, folders)
* toggle playlist repeat
* set seek interval
* toggle album sort mode (by date, by name)
* select layout
  * 360x640 (e.g. Symbian S60/5th and S^3)
  * 240x320 (e.g. Windows Mobile)
* trigger database update
* print server statistics

### Built on solid 1990s web technology

* plain HTML 4.01 and PHP
* absolute minimum of JavaScript (works without)
* basic CSS rules
* evil deprecated table layouts
* **no** AJAX
* **no** CSS extravagance
* **no** annoying JavaScript bling-bling
* **no** transparent images
* **no** "my" in its name

Keep your old device, Web1.0MPC will work on it – go green! :frog:

## Requirements

Development system:

* Debian Jessie
* MPD 0.19.1
* lighttpd 1.4.35
* PHP 5.6.2 with php5-cgi, php5-gd and ```short_open_tag = On```

Should work with less, but not tested.

Tested browsers:

* Iceweasel 31 ESR on Debian
* Opera Mobile 12 on Symbian S60/5th
* IE on emulated Windows Mobile 2003 SE

## Usage

### Installation

* Extract the files to a directory on your webserver.
* Edit ./class/Web10Mpc/Config.php.
* Check and adjust file and directory permissions, the ./cache directory needs
  to be writable by the webserver.

### Sort artists by "ArtistSort" tag

Setting ```USE_ARTISTSORT_TAG = TRUE``` in Config.php will sort artists like
this:

Pearl Jam --> Tom Petty --> Pink Floyd --> The Police --> Porcupine Tree.

Please note the following before you enable this:

* Add "ArtistSort" tags to all songs. Create a consistent mapping, i.e. avoid
  something like this:

Song | Artist | ArtistSort
---- | ------ | ----------
1 | Bob Marley & The Wailers | Marley, Bob & The Wailers
2 | Bob Marley & The Wailers | Marley, Bob & Wailers, The

  This does not make sense, and Web1.0MPC will throw an MpdDatabaseException.

* MPD must read the "artistsort" tag, by default it does **not**. Add it to the
  line starting with ```metadata_to_use``` in your mpd.conf file.
* Restart MPD.
* Update the MPD database.
* Web1.0MPC will scan the complete database to create the mapping of artist
  names to sort keys. This may take some time (~3.9s on an Atom N270 @ 1600 MHz
  for a database with ~650 artists and ~10,400 songs, MPD and Web1.0MPC running
  on the same machine (no network transfer)). It will do this only once and
  cache the results in a file. The cache file will be deleted (and recreated on
  the next request) when you press "Update database" on "Setup" page.

## Contact / Bugs

[Web1.0MPC](http://web10mpc.geuecke.org)

e-mail: web10mpc [at] geuecke [dot] org
