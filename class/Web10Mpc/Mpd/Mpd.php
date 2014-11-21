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
 * A class to connect and talk to MPD (Music Player Daemon).
 *
 * Created along with Web1.0MPC (a web based remote control for MPD) to replace
 * the original mpd.class.php by B. Carlisle. Now extracted as a separate
 * project for general use.
 *
 * Plans for a full-fledged OO approach were dumped. After all, this is PHP, so
 * let's keep it simple. Features:
 *
 * - connect to MPD
 * - send commands and arguments
 * - parse MPD response
 * - return results as values and associative arrays of various dimensions
 * - throw some exceptions if something goes wrong
 *
 * Most of the MPD commands up to version 0.18 are supported, except for
 * commands that require keeping the socket open ("idle", "noidle", Client to
 * client).
 */
class Mpd {
	const CLASS_VERSION = '0.1b1';
	const MPD_OK = 'OK';
	const MPD_ACK = 'ACK ';

	/**
	 * Mapping of MPD commands to command results types.
	 *
	 * Each MPD command is mapped to a command result type that specifies how the
	 * response of the MPD server has to be parsed.
	 *
	 * Result type of "sticker" command depends on the first argument, but a
	 * command can only have one result type. So "sticker" is split into five
	 * separate commands.
	 *
	 * @var array $commands Mapping of MPD commands to command results types.
	 */
	private $commands = array(
		// Command lists:
		'command_list_begin'    => MpdCommandResultType::Internal,
		'command_list_ok_begin' => MpdCommandResultType::Internal,
		'command_list_end'      => MpdCommandResultType::Internal,
		// Querying MPD's status:
		'clearerror'            => MpdCommandResultType::Ack,
		'currentsong'           => MpdCommandResultType::Assoc,
		'idle'                  => MpdCommandResultType::Unsupported,
		'noidle'                => MpdCommandResultType::Unsupported,
		'status'                => MpdCommandResultType::Assoc,
		'stats'                 => MpdCommandResultType::Assoc,
		// Playback options:
		'consume'               => MpdCommandResultType::Ack,
		'crossfade'             => MpdCommandResultType::Ack,
		'mixrampdb'             => MpdCommandResultType::Ack,
		'mixrampdelay'          => MpdCommandResultType::Ack,
		'random'                => MpdCommandResultType::Ack,
		'repeat'                => MpdCommandResultType::Ack,
		'setvol'                => MpdCommandResultType::Ack,
		'single'                => MpdCommandResultType::Ack,
		'replay_gain_mode'      => MpdCommandResultType::Ack,
		'replay_gain_status'    => MpdCommandResultType::Value,
		// Controlling playback:
		'next'                  => MpdCommandResultType::Ack,
		'pause'                 => MpdCommandResultType::Ack,
		'play'                  => MpdCommandResultType::Ack,
		'playid'                => MpdCommandResultType::Ack,
		'previous'              => MpdCommandResultType::Ack,
		'seek'                  => MpdCommandResultType::Ack,
		'seekid'                => MpdCommandResultType::Ack,
		'seekcur'               => MpdCommandResultType::Ack,
		'stop'                  => MpdCommandResultType::Ack,
		// The current playlist:
		'add'                   => MpdCommandResultType::Ack,
		'addid'                 => MpdCommandResultType::Value,
		'clear'                 => MpdCommandResultType::Ack,
		'delete'                => MpdCommandResultType::Ack,
		'deleteid'              => MpdCommandResultType::Ack,
		'move'                  => MpdCommandResultType::Ack,
		'moveid'                => MpdCommandResultType::Ack,
		'playlist'              => MpdCommandResultType::Arr,
		'playlistfind'          => MpdCommandResultType::Files,
		'playlistid'            => MpdCommandResultType::Files,
		'playlistinfo'          => MpdCommandResultType::Files,
		'playlistsearch'        => MpdCommandResultType::Files,
		'plchanges'             => MpdCommandResultType::Files,
		'plchangesposid'        => MpdCommandResultType::Special,
		'prio'                  => MpdCommandResultType::Ack,
		'prioid'                => MpdCommandResultType::Ack,
		'shuffle'               => MpdCommandResultType::Ack,
		'swap'                  => MpdCommandResultType::Ack,
		'swapid'                => MpdCommandResultType::Ack,
		// Stored playlists:
		'listplaylist'          => MpdCommandResultType::Arr,
		'listplaylistinfo'      => MpdCommandResultType::Files,
		'listplaylists'         => MpdCommandResultType::Special,
		'load'                  => MpdCommandResultType::Ack,
		'playlistadd'           => MpdCommandResultType::Ack,
		'playlistclear'         => MpdCommandResultType::Ack,
		'playlistdelete'        => MpdCommandResultType::Ack,
		'playlistmove'          => MpdCommandResultType::Ack,
		'rename'                => MpdCommandResultType::Ack,
		'rm'                    => MpdCommandResultType::Ack,
		'save'                  => MpdCommandResultType::Ack,
		// The music database:
		'count'                 => MpdCommandResultType::Assoc,
		'find'                  => MpdCommandResultType::Files,
		'findadd'               => MpdCommandResultType::Ack,
		'list'                  => MpdCommandResultType::Arr,
		'listall'               => MpdCommandResultType::Multi,
		'listallinfo'           => MpdCommandResultType::Multi,
		'lsinfo'                => MpdCommandResultType::Multi,
		'readcomments'          => MpdCommandResultType::Assoc,
		'search'                => MpdCommandResultType::Files,
		'searchadd'             => MpdCommandResultType::Ack,
		'searchaddpl'           => MpdCommandResultType::Ack,
		'update'                => MpdCommandResultType::Value,
		'rescan'                => MpdCommandResultType::Value,
		// Stickers:
		'sticker get'           => MpdCommandResultType::Stickers,
		'sticker set'           => MpdCommandResultType::Ack,
		'sticker delete'        => MpdCommandResultType::Ack,
		'sticker list'          => MpdCommandResultType::Stickers,
		'sticker find'          => MpdCommandResultType::Special,
		// Connection settings:
		'close'                 => MpdCommandResultType::None,
		'kill'                  => MpdCommandResultType::None,
		'password'              => MpdCommandResultType::Internal,
		'ping'                  => MpdCommandResultType::Ack,
		// Audio output devices:
		'disableoutput'         => MpdCommandResultType::Ack,
		'enableoutput'          => MpdCommandResultType::Ack,
		'toggleoutput'          => MpdCommandResultType::Ack,
		'outputs'               => MpdCommandResultType::Special,
		// Reflection:
		'config'                => MpdCommandResultType::Unsupported,
		'commands'              => MpdCommandResultType::Arr,
		'notcommands'           => MpdCommandResultType::Arr,
		'tagtypes'              => MpdCommandResultType::Arr,
		'urlhandlers'           => MpdCommandResultType::Arr,
		'decoders'              => MpdCommandResultType::Special,
		// Client to client:
		'subscribe'             => MpdCommandResultType::Unsupported,
		'unsubscribe'           => MpdCommandResultType::Unsupported,
		'channels'              => MpdCommandResultType::Unsupported,
		'readmessages'          => MpdCommandResultType::Unsupported,
		'sendmessage'           => MpdCommandResultType::Unsupported
	);
	/** @var string $hostname The hostname or IP address of the MPD server. */
	private $hostname = 'localhost';
	/** @var int $port The listening port of the MPD server. */
	private $port = 6600;
	/** @var string|null $password The connection password of the MPD server. */
	private $password = NULL;
	/** @var bool $connected The connection state. */
	private $connected = FALSE;
	/**
	 * @var resource|null $socket Socket variable for TCP communication with the
	 *   MPD server.
	 */
	private $socket = NULL;
	/** @var string $protocolVersion The MPD protocol version. */
	private $protocolVersion = '?';
	/** @var string|null $commandList Variable for building command lists. */
	private $commandList = NULL;

