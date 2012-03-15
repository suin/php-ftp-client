<?php

class Suin_FTPClient_Test_FTPMessageObserver implements Suin_FTPClient_ObserverInterface
{
	protected $messages = array();

	/**
	 * @param string $request
	 * @return void
	 */
	public function updateWithRequest($request)
	{
		$this->messages[] = sprintf('PUT > %s', trim($request));
	}

	/**
	 * @param string $message
	 * @param int $code
	 * @return void
	 */
	public function updateWithResponse($message, $code)
	{
		$this->messages[] = sprintf('GET < %s', trim($message));
	}

	public function getMessagesAsString()
	{
		return implode("\n", $this->messages);
	}
}
