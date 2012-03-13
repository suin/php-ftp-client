<?php

namespace Suin\FTPClient;

interface FTPClientInterface
{
	const MODE_ASCII  = 1;
	const MODE_BINARY = 2;

	/**
	 * Connect to the server and return the new FTPClientInterface object.
	 * @param string $host
	 * @param int $port
	 * @throws \RuntimeException If failed to connect to the server.
	 */
	public function __construct($host, $port = 21);

	/**
	 * Login to the server.
	 * @abstract
	 * @param string $username
	 * @param string $password
	 * @return void
	 * @throws \RuntimeException If failed to login to the server.
	 */
	public function login($username, $password);

	/**
	 * Turn on passive mode.
	 * @abstract
	 * @return void
	 */
	public function enablePassive();

	/**
	 * Close the connection.
	 * @abstract
	 * @return void
	 */
	public function disconnect();

	/**
	 * Return the current directory name.
	 * @abstract
	 * @return string
	 * @throws \RuntimeException If getting the current directory fails.
	 */
	public function getCurrentDirectory();

	/**
	 * Change the current directory on a FTP server.
	 * @abstract
	 * @param string $directory
	 * @return void
	 * @throws \RuntimeException If changing directory fails.
	 */
	public function changeDirectory($directory);

	/**
	 * Remove a directory.
	 * @abstract
	 * @param string $directory
	 * @return void
	 * @throws \RuntimeException If removing a directory fails.
	 */
	public function removeDirectory($directory);

	/**
	 * Create a directory.
	 * @abstract
	 * @param string $directory
	 * @return void
	 * @throws \RuntimeException If creating a directory fails.
	 */
	public function createDirectory($directory);

	/**
	 * Rename a file or a directory on the FTP server.
	 * @abstract
	 * @param string $oldName
	 * @param string $newName
	 */
	public function rename($oldName, $newName);

	/**
	 * Return the size of the given file.
	 * @abstract
	 * @param string $filename
	 * @return int
	 * @throws \RuntimeException If getting the size of the given file fails.
	 */
	public function getFileSize($filename);

	/**
	 * Return the last modified time of the given file.
	 * @abstract
	 * @param string $filename
	 * @return int The last modified time as a Unix timestamp
	 * @throws \RuntimeException If getting the modified time fails.
	 */
	public function getModifiedTime($filename);

	/**
	 * Delete a file on the FTP server.
	 * @abstract
	 * @param string $filename
	 * @return void
	 * @throws \RuntimeException If deleting the file fails.
	 */
	public function removeFile($filename);

	/**
	 * Set permissions on a file via FTP.
	 * @abstract
	 * @param string $filename
	 * @param int $mode The new permissions, given as an octal value.
	 * @throws \RuntimeException If chmod fails.
	 */
	public function setPermission($filename, $mode);

	/**
	 * Return a list of files in the given directory.
	 * @abstract
	 * @param string $directory
	 * @return array
	 * @throws \RuntimeException If getting a list fails.
	 */
	public function getList($directory);

	/**
	 * Download a file from the FTP server.
	 * @abstract
	 * @param string $remoteFilename
	 * @param string $localFilename
	 * @param int $mode MODE_ASCII or MODE_BINARY
	 * @throws \RuntimeException If downloading a file fails.
	 */
	public function download($remoteFilename, $localFilename, $mode);

	/**
	 * Upload a file to the FTP server.
	 * @abstract
	 * @param string $localFilename
	 * @param string $remoteFilename
	 * @param int $mode MODE_ASCII or MODE_BINARY
	 * @throws \RuntimeException If uploading a file fails.
	 */
	public function upload($localFilename, $remoteFilename, $mode);
}