	/**
	 * Construct the Mpd object.
	 *
	 * If the connection to the MPD server is not password protected, supply NULL
	 * for the $password parameter.
	 *
	 * @param string $hostname The hostname or IP address of the MPD server.
	 * @param integer $port The listening port of the MPD server.
	 * @param string|null $password The connection password of the MPD server.
	 */
	public function __construct($hostname, $port = 6600, $password = NULL) {
		$this->hostname = $hostname;
		$this->port = $port;
		$this->password = $password;
	}

	/**
	 * Destroy the Mpd object.
	 */
	public function __destruct() {
		if ($this->connected) {
			$this->disconnect();
		}
	}

	/**
	 * Open a connection to the MPD server and try to login.
	 *
	 * @throws MpdIOException If a socket error or protocol error is detected.
	 * @throws MpdCommandFailedException If login to the MPD server fails.
	 */
	public function connect() {
		if ($this->connected) {
			$this->disconnect();
		}

		// Open socket.
		$errNo = 0;
		$errStr = '';
		$this->socket = fsockopen($this->hostname, $this->port, $errNo, $errStr, 5);

		if ($this->socket == FALSE) {
			$msg = 'Socket error ' . $errNo . ' ("' . $errStr. '").';
			throw new MpdIOException($msg);
		}

		$this->connected = TRUE;

		// Read MPD hello message and extract MPD protocol version.
		$hello = strtok(fgets($this->socket), "\n");

		if (strncmp('OK MPD ', $hello, strlen('OK MPD ')) == 0) {
			list($this->protocolVersion) = sscanf($hello, 'OK MPD %s');
		} else {
			$msg = 'Protocol error. Server response: "' . $hello . '".';
			throw new MpdIOException($msg);
		}

		// If password is not NULL try to login.
		if (!is_null($this->password)) {
			fputs($this->socket, 'password "' . $this->password . '"' . "\n");
			$line = fgets($this->socket);

			if (strncmp(self::MPD_ACK, $line, strlen(self::MPD_ACK)) == 0) {
				$this->disconnect();
				list($ack, $errStr) = explode(self::MPD_ACK, $line, 2);
				throw new MpdCommandFailedException(rtrim($errStr, "\n"));
			}
		}
	}

