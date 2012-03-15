<?php

interface Suin_FTPClient_ObservableInterface
{
	/**
	 * Set an observer.
	 * @param Suin_FTPClient_ObserverInterface $observer
	 */
	public function setObserver(Suin_FTPClient_ObserverInterface $observer);
}
