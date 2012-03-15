<?php

interface Suin_FTPClient_ObserverInterface
{
	/**
	 * @abstract
	 * @param string $request
	 * @return void
	 */
	public function updateWithRequest($request);

	/**
	 * @abstract
	 * @param string $message
	 * @param int $code
	 * @return void
	 */
	public function updateWithResponse($message, $code);
}