	/**
	 * Close the connection to the MPD server.
	 */
	public function disconnect() {
		if (!$this->connected) {
			// Already disconnected.
			return;
		}

		$this->executeCommand('close');
		fclose($this->socket);
		$this->connected = FALSE;
		$this->protocolVersion = '?';
		$this->socket = NULL;
	}

	/**
	 * Get the connection state.
	 *
	 * @return bool TRUE if connected, FALSE if disconnected.
	 */
	public function getConnected() {
		return $this->connected;
	}

	/**
	 * Get the MPD protocol version.
	 *
	 * This is not the actual daemon version, e.g. protocol can be "0.17.0" while
	 * daemon version is 0.17.6. Returns "?" if disconnected.
	 *
	 * @return string The version of the MPD protocol spoken by the MPD server.
	 */
	public function getProtocolVersion() {
		return $this->protocolVersion;
	}

	/**
	 * Execute an MPD command with a maximum of two arguments, parse the response
	 * and return the result.
	 *
	 * @param string $command The MPD command to send.
	 * @param string|null $arg1 The 1st command argument.
	 * @param string|null $arg2 The 2nd command argument.
	 * @return mixed The parsed result of the MPD command sent.
	 * @throws InvalidArgumentException If the MPD command is unknown, not
	 *   supported, not allowed to be sent directly, if the special parsing
	 *   function for the command is missing or if the MPD command result type is
	 *   invalid.
	 * @throws MpdIOException If not connected to the MPD server or if connection
	 *   is lost while reading.
	 * @throws MpdCommandFailedException If the MPD server replies with an error
	 *   message.
	 */
	public function executeCommand($command, $arg1 = NULL, $arg2 = NULL) {
		$args = array();

		// Do only use arg2 if there is also an arg1.
		if (!is_null($arg1)) {
			$args[] = $arg1;

			if (!is_null($arg2)) {
				$args[] = $arg2;
			}
		}

		return $this->executeCommandEx($command, $args);
	}

	/**
	 * Execute an MPD command with more than two arguments, parse the response
	 * and return the result.
	 *
	 * @param string $command The MPD command to send.
	 * @param string[] $args The command arguments.
	 * @return mixed The parsed result of the MPD command sent.
	 * @throws InvalidArgumentException If the MPD command is unknown, not
	 *   supported, not allowed to be sent directly, if the special parsing
	 *   function for the command is missing or if the MPD command result type is
	 *   invalid.
	 * @throws MpdIOException If not connected to the MPD server or if connection
	 *   is lost while reading.
	 * @throws MpdCommandFailedException If the MPD server replies with an error
	 *   message.
	 */
	public function executeCommandEx($command, array $args) {
		// Get the result type for the command.
		if (array_key_exists($command, $this->commands)) {
			$mpdCommandResultType = $this->commands[$command];
		} else {
			$msg = 'Unknown MPD command: "' . $command . '".';
			throw new \InvalidArgumentException($msg);
		}

		if ($mpdCommandResultType == MpdCommandResultType::Unsupported) {
			$msg = 'MPD command "' . $command . '" is not supported.';
			throw new \InvalidArgumentException($msg);
		}

		if ($mpdCommandResultType == MpdCommandResultType::Internal) {
			$msg = 'MPD command "' . $command . '" not allowed to be sent '
			     . 'directly. Use the corresponding class methods instead.';
			throw new \InvalidArgumentException($msg);
		}

		// Build the command string.
		$commandString = $command;

		foreach ($args as $arg) {
			$commandString .= ' "' . $arg . '"';
		}

		// Add line break and write the command string to the socket.
		$this->write($commandString . "\n");

		if ($mpdCommandResultType == MpdCommandResultType::None) {
			// Nothing to read, nothing to parse.
			return TRUE;
		}

		if ($mpdCommandResultType == MpdCommandResultType::Special) {
			// Read from the socket and call the special parsing function for this
			// command by name.
			$function = 'parse_' . str_replace(' ', '_', $command);

			if (is_callable(array($this, $function))) {
				return call_user_func(array($this, $function), $this->read());
			} else {
				$msg = 'Missing parsing function for MPD command "' . $command . '".';
				throw new \InvalidArgumentException($msg);
			}
		}

		// Read from the socket and call the standard parsing function.
		return $this->parse($this->read(), $mpdCommandResultType);
	}

	/**
	 * Start a new command list.
	 */
	public function beginCommandList() {
		$this->commandList = "command_list_begin\n";
	}

	/**
	 * Enqueue an MPD command with a maximum of two arguments to the command list.
	 *
	 * At the moment, only MPD commands of MpdCommandResultType::Ack are allowed
	 * in a command list.
	 *
	 * @param string $command The MPD command.
	 * @param string|null $arg1 The 1st command argument.
	 * @param string|null $arg2 The 2nd command argument.
	 * @throws InvalidArgumentException If the MPD command is unknown, not
	 *   supported or not allowed in a command list.
	 */
	public function enqueueCommand($command, $arg1 = NULL, $arg2 = NULL) {
		$args = array();

		// Do only use arg2 if there is also an arg1.
		if (!is_null($arg1)) {
			$args[] = $arg1;

			if (!is_null($arg2)) {
				$args[] = $arg2;
			}
		}

		$this->enqueueCommandEx($command, $args);
	}

	/**
	 * Enqueue an MPD command with more than two arguments to the command list.
	 *
	 * At the moment, only MPD commands of MpdCommandResultType::Ack are allowed
	 * in a command list.
	 *
	 * @param string $command The MPD command.
	 * @param string[] $args The command arguments.
	 * @throws InvalidArgumentException If the MPD command is unknown, not
	 *   supported or not allowed in a command list.
	 */
	public function enqueueCommandEx($command, array $args) {
		// Get the result type for the command.
		if (array_key_exists($command, $this->commands)) {
			$mpdCommandResultType = $this->commands[$command];
		} else {
			$msg = 'Unknown MPD command: "' . $command . '".';
			throw new \InvalidArgumentException($msg);
		}

		if ($mpdCommandResultType == MpdCommandResultType::Unsupported) {
			$msg = 'MPD command "' . $command . '" is not supported.';
			throw new \InvalidArgumentException($msg);
		}

		if ($mpdCommandResultType != MpdCommandResultType::Ack) {
			$msg = 'Only MPD commands of MpdCommandResultType::Ack are allowed in '
			     . 'a command list.';
			throw new \InvalidArgumentException($msg);
		}

		// Begin new command list, if the user forgot the call of beginCommandList.
		if (is_null($this->commandList)) {
			$this->beginCommandList();
		}

		// Build the command string.
		$commandString = $command;

		foreach ($args as $arg) {
			$commandString .= ' "' . $arg . '"';
		}

		// Add line break and add the command to the command list.
		$this->commandList .= $commandString . "\n";
	}

	/**
	 * Execute the command list, parse the response and return the result.
	 *
	 * @return mixed The parsed result of the MPD command(s) sent.
	 * @throws MpdIOException If not connected to the MPD server or if connection
	 *   is lost while reading.
	 * @throws MpdCommandFailedException If the MPD server replies with an error
	 *   message.
	 */
	public function endCommandList() {
		if (is_null($this->commandList)) {
			// Nothing to do.
			return;
		}

		$this->commandList .= "command_list_end\n";
		$commandString = $this->commandList;
		$this->commandList = NULL;
		$this->write($commandString);
		return $this->parse($this->read(), MpdCommandResultType::Ack);
	}

	/**
	 * Write a command string to the socket.
	 *
	 * @param string $commandString The command string to send.
	 * @throws MpdIOException If not connected to the MPD server.
	 */
	private function write($commandString) {
		if (!$this->connected) {
			throw new MpdIOException('Not connected.');
		}

		// Write to the socket.
		fputs($this->socket, $commandString);
	}

	/**
	 * Read response from the socket.
	 *
	 * @return string[] All lines of the MPD response with the line break removed.
	 * @throws MpdIOException If not connected to the MPD server or if connection
	 *   is lost while reading.
	 * @throws MpdCommandFailedException If the MPD server replies with an error
	 *   message.
	 */
	private function read() {
		if (!$this->connected) {
			throw new MpdIOException('Not connected.');
		}

		// Read lines from the socket until we get an "OK" (success) or an "ACK ..."
		// (error) or until the server closes the connection.
		$continue = TRUE;
		$errStr = '';
		$lines = array();

		while (!feof($this->socket) && $continue) {
			$line = fgets($this->socket);

			if (strncmp(self::MPD_OK, $line, strlen(self::MPD_OK)) == 0) {
				$continue = FALSE;
			}

			if (strncmp(self::MPD_ACK, $line, strlen(self::MPD_ACK)) == 0) {
				list($ack, $errStr) = explode(self::MPD_ACK, $line, 2);
				$continue = FALSE;
			}

			if ($continue) {
				$lines[] = strtok($line, "\n"); // remove line break
			}
		}

		if ($continue) {
			// Incomplete response because the server closed the connection.
			fclose($this->socket);
			$this->connected = FALSE;
			$this->protocolVersion = '?';
			$this->socket = NULL;
			throw new MpdIOException('Connection lost.');
		}

		if ($errStr != '') {
			throw new MpdCommandFailedException(rtrim($errStr, "\n"));
		}

		return $lines;
	}

	/**
	 * Standard parsing function.
	 *
	 * Parse all lines of the MPD response according to the command result type.
	 *
	 * @param string[] $lines The lines of the raw MPD response.
	 * @param MpdCommandResultType $mpdCommandResultType The MPD command result
	 *   type.
	 * @return mixed The parsed result of the MPD command sent.
	 * @throws InvalidArgumentException If the MPD command result type is invalid.
	 */
	private function parse(array $lines, $mpdCommandResultType) {
		switch ($mpdCommandResultType) {
			case MpdCommandResultType::Ack:
				return TRUE;
				break;
			case MpdCommandResultType::Value:
				list($key, $value) = explode(': ', $lines[0], 2);
				return $value;
				break;
			case MpdCommandResultType::Arr:
				$result = array();

				foreach ($lines as $line) {
					list($key, $value) = explode(': ', $line, 2);
					$result[] = $value;
				}

				return $result;
				break;
			case MpdCommandResultType::Assoc:
				$result = array();

				foreach ($lines as $line) {
					list($key, $value) = explode(': ', $line, 2);
					$result[$key] = $value;
				}

				return $result;
				break;
			case MpdCommandResultType::Files:
				$result = array();
				$counter = -1;

				foreach ($lines as $line) {
					list($key, $value) = explode(': ', $line, 2);

					if ($key == 'file') {
						$counter ++;
					}

					if ($counter > -1) {
						$result[$counter][$key] = $value;
					}
				}

				return $result;
				break;
			case MpdCommandResultType::Multi:
				$result = array('directories' => array(),
				                'files' => array(),
				                'playlists' => array());
				$counter = array('directories' => -1, 'files' => -1, 'playlists' => -1);
				$currentArray = 'none';

				foreach ($lines as $line) {
					list($key, $value) = explode(': ', $line, 2);

					if ($key == 'directory') {
						$currentArray = 'directories';
					}

					if ($key == 'file') {
						$currentArray = 'files';
					}

					if ($key == 'playlist') {
						$currentArray = 'playlists';
					}

					if (($key == 'directory') ||
					    ($key == 'file') ||
					    ($key == 'playlist')) {
						$counter[$currentArray]++;
					}

					if ($currentArray != 'none') {
						if ($counter[$currentArray] > -1) {
							$result[$currentArray][$counter[$currentArray]][$key] = $value;
						}
					}
				}

				return $result;
				break;
			case MpdCommandResultType::Stickers:
				$result = array();

				foreach ($lines as $line) {
					list($sticker) = sscanf($line, 'sticker: %s');
					list($key, $value) = explode('=', $sticker, 2);
					$result[$key] = $value;
				}

				return $result;
				break;
			default:
				$msg = 'Invalid result type: "' . $mpdCommandResultType . '".';
				throw new \InvalidArgumentException($msg);
		}
	}

	/**
	 * Special parsing function for response of "plchangesposid" command.
	 *
	 * @param string[] $lines The lines of the raw MPD response.
	 * @return array The parsed result of the MPD command sent.
	 */
	private function parse_plchangesposid(array $lines) {
		$result = array();
		$counter = -1;

		foreach ($lines as $line) {
			list($key, $value) = explode(': ', $line, 2);

			if ($key == 'cpos') {
				$counter ++;
			}

			if ($counter > -1) {
				$result[$counter][$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Special parsing function for response of "listplaylists" command.
	 *
	 * @param string[] $lines The lines of the raw MPD response.
	 * @return array The parsed result of the MPD command sent.
	 */
	private function parse_listplaylists(array $lines) {
		$result = array();
		$counter = -1;

		foreach ($lines as $line) {
			list($key, $value) = explode(': ', $line, 2);

			if ($key == 'playlist') {
				$counter ++;
			}

			if ($counter > -1) {
				$result[$counter][$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Special parsing function for response of "outputs" command.
	 *
	 * @param string[] $lines The lines of the raw MPD response.
	 * @return array The parsed result of the MPD command sent.
	 */
	private function parse_outputs(array $lines) {
		$result = array();
		$counter = -1;

		foreach ($lines as $line) {
			list($key, $value) = explode(': ', $line, 2);

			if ($key == 'outputid') {
				$counter ++;
			}

			if ($counter > -1) {
				$result[$counter][$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Special parsing function for response of "decoders" command.
	 *
	 * @param string[] $lines The lines of the raw MPD response.
	 * @return array The parsed result of the MPD command sent.
	 */
	private function parse_decoders(array $lines) {
		$result = array();
		$counter = -1;

		foreach ($lines as $line) {
			list($key, $value) = explode(': ', $line, 2);

			if ($key == 'plugin') {
				$counter ++;
			}

			if ($counter > -1) {
				switch ($key) {
					case 'plugin':
						$result[$counter][$key] = $value;
						$result[$counter]['suffixes'] = array();
						$result[$counter]['mime_types'] = array();
						break;
					case 'suffix':
						$result[$counter]['suffixes'][] = $value;
						break;
					case 'mime_type':
						$result[$counter]['mime_types'][] = $value;
						break;
				}
			}
		}

		return $result;
	}

	/**
	 * Special parsing function for response of "sticker find" command.
	 *
	 * @param string[] $lines The lines of the raw MPD response.
	 * @return array The parsed result of the MPD command sent.
	 */
	private function parse_sticker_find(array $lines) {
		$result = array();
		$counter = -1;

		foreach ($lines as $line) {
			list($key, $value) = explode(': ', $line, 2);

			if ($key == 'file') {
				$counter ++;
			}

			if ($counter > -1) {
				switch ($key) {
					case 'file':
						$result[$counter][$key] = $value;
						break;
					case 'sticker':
						list($stickerKey, $stickerValue) = explode('=', $value, 2);
						$result[$counter][$key] = array($stickerKey => $stickerValue);
						break;
				}
			}
		}

		return $result;
	}
}
?>
